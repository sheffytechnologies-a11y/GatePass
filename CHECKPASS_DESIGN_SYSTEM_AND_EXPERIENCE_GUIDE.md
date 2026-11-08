# CheckPass Design System, Experience Flow & Copy Strategy Guide
*The living master reference for visual consistency, user experience flow, motion principles, and tone of voice across the CheckPass platform.*

---

## 1. Brand Identity & Color System

CheckPass is Nigeria's premier institutional estate access, security operations, and financial management platform. The visual identity conveys authority, impenetrable security, institutional calm, and modern operational efficiency.

### Primary Palette
| Token | Hex Value | Role & Usage |
| :--- | :--- | :--- |
| `--p` / Brand Primary | `#0A5B38` | Primary CTA buttons, brand badges, active highlights, key metrics |
| `--p-h` / Brand Hover | `#1A7D51` | Hover state for primary buttons and interactive elements |
| `--p-dk` / Deep Forest | `#073D26` | Deep green borders, active card strokes, dark accents |
| `--p-lt` / Mint Wash | `#E6F2EC` | Light badge backgrounds, subtle highlight fills |
| `--dark` / Obsidian Green | `#0B1F17` | Hero background, dark section cards, footer base |
| `--dark-700` | `#132E21` | Dark card surfaces, container borders in dark mode |
| `--gold` / Warm Amber | `#F4C84A` | Secondary CTAs, accent hero words, "Most Requested" badges |
| `--gold-h` | `#C9A32E` | Hover state for gold action buttons |

### Neutral Palette
| Token | Hex Value | Role & Usage |
| :--- | :--- | :--- |
| `--n50` | `#F9FAF8` | Alternating section backgrounds, page background tint |
| `--n100` | `#F5F5F4` | Subdued card fills, slider tracks, input backgrounds |
| `--n200` | `#E5E5E3` | Standard borders, divider rules, passive timeline tracks |
| `--n400` | `#9CA3A0` | Subtitle text, placeholder text, step indicators |
| `--n700` | `#374038` | Body copy on light backgrounds (accessible > 7:1 contrast) |
| `#0F172A` / Slate 900 | `#0F172A` | Section headers and card titles on light surfaces |

### Container-Matched Color Harmony Rule (Features & Modular Sections)
When showcasing distinct platform capabilities (e.g., Visitor Access, Dues Management, Asset Tracking, Central Command), each feature row or module must maintain visual harmony across three elements:
1. **Container Inside Fill**: The background fill of the graphic stage/card.
2. **First-Line Header Accent**: The colored lead phrase of the section headline.
3. **Perk Badge Icons**: The icon wrapper background and icon glyph.

*Example Mappings:*
* **Visitor Access**: `#D9EBDF` container fill $\rightarrow$ `#005E34` header text $\rightarrow$ `#D9EBDF` icon badge with `#005E34` glyph.
* **Payments & Dues**: `#044B2B` container fill $\rightarrow$ `#044B2B` header text $\rightarrow$ `#D8F2E5` icon badge with `#044B2B` glyph.
* **Asset Tracking**: `#033445` container fill $\rightarrow$ `#02526E` header text $\rightarrow$ `#DDF3FA` icon badge with `#02526E` glyph.
* **Central Command**: `#071F14` container fill $\rightarrow$ `#0A5B38` header text $\rightarrow$ `#E0F4EC` icon badge with `#0A5B38` glyph.

---

## 2. Typography Rules & Hierarchy

### Font Families
* **Display / Headlines**: `'Cabinet Grotesk', sans-serif` (Weights: `800` Extrabold, `700` Bold, `600` Semibold).
* **Body / System Text**: `'DM Sans', 'Satoshi', 'Inter', sans-serif` (Weights: `400` Regular, `500` Medium, `600` Semibold).

