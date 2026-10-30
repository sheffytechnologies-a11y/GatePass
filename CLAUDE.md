# CLAUDE.md

Gatepass (shipped to users as **checkpazz**) is an estate visitor-access system: residents issue QR gate passes to visitors, security guards scan them at the gate, and estate admins manage residents, fees, news, and their SaaS subscription. Payments go through Paystack (NGN, Nigeria).

There is no root build. The repo holds three independent projects, each with its own dependencies:

| Dir | What | Stack |
|---|---|---|
| `gatepass-api/` | REST API used by both frontends | Laravel 12, PHP 8.2, Sanctum tokens, Telescope, MySQL (SQLite in `.env.example`) |
| `gatepass-admin/` | Web admin panel for estate admins and super admins | Vue 3 + Vite 5 + Pinia + TypeScript, dev port 3100 |
| `gatepass-app/` | Mobile app for residents **and** security guards | Ionic 7 + Vue 3 + Capacitor 8 (`com.checkpazz.app`), Vite 4 |

The PNG screenshots in the repo root are UI reference mockups. `gatepass-app/android-old/` is a stale Android project; the live one is `gatepass-app/android/`.

## Commands

```bash
# API (from gatepass-api/)
composer setup                 # install, copy .env, key:generate, migrate, npm build
composer dev                   # serve + queue + pail logs + vite, all at once
php artisan serve              # API only, on :8000
php artisan migrate:fresh --seed
composer test                  # or: php artisan test --filter=SomeTest
vendor/bin/pint                # PHP formatter

# Admin / mobile app (from gatepass-admin/ or gatepass-app/)
npm run dev
npm run build                  # runs vue-tsc first, so type errors fail the build

# Mobile native (from gatepass-app/)
npm run build && npx cap sync  # then open android/ or ios/ in the IDE
```

Both frontends deploy as SPAs to Firebase Hosting (`firebase.json`, serving `dist/`).

Tests run on in-memory SQLite (`phpunit.xml`). Only the access-phrase feature has real tests. Check other changes by hand against the running API.

## Backend architecture

All routes live in `gatepass-api/routes/api.php` under `/api/v1`. Every controller is in `app/Http/Controllers/Api/`.

### Three kinds of authenticated actor

This is the most important thing to understand before touching auth or permissions:

1. **`Admin`** (`admins` table): logs in with email at `/v1/admin/auth/login`. `role` is `super_admin` (the internal team, sees every estate) or `estate_admin` (self-registered, confined to one estate through the `admin_estate` pivot). The `admin` middleware (`EnsureAdmin`) only checks `$request->user() instanceof Admin`.
2. **`User` with `type = 'resident'`**: logs in with **phone** at `/v1/auth/login` and must have a linked `Resident` row (unit, estate, `role` owner/tenant, push preferences). Without one, login returns `NO_RESIDENT_PROFILE`.
3. **`User` with `type = 'security'`**: a gate guard. Same phone login, no Resident row. Security-only actions (`find-by-phone`, `allow-entry`, `mark-exited`) are gated inline in `PassController` with `$user->type !== 'security'`, not by middleware.

`Admin` and `User` are both Sanctum tokenables. Login issues an access/refresh token pair: admin tokens last 7 and 30 days (`Concerns/IssuesAdminTokens`), and refresh tokens carry the `refresh` ability and are rotated on use. A comment in `api.php` still says admin routes are for "security users". That is out of date: they are for `Admin` models.

### Multi-tenancy and plan limits (shared traits in `Controllers/Api/Concerns/`)

- `ScopesToEstate`: use `scopeEstate($query, $request)` on every admin list query, `resolveEstateId()` when an admin creates something, and `assertEstateAccess()` before acting on a single record. Estate admins are forced onto their own estate. Super admins can narrow to one estate with `?estate_id=`.
- `EnforcesPlanLimits`: `assertNotPastDue`, `assertCanAddUnit` (plan `unit_limit` + `extra_units`, where null means unlimited), and `assertCanAddResident` (hard cap of 3 per unit). These return **HTTP 402** with code `PLAN_LIMIT_REACHED` or `SUBSCRIPTION_PAST_DUE` plus `upgradeUrl: '/billing'`. Super admins bypass them. The grace period comes from `config/billing.php` (`BILLING_GRACE_PERIOD_DAYS`, default 3).

New admin endpoints that read or write estate data should use both traits.

### Domain

- `Estate` has `Unit`s (lane/house/flat plus a `flat_address` string), `Resident`s, `Fee`s, `News`, and a `Subscription` (pointing to a `Plan`, with `BillingTransaction`s).
- `Pass` is keyed publicly by `ulid` (route key) and gets `ulid` and `share_token` automatically on create. `type`: `One-time` | `Recurring` (with `recurring_days`). `status` uses capitalised strings: `Pending` → `On-site` → `Exited`, plus `Revoked` and `Expired`. Expiry is applied lazily when a pass is formatted. A pass has many `Item`s (flagged items carried in or out).
- `Fee` ↔ `User` is many-to-many through `fee_user`, whose pivot holds `payment_status`, `file_path` (manual receipt), `verified_at/by`, and `paystack_reference`.
- `Notification` and `Emergency` belong to a Resident. Admins acknowledge and resolve emergencies.

### Daily access phrase (visitors without smartphones)

