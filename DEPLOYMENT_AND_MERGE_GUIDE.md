# CheckPass Production Deployment & Branch Merging Guide

> **System Version:** CheckPass v2.4.0 (Unified Multi-Estate Architecture)  
> **Target Release Branch:** `main` (Production)  
> **Source Working Branch:** `Sheriff`  
> **Repository:** `https://github.com/sheffytechnologies-a11y/GatePass.git`  
> **Date:** November 2026

---

## 1. System Architecture Overview

The CheckPass monorepo comprises three interconnected production services and shared brand assets:

| Service Directory | Technology Stack | Role & Output | Production Port / Target |
| :--- | :--- | :--- | :--- |
| `checkpass-admin/` | Vue 3 + TypeScript + Vite + Lenis + GSAP | Marketing Landing Page, Super Admin CRM, Estate Manager Portal, Guard Station Web Console | Port `3100` / Vercel / Cloudflare / Nginx |
| `checkpass-app/` | Ionic 7 + Vue 3 + Capacitor 6 | Resident Mobile Portal, 2-Tap WhatsApp Pass Dispatch, Guard Terminal Scanner | Port `5173` / iOS App Store / Google Play / PWA |
| `checkpass-api/` | Laravel 11 + PHP 8.2+ + Sanctum + SQLite/MySQL/PostgreSQL | REST API Engine, Leads CRM, Multi-Barrier Synced Gate Engine, Dues & Paystack Gateway | Port `8000` / Laravel Forge / Docker / EC2 / Railway |
| `shared-brand-assets/` | SVG / WebP / Brand Guidelines | Official logos, duotone icon packs, feature illustrations, and regulatory compliance assets | Bundled into web & mobile builds |

---

## 2. Step-by-Step Git Merge Guide (`Sheriff` ➔ `main`)

Follow these exact steps in your terminal to merge all recent features, fixes, legal compliance updates, and Bento slider refinements into the production `main` branch.

### Step 2.1: Pre-Merge Verification on `Sheriff`
Make sure working tree is completely clean and pushed:
```bash
cd "c:\Users\DELL\Documents\Code Base\Checkpass\GatePass"

# 1. Verify working directory is clean
git status

# 2. Ensure Sheriff is up to date with remote
git pull origin Sheriff
git push origin Sheriff
```

### Step 2.2: Switch to `main` & Synchronize with Remote
```bash
# 3. Switch to main
git checkout main

# 4. Pull latest upstream changes from origin/main
git pull origin main
```

### Step 2.3: Execute Merge
Merge `Sheriff` into `main` with an explicit merge commit:
```bash
# 5. Merge Sheriff into main
git merge Sheriff -m "chore(release): merge Sheriff v2.4.0 into main for production deployment"
```

> [!NOTE]
> *Conflict Status:* The dry-run verification performed across both branches confirmed that `Sheriff` merges cleanly into `origin/main` with automatic fast-forward / zero merge conflicts.

### Step 2.4: Deploy to Remote Production Branch
Push the merged commits to `origin/main`:
```bash
# 6. Push merged production code to GitHub main
git push origin main

# 7. Switch back to your working branch for continued development
git checkout Sheriff
```

---

## 3. Environment Configuration (`.env`)

Ensure the production environment variables are properly configured across all three services before building.

### 3.1 Backend (`checkpass-api/.env`)
```ini
APP_NAME=CheckPass
APP_ENV=production
APP_KEY=base64:YOUR_GENERATED_APP_KEY_HERE
APP_DEBUG=false
APP_URL=https://api.checkpass.ng

LOG_CHANNEL=stack
LOG_LEVEL=error

# Database Configuration (PostgreSQL / MySQL recommended for production)
DB_CONNECTION=pgsql
DB_HOST=127.0.0.1
DB_PORT=5432
DB_DATABASE=checkpass_production
DB_USERNAME=checkpass_user
DB_PASSWORD=YOUR_STRONG_PASSWORD

# Session & Cache (Redis recommended for multi-instance high throughput)
CACHE_STORE=database
SESSION_DRIVER=database
QUEUE_CONNECTION=database

# CORS Configuration
CORS_ALLOWED_ORIGINS=https://checkpass.ng,https://admin.checkpass.ng,https://app.checkpass.ng

# Payment Gateway (Paystack Keys from Dashboard)
PAYSTACK_PUBLIC_KEY=your_paystack_public_key_here
PAYSTACK_SECRET_KEY=your_paystack_secret_key_here
PAYSTACK_PAYMENT_URL=https://api.paystack.co

# Termii SMS / WhatsApp Gateway (Optional for instant gate dispatch)
TERMII_API_KEY=your_termii_api_key
TERMII_SENDER_ID=CheckPass
```