### Hierarchy Scale
| Level | Font Family | Size | Weight | Line Height | Letter Spacing |
| :--- | :--- | :--- | :--- | :--- | :--- |
| **Hero Title** | Cabinet Grotesk | `clamp(1.7rem, 3.2vw, 3.1rem)` | 800 | 1.04 | `-0.01em` |
| **Section Title** | Cabinet Grotesk | `clamp(1.7rem, 3.2vw, 3.1rem)` | 800 | 1.04 | `-0.01em` |
| **Card / Step Title** | Cabinet Grotesk | `clamp(1.2rem, 1.4vw, 1.45rem)` | 800 | 1.15 | `-0.01em` |
| **Lead Subtitle** | DM Sans / Satoshi | `clamp(0.98rem, 1.3vw, 1.08rem)` | 400–500 | 1.65–1.72 | `normal` |
| **Body Paragraph** | DM Sans | `0.92rem – 0.96rem` | 400 | 1.65 | `normal` |
| **Kicker / Label** | Cabinet Grotesk | `0.72rem – 0.78rem` | 800 | 1.0 | `0.06em` (Uppercase) |

### Strict Capitalization Rule
* **Hero & Major Headlines**: Always formatted in **Title Case** (Capitalizing the first letter of major words: *"Smarter Access Management"*, *"Instant Clearance. Zero Gate Queues"*).
* **Never use `text-transform: uppercase` on main headlines or display H1/H2 tags**. All-caps should only be reserved for micro-labels, kickers, and status tags (`0.75rem`).

---

## 3. Motion Principles & Micro-Interactions

Every motion in CheckPass must feel mechanical, precise, and authoritative. Avoid bouncy, playful, or cartoonish easing.

1. **Easing Standard**:
   `cubic-bezier(0.16, 1, 0.3, 1)` (snappy ease-out with smooth deceleration).
2. **Synchronized Cinematic Zoom**:
   When background slides cycle alongside progress indicators (e.g., the 6-second Stakeholder showcase), background images smoothly scale from `1.14` down to `1.0` over the exact duration of the progress fill:
   ```css
   @keyframes saZoomOut {
     0% { transform: scale(1.14); }
     100% { transform: scale(1.0); }
   }
   ```
3. **Hover Feedback**:
   * Interactive buttons: `transform: scale(1.02)` or `translateY(-2px)` with subtle shadow enlargement.
   * Icon badges: `transform: scale(1.06)` with background color deepening.
   * Form inputs: subtle border color shift to `#0A5B38` with `box-shadow: 0 0 0 3px rgba(10, 91, 56, 0.12)`.

---

## 4. Copy Strategy & Voice Blueprint

CheckPass speaks directly to Nigerian estate executives, Landlord Associations, Facilities Managers, CDA chairpersons, and Chief Security Officers.

### Core Principles
1. **High Institutional Trust**: We are safeguarding multi-billion Naira residential property assets, private communities, and VIP households. Copy must be serious, organized, and confident.
2. **Anti-Tech Slop**: Never use empty buzzwords like "synergize", "game-changing", or "supercharge". State the exact operational outcome:
   * *Bad:* "Leverage cutting-edge AI to streamline your entrance."
   * *Good:* "Verify visitors in under four seconds without paper logbooks or phone calls."
3. **Ground in Nigerian Estate Realities**:
   * Reference real infrastructure: *Security gatehouses, boom barriers, WhatsApp guest passes, diesel levies, service charge bank receipts, moving trucks, estate bylaws, offline power/network fallbacks.*
   * Recognize the stakeholder hierarchy: *Estate Managers, Exco Committees, Shift Security Captains, Homeowners, and Tenants.*

---

## 5. Living Evolution & Revision Protocol

Whenever a new page, feature, or design pattern is approved:
1. Update this guide with the approved CSS tokens, component structures, and copy patterns.
2. Ensure new pages (Pricing, Contact, Demo Modals) reference these established standards.
3. Maintain zero regression on existing verified sections.

