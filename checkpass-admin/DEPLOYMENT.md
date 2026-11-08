# CheckPass Website & Admin Portal Deployment Guide

> **Project:** `checkpass-admin` (CheckPass Marketing Website & Estate Admin Portal)  
> **Framework:** Vue 3 + TypeScript + Vite + Pinia + Vue Router + GSAP / Lenis Smooth Scroll  
> **Target Production Platforms:** Vercel, Netlify, Cloudflare Pages, Docker, Nginx  
> **Repository:** `https://github.com/sheffytechnologies-a11y/GatePass.git`  
> **Path in Repo:** `/checkpass-admin`

---

## 1. Quick Start / Build Commands

```bash
# 1. Install dependencies
npm ci

# 2. Local development server (port 3100)
npm run dev -- --port 3100

# 3. Production build (type-checked with vue-tsc)
npm run build

# 4. Preview compiled production build
npm run preview
```

* **Output Directory:** `dist/`
* **Static Assets:** `dist/assets/`, `dist/landing.css`, `dist/docs/CheckPass_Master_Services_Agreement_and_DPA.pdf`, `dist/checkpass-logo.svg`

---

## 2. Environment Variables Configuration

Create a `.env.production` file in `checkpass-admin/`:

```ini
# Production REST API Endpoint
VITE_API_URL=https://api.checkpass.ng/api

# Application Metadata
VITE_APP_NAME="CheckPass Estate OS"
VITE_APP_ENV=production
```

---

## 3. Platform-Specific Deployment Instructions

### 3.1 Deploying on Vercel
1. Import repository: `https://github.com/sheffytechnologies-a11y/GatePass`
2. Select **Root Directory**: `checkpass-admin`
3. Framework Preset: **Vite**
4. Build Command: `npm run build`
5. Output Directory: `dist`
6. Add SPA rewrite configuration in `checkpass-admin/vercel.json`:
   ```json
   {
     "rewrites": [
       { "source": "/(.*)", "destination": "/index.html" }
     ]
   }
   ```

### 3.2 Deploying on Netlify
1. Create a new site from Git: select `sheffytechnologies-a11y/GatePass`.
2. **Base directory:** `checkpass-admin`
3. **Build command:** `npm run build`
4. **Publish directory:** `checkpass-admin/dist`
5. SPA redirect rule in `checkpass-admin/public/_redirects`:
   ```text
   /*    /index.html   200
   ```

### 3.3 Deploying with Nginx / VPS
```nginx
server {
    listen 80;
    listen 443 ssl http2;
    server_name checkpass.ng admin.checkpass.ng;
    root /var/www/checkpass/checkpass-admin/dist;
    index index.html;

    # SSL configuration
    ssl_certificate /etc/letsencrypt/live/checkpass.ng/fullchain.pem;
    ssl_certificate_key /etc/letsencrypt/live/checkpass.ng/privkey.pem;
    ssl_protocols TLSv1.2 TLSv1.3;

    # Gzip Compression
    gzip on;
    gzip_types text/plain text/css application/json application/javascript text/xml application/xml image/svg+xml;

    location / {
        try_files $uri $uri/ /index.html;
    }

    # Static assets cache headers (1 year)
    location ~* \.(?:ico|css|js|gif|jpe?g|png|webp|woff2?|svg|pdf)$ {
        expires 1y;
        add_header Cache-Control "public, max-age=31536000, immutable";
        access_log off;
    }
}
```

---

## 4. Latest Website Updates & Architecture (v2.4.0)

This deployment includes all the latest frontend architectural enhancements:

1. **Interactive Bento Slider Showcase (All 4 Features):**
   * **Feature 01 (Visitor Access):** WhatsApp 2-Tap Pass, Sub-4s Optical Scanner HUD, Zero-Downtime Cryptography, Live Gate 1 Audit Stream.
   * **Feature 02 (Automated Dues):** Real-time Unit Ledger, Stanbic IBTC OCR Teller Match, Direct Bank Routing (Paystack to HOA), AGM Export.
   * **Feature 03 (Move-Out Manifest):** Itemized Digital Cargo Manifest, Photo & Serial Number HUD (#SAM-INV-889104), Dual Sign-off Seals (Landlord + HOA), Barrier Lift.
   * **Feature 04 (Multi-Gate Console):** Multi-barrier synchronization (Gates 1, 2, 3), Resident & Vehicle Census directory (`LND-492-AA`), 1-Tap Emergency SOS dispatch, Governance KPIs.
2. **Left-Column Cleanup:**
   * Removed bulky stacked card selectors (`.feature-bento-selectors`).
   * Restored clean, legible typography and Phosphor-duotone bullet perks (`.feature-perks`).
3. **Framer Motion Spring Physics:**
   * All slide switches use smooth spring transitions: `cubic-bezier(0.16, 1, 0.3, 1)`.
   * Floating bottom status pills (`.bento-bottom-pill`) update dynamically with active slide state.
   * Auto-cycles every 4 seconds, pauses on hover, and supports touch swiping.
4. **Legal Compliance Center & MSA/DPA Direct PDF Download:**
   * Full NDPA 2023 / GAID 2025 Data Processing Addendum.
   * Fixed modal flexbox scroll locking with `overscroll-behavior: contain`.
   * Direct automatic vector PDF download via `downloadMsaPdf()` (`/docs/CheckPass_Master_Services_Agreement_and_DPA.pdf`).
5. **Fluid Auto-Scaling & Fixed Navigation:**
   * Stakeholder showcase container fluidly adapts to horizontal viewport with fixed 16:9 aspect ratio.
   * Top navigation bar remains permanently fixed on scroll with responsive container and background blur.
   * Top navigation CTA button matches the hero section CTA button dimensions.
6. **Super Admin CRM & Lead Capture:**
   * Synchronized with Laravel API (`POST /api/leads`) from interactive AI concierge.