### 3.2 Web Admin & Landing (`checkpass-admin/.env.production`)
```ini
VITE_API_URL=https://api.checkpass.ng/api
VITE_APP_NAME="CheckPass Estate OS"
VITE_APP_ENV=production
```

### 3.3 Resident & Guard Mobile App (`checkpass-app/.env.production`)
```ini
VITE_API_URL=https://api.checkpass.ng/api
VITE_APP_NAME="CheckPass"
```

---

## 4. Production Build & Compilation Steps

### 4.1 Admin Portal & Marketing Website (`checkpass-admin`)
```bash
cd checkpass-admin

# Install dependencies (clean install)
npm ci

# Type-check and production build
npm run build
```
* **Build Output:** `checkpass-admin/dist/`
* **Static Assets:** Includes `dist/landing.css`, `dist/docs/CheckPass_Master_Services_Agreement_and_DPA.pdf`, and pre-compressed Phosphor icons.

### 4.2 Resident Mobile App (`checkpass-app`)
```bash
cd ../checkpass-app

# Install dependencies
npm ci

# Build web assets for Capacitor
npm run build

# Synchronize assets into iOS & Android native containers
npx cap sync
```

#### Android Production APK / AAB:
```bash
cd android
./gradlew bundleRelease
# Output located at: android/app/build/outputs/bundle/release/app-release.aab
```

#### iOS Archive:
```bash
npx cap open ios
# Xcode -> Product -> Archive -> Distribute App
```

### 4.3 Backend API (`checkpass-api`)
```bash
cd ../checkpass-api

# Install production dependencies without dev tools
composer install --no-dev --optimize-autoloader

# Run database migrations
php artisan migrate --force

# Create public storage symlink for receipts and item photos
php artisan storage:link

# Cache config, routes, and views for sub-10ms response times
php artisan config:cache
php artisan route:cache
php artisan view:cache
php artisan event:cache
```

---

## 5. Server Deployment Configurations

### 5.1 Nginx Web Server Configuration (`/etc/nginx/sites-available/checkpass.conf`)
```nginx
# 1. Admin Portal & Marketing Landing Page
server {
    listen 80;
    listen 443 ssl http2;
    server_name checkpass.ng admin.checkpass.ng;
    root /var/www/checkpass/checkpass-admin/dist;
    index index.html;

    # SSL Certificates (managed via Certbot)
    ssl_certificate /etc/letsencrypt/live/checkpass.ng/fullchain.pem;
    ssl_certificate_key /etc/letsencrypt/live/checkpass.ng/privkey.pem;
    ssl_protocols TLSv1.2 TLSv1.3;

    # Gzip Compression
    gzip on;
    gzip_types text/plain text/css application/json application/javascript text/xml application/xml image/svg+xml;

    location / {
        try_files $uri $uri/ /index.html;
    }

    # Static assets cache header (1 year)
    location ~* \.(?:ico|css|js|gif|jpe?g|png|webp|woff2?|svg|pdf)$ {
        expires 1y;
        add_header Cache-Control "public, max-age=31536000, immutable";
        access_log off;
    }
}

# 2. REST API Backend
server {
    listen 80;
    listen 443 ssl http2;
    server_name api.checkpass.ng;
    root /var/www/checkpass/checkpass-api/public;
    index index.php;

    ssl_certificate /etc/letsencrypt/live/checkpass.ng/fullchain.pem;
    ssl_certificate_key /etc/letsencrypt/live/checkpass.ng/privkey.pem;
    ssl_protocols TLSv1.2 TLSv1.3;

    add_header X-Frame-Options "SAMEORIGIN";
    add_header X-Content-Type-Options "nosniff";
    add_header X-XSS-Protection "1; mode=block";

    location / {
        try_files $uri $uri/ /index.php?$query_string;
    }

    location ~ \.php$ {
        fastcgi_pass unix:/var/run/php/php8.2-fpm.sock;
        fastcgi_param SCRIPT_FILENAME $realpath_root$fastcgi_script_name;
        include fastcgi_params;
        fastcgi_hide_header X-Powered-By;
    }

    location ~ /\.(?!well-known).* {
        deny all;
    }
}
```

