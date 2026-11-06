# CheckPass Landing Page Architecture & Design System Guide

This document provides a comprehensive technical and design reference for the CheckPass landing page located at `checkpass-admin/src/views/LandingView.vue` and styled via `checkpass-admin/public/landing.css`.

---

## 1. High-Level Architecture

The landing page is a single-page application (SPA) built in Vue 3 (Vite + TypeScript) with a multi-page tabbed switching model (`showPage(pageId)`). It features hardware-accelerated smooth scrolling (Lenis + GSAP ScrollTrigger), interactive stateful components, and mobile-optimized reactive layouts.

### Main Navigation Pages:
* `#page-home` — Hero video banner, live database metrics auto-ticker, Section 2 (Better Property Management), Sections A/B/C overlapping pinned feature scenes, stakeholder grid, SiteAssist interactive showcase, FAQ accordion, footer.
* `#page-how-it-works` — Clean, connected 5-step interactive journey card with Phosphor Duotone icons, active scroll-spy line tracking, and 12-day deployment roadmap.
* `#page-features` — Four core feature pillars (Visitor Access, Financial Ledger, Move-Out Clearance, Central Command) with Phosphor duotone icon badges and signature 3D brand container mockups.
* `#page-pricing` — Multi-tier consultation cards (Boutique, Medium, Enterprise) with dynamic unit slider.
* `#page-contact` — High-converting demonstration and inquiry form with plan pre-selection.

---

## 2. Design System & Brand Identity

### Color Palette:
| Token | Hex | Usage |
|---|---|---|
| **Primary Brand Green** | `#005E34` / `#0A5C38` | Brand headers, primary buttons, active nodes, accents |
| **Accent Mint** | `#96D0B6` / `#82C9AA` | Container highlights, duotone secondary accents, gradient text |
| **Soft Mint Surface** | `#D9EBDF` | Brand container background SVG fills |
| **Teal / Slate (Section C)**| `#033445` / `#50B3D5` | Security terminal and command-center container palette |
| **Interactive Cyan/Blue** | `#0284C7` / `#0EA5E9` | "How it All Works" card header, active tracking line, active badges |
| **Gold Accent** | `#D4AF37` / `#997819` | High-value action buttons, deployment badges, accent text |
| **Neutral Dark** | `#0F172A` / `#1E293B` | High-contrast headlines and body titles |
| **Muted Slate / Outline**| `#94A3B8` / `#CBD5E1` | Inactive/disabled icons, subtle borders, subtext |
| **Canvas Background** | `#FAFAF9` / `#F8FAFC` | Page backgrounds, metrics ticker card surfaces |

### Typography:
* **Display / Headings:** `'Cabinet Grotesk', sans-serif` (800, 700, 600 weight).
* **Body / Narratives:** `'DM Sans', 'Satoshi', 'Inter', sans-serif` (400, 500, 600 weight).
* **Strict Anti-Tag Rule:** No generic kicker tags (e.g. `01 · VISITOR ACCESS` or badge labels) sit on top of copy headers. Headlines are pure, clean, and self-standing.

---

## 3. "How It Works" Interactive Timeline Engine

The "How It Works" page (`#page-how-it-works`) uses a modern 2-column alternating card layout inspired by high-end design systems:

```
[ How it All Works (Cyan/Blue #0284C7) ]
------------------------------------------------------------------
                 |
   (Icon 1)      | === [1] Create Account & Configure Property
   Duotone       | ===     (Step narrative & details)
                 |
   [2] Add Users | === (Icon 2)
   (Step details)| === Duotone
                 |
   (Icon 3)      | === [3] Create Passes & Pay Dues
   Duotone       | ===     (Step narrative & details)
                 |
   [4] Security  | === (Icon 4)
   (Step details)| === Duotone
                 |
   (Icon 5)      | === [5] Central Admin Command
   Duotone       | ===     (Step narrative & details)
                 |
------------------------------------------------------------------
[ ↗ Visit site & Book Demo ]
```

### The Active Line & Duotone Logic:
* **Central Spine Track (`.hiw-spine-track`):** Vertical track centered between columns with a 2px `#E2E8F0` base line and a dynamic 4px `#0284C7` blue line (`.hiw-spine-active`) with a circular indicator node (`.hiw-spine-dot`).
* **Active State:** When the line reaches a step:
  * That step receives `.is-active`.
  * The step's **Phosphor Duotone** icon scales to `1.08`, lights up in vibrant blue (`#0284C7`), and projects a soft radial glow (`.hiw-icon-halo`).
  * The step number badge turns solid blue with white text and active shadow.
* **Disabled State:** When the line leaves a step:
  * The step receives `.is-disabled`.
  * The icon transitions to `grayscale(1)`, opacity `0.35`, and soft outline `#CBD5E1`.
* **Scroll-Spy & Click Support:**
  * Scroll listener dynamically evaluates viewport position and updates `activeHowStep`.
  * Clicking on any step smoothly scrolls the page and animates the active blue line directly to that step.

---

## 4. Icon Library: Phosphor Icons Duotone

CheckPass standardizes on **Phosphor Icons Duotone** via `@phosphor-icons/web/duotone` bundled directly by Vite:

### Key Icons Used:
* `ph-buildings` — Estate creation & house unit mapping
* `ph-users-three` / `ph-user-plus` — Resident directory & security guard onboarding
* `ph-qr-code` / `ph-ticket` — Digital visitor pass generation
* `ph-shield-check` — Sub-4s optical gate scan & offline token verification
* `ph-chart-line-up` — Real-time analytics & executive command
* `ph-device-mobile` — Resident mobile app & WhatsApp link creation
* `ph-clock-counter-clockwise` — Permanent immutable gate entry log
* `ph-wallet` & `ph-receipt` — Dues breakdown & instant payment receipts
* `ph-bell-ringing` — Automated payment reminders
* `ph-package` & `ph-camera` — Move-out photo manifest & appliance declaration
* `ph-clipboard-text` — Security cargo checklist
* `ph-broadcast` & `ph-identification-badge` — Live gate stream & staff census
* `ph-lock-key` — Role-based permission controls
* `ph-file-arrow-down` — One-click AGM financial exports

---

## 5. Mobile Responsiveness Rules

1. **Image-First Hierarchy:**
   * On screens `< 900px`, all visual columns (`.feature-stage-card`, `.section-a-visual-col`, `.section-b-visual-col`, `.section-c-visual-col`) are assigned `order: 1 !important` so visual mockups appear **before** narrative text.
2. **Metrics Ticker Auto-Scroll:**
   * On mobile viewports, `.stats-strip` converts into an infinite horizontal CSS ticker using `@keyframes statsTickerSlide` with a duplicate hidden group for seamless looping.
3. **Timeline Single-Column Docking:**
   * On mobile `< 860px`, `.hiw-spine-track` docks to the left side (`left: 20px`), with step rows stacking vertically with left padding to maintain line-and-badge continuity.

---

## 6. Build & Deployment Commands

```bash
# Navigate to admin frontend
cd checkpass-admin

# Development server (dev port 3100)
npm run dev

# Full production type-check & Vite build
npm run build

# Preview production build locally
npm run preview
```
