# CheckPass Critical Code Assessment, Independent QAT & Google Play Store Readiness Report

> **Assessment Date:** November 25, 2026  
> **Target Platforms:** `checkpass-api` (Laravel), `checkpass-app` (Ionic/Vue + Capacitor Android), `checkpass-admin` (Vue 3)  
> **Overall Google Play Store Readiness:** **NOT READY — 65% (Blocked by Critical Mobile Config, Signing & Backend Security/500 Fixes)**

---

## 1. Executive Verdict

While the core CheckPass business workflows across Resident, Security Guard, Homeowner, Estate Admin, and Super Admin pass baseline happy-path testing (**24/24** on `tests/agent-e2e-runner.mjs`), an independent critical code audit, edge-case QAT suite, and Android packaging inspection uncovered **critical production blockers** that must be resolved before building a release `.aab` or submitting to the Google Play Store.

| Area | Status | Score / Result |
| :--- | :--- | :--- |
| **Core E2E Happy Paths** (`agent-e2e-runner.mjs`) | PASS | **24 / 24** assertions passed (44.59s) |
| **Independent QAT, Security & Edge Cases** | PARTIAL FAIL | **38 / 41** passed (2 critical backend bugs exposed) |
| **Frontend Production Builds** (`checkpass-app` & `checkpass-admin`) | PASS | Both `vue-tsc && vite build` succeed with 0 errors |
| **Android Native & Play Store Packaging** | BLOCKED | Missing `.env.production`, release signing keystore, `google-services.json`, and Java/Android SDK on host |
| **Google Play Policy Compliance** | BLOCKED | Privacy Policy & Terms exist, but **In-App Account Deletion** is missing |

---

## 2. Independent Test Execution Results

### 2.1 Existing Autonomous E2E Suite (`node tests/agent-e2e-runner.mjs`)
- **Result:** **24 / 24 Passed (100%)** in 44.59s against live Laravel + MySQL.
- **Verified Flows:** Public platform stats, inbound CRM lead ingestion, Super Admin login & Kanban stage progression, Estate Admin topology/residents/fees/artisans zero-mock checks, Resident pass creation & extension, Security Gate QR lookup/allow-entry/mark-exited, Homeowner deed (`DEED-SH-21`) & tenant link, and automated teardown.

### 2.2 Independent QAT, Security & Edge-Case Suite (`independent_qat_suite.mjs`)
- **Result:** **38 Passed | 3 Failed** (Total: 41 assertions)
- **Verified Passing Edge Cases & Modules:**
  - **Auth & Session:** Invalid password rejected (`422`), empty payload rejected (`422`), logout succeeds (`200`), and **revoked Bearer token is rejected (`401`)** on subsequent requests.
  - **RBAC Isolation:** Residents blocked from `/v1/admin/dashboard` (`403`), `/v1/admin/estates` (`403`), and `PATCH /v1/passes/{ulid}/allow-entry` (`403`). Estate Admins blocked from Super-Admin `/v1/admin/billing/subscribers` (`403`) and cross-estate `/v1/admin/estates/1/properties` (`403`).
  - **Pass Edge Cases:** Non-existent ULID returns `404`; past `expiresAt` rejected (`422`); `Recurring` pass without `recurringDays` rejected (`422`); revoking a `Pending` pass works; Security Guard blocked from `allow-entry` on a `Revoked` pass (`422`); extending a `Revoked` pass blocked (`422`).
  - **Emergency SOS:** Resident triggers SOS (`201`) -> Admin acknowledges (`200`) -> Admin resolves (`200`) -> cleaned up.
  - **Artisans & Reviews:** Admin creates artisan (`201`) -> Resident lists & submits review (`201`) -> cleaned up.
  - **News & Announcements:** Admin publishes (`201`) -> Resident fetches estate feed (`200`) -> cleaned up.
  - **Properties, Notifications, Profile & Billing:** Stats, topology tree, property structure, notifications, profile, plans, and subscription endpoints all returned `200`.
  - *(Note: `POST /v1/access-phrase/verify` returned `422` in the script solely because `AccessPhraseController::verify` correctly enforces `visitorName` as required.)*

---

## 3. Critical Blockers Found (Must Fix Before Play Store Submission)

### 3.1 Mobile App (`checkpass-app`) & Play Store Blockers
1. **Relative API Base URL (`/api`) Breaks Native Android APK/AAB**
   - **Evidence:** `checkpass-app/.env` sets `VITE_API_BASE_URL=/api` (and `src/api/client.ts:4` falls back to `/api`), with no `.env.production` file.
   - **Impact:** Inside Capacitor's Android WebView (`https://localhost`), `/api` resolves to `https://localhost/api` — every login and API call will fail on real phones.
   - **Fix:** Create `checkpass-app/.env.production` with `VITE_API_BASE_URL=https://api.checkpass.ng/api`.