### 5.2 Systemd Process Monitor for Queue Worker (`/etc/systemd/system/checkpass-worker.service`)
```ini
[Unit]
Description=CheckPass Background Queue Worker
After=network.target

[Service]
User=www-data
Group=www-data
Restart=always
ExecStart=/usr/bin/php /var/www/checkpass/checkpass-api/artisan queue:work --sleep=3 --tries=3 --max-time=3600

[Install]
WantedBy=multi-user.target
```

---

## 6. Post-Deployment Smoke Test & Verification Checklist

Verify these 6 critical flows immediately following production deployment:

- [ ] **1. Landing Page Fluidity & Responsiveness:**
  - Visit `https://checkpass.ng`.
  - Check hero headline auto-scaling, fixed navbar with blur on scroll, and CTA button sizes.
- [ ] **2. Interactive Bento Slider Showcase:**
  - Navigate to `#features` (Features Page).
  - Verify that the bulky left-column buttons are removed and all 4 stage cards cycle automatically with in-card nav dots and status pills:
    - Feature 1: WhatsApp Pass, Optical Scanner, Offline Cryptography, Live Gate Stream.
    - Feature 2: Automated Dues, Stanbic IBTC Teller Match, Direct Bank Routing, AGM Export.
    - Feature 3: Move-Out Manifest, Optical Serial Match HUD, Dual Sign-off Seals, Gate Barrier Lift.
    - Feature 4: Multi-Barrier Sync, Resident Census Directory, SOS Dispatch, KPI Grid.
- [ ] **3. Legal Compliance & Direct PDF Download:**
  - Click footer **"Master Services Agreement (MSA & DPA)"** link.
  - Verify modal opens smoothly, scrolls properly without freezing, and clicking **"Download Official PDF Agreement"** immediately triggers a direct download of `CheckPass_Master_Services_Agreement_and_DPA.pdf`.
- [ ] **4. AI Concierge & Super Admin CRM Lead Capture:**
  - Click floating green concierge pill at bottom right.
  - Tap *"Book a 15-min live demo"*.
  - Confirm lead record is captured and visible in Super Admin CRM at `/crm`.
- [ ] **5. Estate Manager Portal & Dues Ledger:**
  - Sign in as Estate Admin.
  - Check that service charge payments show both online card payment and manual bank transfer receipt upload.
- [ ] **6. Gate Security Offline Scanner:**
  - Sign in as Guard (`GUARD-MAIN-GATE`).
  - Scan test QR pass; ensure token decodes under 4 seconds with offline cryptographic fallback.

---

## 7. Emergency Rollback Strategy

If a critical flaw is detected post-deployment:

### Immediate Git Rollback:
```bash
cd "c:\Users\DELL\Documents\Code Base\Checkpass\GatePass"

# 1. View recent deployment commit hash on main
git log -n 5 --oneline main

# 2. Revert the merge commit or hard reset main to previous stable release
git checkout main
git reset --hard HEAD~1
git push origin main --force-with-lease

# 3. Re-trigger deployment pipeline or re-run build scripts
```

### Database Migration Rollback:
```bash
cd checkpass-api
php artisan migrate:rollback --step=1
php artisan config:clear
php artisan cache:clear
```