`AccessPhraseController` + `UnitAccessPhrase` model, routes under `/v1/access-phrase`.
- A resident's `GET /access-phrase` allocates the unit's phrase for today on first call (format `AMBER LION 27`). `POST /access-phrase/regenerate` rotates it and clears the lockout. "Today" is the date in `config('access.phrase_timezone')` (default `Africa/Lagos`), not UTC.
- Unique indexes `(unit_id, phrase_date)` and `(phrase_date, phrase)` guarantee that no two units share a phrase on a given day. On a collision the code catches `UniqueConstraintViolationException` and draws another phrase.
- A security user calls `GET /access-phrase/units` (the units in their `estate_id`) and then `POST /access-phrase/verify`. A match creates an `On-site` one-time pass under the unit's owner (or first resident), expiring at local midnight, and sends an `arrival` notification to every resident of the unit. A miss increments `failed_attempts` and sends a `denied` notification. Once `access.max_failed_attempts` (default 5) is reached, verify returns 429 `PHRASE_LOCKED` until the phrase is rotated.
- Tests: `tests/Feature/AccessPhraseTest.php`. The SQLite test schema still has the old `residents.role` enum (`primary`/`member`), so tests use `primary`.

### Payments (Paystack)

- **Resident fees**: `FeeController::initializePaystackPayment` / `verifyPaystackPayment`, plus the public webhook `POST /api/v1/paystack/webhook` (HMAC-verified). A fee verified through Paystack becomes `paid` straight away. The older manual flow (bank details + receipt upload, then admin approval) still exists.
- **Estate subscriptions**: `BillingController` (`checkout`/`verify`) plus the public webhook `POST /api/v1/paystack/billing/webhook`. Plans are seeded by `PlanSeeder` (Free Trial with 3 units, then 50, 200, and 1,000 unit tiers; prices in NGN).
- Keys: `PAYSTACK_SECRET_KEY` / `PAYSTACK_PUBLIC_KEY` → `config('services.paystack.*')`. The frontends use `VITE_PAYSTACK_PUBLIC_KEY` and load Paystack's inline JS (`gatepass-admin/src/composables/usePaystack.ts`, `gatepass-app/src/views/PaymentDetailsView.vue`).

### API conventions

- Responses are hand-built arrays with **camelCase** keys, made by private `format*()` methods in each controller (for example `formatResident`, `formatPass`, `formatPlan`). There are no API Resource classes. When you add a field, add it to the matching formatter.
- Request bodies are also camelCase (`visitorName`, `expiresAt`, `refreshToken`) and are mapped to snake_case columns inside the controller.
- Errors use the shape `{ error: true, code: 'UPPER_SNAKE', message }` with a suitable HTTP status. Validation uses inline `$request->validate()` (there are no FormRequest classes).
- Phone numbers are normalised on login (`AuthController::normalisePhone`). Both the normalised and the raw value are matched.

### Seed data (`php artisan migrate:fresh --seed`)

"PHDL Estate" with two units. Residents `08024035326` and `08132489619`, security guard `00000000000`, admin `admin@gatepass.com`. Every password is `123456`.

## Frontends

Both use the `@/` → `src/` alias, axios clients in `src/api/client.ts`, Pinia stores in `src/stores/`, and lazily loaded route views.

### gatepass-admin
- Token is stored in `localStorage` under `admin_token`, `admin_role`, `admin_user`. Any 401 clears the session and redirects to `/login`.
- Base URL is `VITE_API_BASE_URL`, falling back to `http://localhost:8000/api`.
- Routes carry `meta.roles` (`super_admin` | `estate_admin`), checked in `router/index.ts`. A newly registered estate admin goes to `/register/estate` to create their estate.
- `scripts/*.mjs` generate presentation decks and are not part of the app.

### gatepass-app
- A single app serves two roles. `stores/auth.ts` keeps `userType` (`resident` | `security`, from the login response), and views such as `HomeView` branch on `isSecurityUser`. Guards scan visitor QR codes with `jsqr` (`QRScannerModal.vue`). Residents create and share passes and pay fees.
- Tokens: `w_token`, `w_refresh_token`, `w_user_type`. The client silently refreshes on a 401 with code `AUTH_TOKEN_EXPIRED` and queues any requests that fail while the refresh is in flight.
- **Gotcha:** `src/api/client.ts` hardcodes the production `baseURL` (`https://erp-api.hayokmedicare.ng/api`), and the `VITE_API_BASE_URL` line is commented out. The refresh call, however, does use `VITE_API_BASE_URL`. To point the app at a local API, change both.
- `src/api/mock.ts` holds leftover mock data that `stores/notifications.ts` and `ProfileView.vue` still import. Prefer real API calls.
- Shared TypeScript types live in `src/types/index.ts`. Keep them in step with the API's formatter output.
- Daily access phrase: `AccessPhraseCard.vue` (resident home: shows the phrase, plus SMS, copy and regenerate) and `PhraseEntrySheet.vue` (security home: "Visitor Without Phone").
- The fee flow is FeesView → `/fees/payment` → `/fees/payment/details` (Paystack popup, then verify) → `/fees/payment/success`.

## Environment files

Each project has its own `.env`. Never commit Paystack secret keys. `config/cors.php` currently allows all origins (`*`). The env-driven `CORS_ALLOWED_ORIGINS` line is commented out.