2. **Hardcoded `http://localhost:3100` in Mobile UI**
   - **Evidence:** `checkpass-app/src/views/HomeView.vue:95` renders `<a href="http://localhost:3100" target="_blank" class="admin-portal-btn">`.
   - **Impact:** Clicking "Open Admin Dashboard" on a production Android device fails with connection refused.
   - **Fix:** Replace with an environment variable (`VITE_ADMIN_URL`) pointing to `https://checkpass.ng/login` (or production admin URL).

3. **Paystack Test Key Active in Mobile App**
   - **Evidence:** `checkpass-app/.env` and `checkpass-app/src/views/PaymentDetailsView.vue:440` fall back to `pk_test_83dc482da45bc6fe89cff604cad736eab50ec44f`.
   - **Impact:** Real residents cannot complete live dues payments; transactions run in Paystack sandbox mode.
   - **Fix:** Set `VITE_PAYSTACK_PUBLIC_KEY=pk_live_...` in `.env.production` and remove hardcoded test key fallback.

4. **Missing Android Release Signing & Keystore**
   - **Evidence:** `checkpass-app/android/app/build.gradle` has no `signingConfigs.release` block under `buildTypes.release`, and no `.keystore` / `.jks` file exists in `checkpass-app`.
   - **Impact:** Google Play Console rejects unsigned or debug-signed `.aab` bundles.
   - **Fix:** Generate an upload keystore (`keytool -genkey ...`), configure `signingConfigs.release` in `android/app/build.gradle` (reading secrets from `keystore.properties` excluded from git), and set `minifyEnabled true` / ProGuard rules if desired.

5. **Missing `google-services.json` for Push Notifications**
   - **Evidence:** `checkpass-app/android/app/google-services.json` does not exist (`build.gradle:53` logs *"Push Notifications won't work"*), even though `@capacitor/push-notifications` is initialized in `App.vue`.
   - **Impact:** FCM push notifications (visitor arrivals, SOS alerts) will fail on Android.
   - **Fix:** Download `google-services.json` for `com.checkpass.app` from Firebase Console and place it in `checkpass-app/android/app/`.

6. **Missing In-App Account Deletion (Google Play Policy Violation)**
   - **Evidence:** `checkpass-app/src/views/ProfileView.vue` includes Terms of Service and Privacy Policy (NDPA 2023), but has **no account deletion / deactivation action**.
   - **Impact:** Google Play's Data Deletion policy mandates an in-app account deletion option (plus a web deletion URL) for apps that manage user accounts.
   - **Fix:** Add a "Delete Account" flow in `ProfileView.vue` backed by an API endpoint (`DELETE /v1/profile`).

7. **Android Permissions & Build Environment**
   - **Evidence:** `AndroidManifest.xml` only declares `INTERNET`; `CAMERA` (for QR scanning) and `POST_NOTIFICATIONS` (Android 13+ / API 33+, `targetSdkVersion = 36`) should be explicitly verified/declared. Also, `java` and `ANDROID_HOME` are not configured on the current workstation.

### 3.2 Backend API (`checkpass-api`) Security & Crash Bugs
1. **CRITICAL SECURITY: Unauthenticated Public Access to CRM Leads**
   - **Evidence:** `checkpass-api/routes/api.php` lines 69–76 place `GET /v1/crm/leads`, `GET /v1/crm/leads/{id}`, `PATCH /v1/crm/leads/{id}`, `DELETE /v1/crm/leads/{id}`, and `POST /v1/crm/leads/{id}/provision-sandbox` **outside** `auth:sanctum` and `admin`.
   - **Live QAT Proof:** Unauthenticated `GET /v1/crm/leads` returned **HTTP `200 OK`**, exposing all estate prospect PII (names, phones, emails, deal values).
   - **Fix:** Keep only `POST /v1/crm/leads` public (with rate throttling) and move `index`, `show`, `update`, `destroy`, and `provisionSandbox` inside `auth:sanctum` + `admin` (and `super_admin` check).

2. **CRITICAL 500 CRASH: `PassController::formatPassForSecurity()` on Declared Items**
   - **Evidence:** `PassController.php:584` executes `'flaggedAt' => $item->flagged_at->toIso8601String()` without null-safety.
   - **Live QAT Proof:** Adding an item via `POST /v1/passes/{ulid}/items` leaves `flagged_at = null`. Subsequent `GET /v1/passes/find-by-phone` crashed with **HTTP `500`**: `Call to a member function toIso8601String() on null`.
   - **Fix:** Change line 584 to `$item->flagged_at?->toIso8601String()`.

3. **HIGH SECURITY: Missing `estate_id` Scope on Security Pass Lookup/Actions**
   - **Evidence:** `PassController.php` `show` (line 99), `findByPhone` (line 164), `allowEntry` (line 330), `declineEntry` (line 391), and `markExited` (line 418) do not filter by `estate_id === $user->estate_id` for security guards, and `declineEntry` lacks a status guard (allowing `Exited`/`Revoked` passes to be marked `Declined`).
   - **Fix:** Scope all security pass queries by `->where('estate_id', $user->estate_id)` and guard `declineEntry` against terminal statuses.

4. **CORS Wildcard (`*`)**
   - **Evidence:** `checkpass-api/config/cors.php:8` sets `'allowed_origins' => ['*']`.
   - **Fix:** Restrict `allowed_origins` to production web domains and Capacitor origins (`https://localhost`, `capacitor://localhost`).

---

## 4. Module-by-Module E2E Readiness Matrix

| Module | Backend API | Web / Mobile UI | Production Readiness |
| :--- | :--- | :--- | :--- |
| **Auth & Session** | Pass (`login`, `logout`, `refresh`, token revocation) | Pass (`LoginView`, legal modals) | Ready once `.env.production` API URL is set |
| **Visitor Passes & Items** | **500 bug** on unflagged items + missing `estate_id` guard on security actions | Pass (`CreatePassView`, `PassDetailView`, QR) | Fix `PassController.php:584` & `estate_id` scoping |
| **Access Phrase** | Pass (`show`, `regenerate`, `verify` w/ `estate_id` scope) | Pass | Ready |
| **Security Gatehouse** | Partial (depends on `PassController` fix) | Pass (`QRScannerModal`) | Fix `PassController` & verify `CAMERA` permission |
| **Homeowner Portfolio** | Pass (`DEED-SH-21`, tenant links) | Pass (`HomeView` homeowner tab) | Ready |
| **Dues & Paystack** | Pass (`/v1/fees`, Paystack init/verify) | **Blocked** (Test key fallback) | Switch to `pk_live_...` |
| **Emergency SOS** | Pass (`store`, `acknowledge`, `resolve`, `delete`) | Pass (`EmergencyView`) | Ready |
| **Artisans & Reviews** | Pass (CRUD + reviews) | Pass (`HomeView`) | Ready |
| **News & Comments** | Pass (CRUD + audience scoping) | Pass (Mobile keyboard lock fixed) | Ready |
| **Properties & Topology** | Pass (CRUD, stats, topology tree, estate isolation) | Pass (`PropertiesView`, `PropertySetupView`) | Ready |
| **Super Admin CRM** | **CRITICAL** (Unauthenticated `GET/PATCH/DELETE` on leads) | Pass (`CrmView`) | Lock down CRM routes immediately |
| **Android Play Store Build** | N/A | **Blocked** (No keystore, no `google-services.json`, no in-app account delete, no Java/SDK on host) | Complete checklist in Section 3.1 |

---

## 5. Post-Fix Verification Update (November 26, 2026)

Following commit `b409b2da`, the following items from Section 3 have been resolved and verified:

### Resolved & Verified
- **Production Builds:** Both `checkpass-admin` (`205` modules, `24.25s`) and `checkpass-app` (`1,954` modules, `17.35s`) pass `vue-tsc && vite build` with 0 errors.
- **Production Environment (`checkpass-app/.env.production`):** Created with `VITE_API_BASE_URL=https://api.checkpass.ng/api` and `VITE_ADMIN_URL=https://checkpass.ng/login`.
- **Hardcoded Localhost Links:** Removed from `HomeView.vue`; remaining `localhost` reference in `LoginView.vue` is strictly guarded by `window.location.hostname === 'localhost'`.
- **In-App Account Deletion:** Implemented in `ProfileController.php` and `ProfileView.vue`.
- **Backend Security & Crash Fixes:** Fixed `PassController.php` null-safe `flagged_at` handling, estate scoping, `cors.php` origin rules, and CRM route protection in `routes/api.php`.
- **Android Manifest Permissions:** Updated `checkpass-app/android/app/src/main/AndroidManifest.xml`.

### Remaining Items Before Play Store `.aab` Release
1. **Paystack Live Key:** Replace `pk_test_...` in `checkpass-app/.env.production` (and `PaymentDetailsView.vue` fallback) with the live `pk_live_...` key.
2. **Firebase Config (`google-services.json`):** Place `google-services.json` into `checkpass-app/android/app/` for FCM push notifications.
3. **Android Release Keystore & SDK:** Configure `signingConfigs.release` in `android/app/build.gradle` and build the signed `.aab` on a machine with Java and Android SDK configured.
