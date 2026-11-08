# CheckPass AI Concierge & Super Admin Sales CRM Implementation Guide

> [!NOTE]
> **Executive Summary**: This document details the end-to-end architecture and implementation of the **CheckPass On-Site AI Concierge** (powered by Google Gemini / OpenAI API) and its direct integration with the **CheckPass Super Admin Sales CRM** (inspired by the open-source [`kargulstudio/sales-crm`](https://github.com/kargulstudio/sales-crm) architecture).

---

## 1. High-Level System Architecture

```
┌────────────────────────────────────────────────────────────────────────────────────────┐
│                               1. CHECKPASS PUBLIC WEBSITE                              │
│                                                                                        │
│  Visitor Inquires:                                                                     │
│  "We have 150 flats in Lekki Phase 1. Does your offline scanner work with our gate?"   │
│                                                                                        │
│                      ▼                                                                 │
│         ┌─────────────────────────┐                                                    │
│         │   On-Site AI Concierge  │                                                    │
│         │   (LandingView.vue)     │                                                    │
│         └────────────┬────────────┘                                                    │
└──────────────────────┼─────────────────────────────────────────────────────────────────┘
                       │
                       ▼
┌────────────────────────────────────────────────────────────────────────────────────────┐
│                                2. AI INTELLIGENCE LAYER                                │
│                                                                                        │
│  • Google Gemini API (gemini-1.5-flash) / OpenAI API (gpt-4o-mini)                     │
│  • Specialized Nigerian Estate Knowledge Prompt (Offline 4s passes, dues, artisans)    │
│  • Automatic Lead Extractor:                                                           │
│      - Estate Name: "Lekki Phase 1 Scheme"                                             │
│      - Contact Person & Phone                                                          │
│      - Units: 150 -> Est. Deal Value: ₦2,160,000/yr                                    │
└──────────────────────┬─────────────────────────────────────────────────────────────────┘
                       │
                       ▼ Real-Time Sync (API & Local Cache)
┌────────────────────────────────────────────────────────────────────────────────────────┐
│                              3. SUPER ADMIN SALES CRM                                  │
│                              (checkpass-admin /crm)                                    │
│                                                                                        │
│  • Top KPI Metrics (Active Pipeline, Deals, AI Inflow, Won Estates)                   │
│  • 6-Stage Kanban Board (New Inquiries -> Discovery -> Demo -> Proposal -> Pilot -> Won│
│  • Full AI Chat Transcript Inspection Drawer                                           │
│  • 1-Click Convert to Free 3-Unit Sandbox Estate Portal                                │
└────────────────────────────────────────────────────────────────────────────────────────┘
```

---

## 2. On-Site AI Concierge Integration (`LandingView.vue`)

The floating assistant at the bottom-right of the CheckPass landing page now features a dual-engine architecture:

### 2.1 Configuration
Add your API key to `checkpass-admin/.env`:

```env
# Option A: Google Gemini API (Recommended for speed & low cost)
VITE_GEMINI_API_KEY="your_google_gemini_api_key_here"

# Option B: OpenAI API
VITE_OPENAI_API_KEY="your_openai_api_key_here"
```

### 2.2 Operational Logic
1. **Dynamic Engine Selection**:
   - If `VITE_GEMINI_API_KEY` is present, it routes to Gemini 1.5 Flash via REST (`https://generativelanguage.googleapis.com/v1beta/models/gemini-1.5-flash:generateContent`).
   - If `VITE_OPENAI_API_KEY` is present, it routes to `gpt-4o-mini` (`https://api.openai.com/v1/chat/completions`).
   - If no API key is configured or the user is offline, it falls back to CheckPass's rule-based expert engine (`getConciergeAnswer`), ensuring **zero downtime**.
2. **Context-Grounding**:
   The AI is injected with CheckPass estate operating knowledge:
   - 4-Second QR passes (no app download needed for guests).
   - Offline guard scanner software (caching cryptographic tokens during cellular downtime).
   - Dual dues payment: Instant online payment (Paystack) vs. Direct Providus Bank transfer with receipt upload for 1-click Exco approval.
   - Vetted artisan directory, Move-out clearance manifest, and 1-tap SOS alerts.
3. **Automated Lead Extraction**:
   Every message sent by the visitor is evaluated by `extractLeadFromConversation()`:
   - Detects phone numbers (`+234...` or `080...`).
   - Detects emails (`name@domain.com`).
   - Detects estate names (e.g. *Victoria Crest Estate*, *Pinnock Beach*, *Megamound*).
   - Detects unit counts (e.g. *120 units*, *80 flats*).
   - Formulates a deal record with estimated ARR: `units × ₦1,200/unit/mo × 12` and writes directly into the CRM storage.

---

## 3. Super Admin Sales CRM (`CrmView.vue`)

Inspired by [`kargulstudio/sales-crm`](https://github.com/kargulstudio/sales-crm), the CheckPass Super Admin CRM is accessible at `/crm`.

### 3.1 Pipeline Stages
The pipeline reflects the Nigerian estate sales cycle:

| Stage | Code | Description |
| :--- | :--- | :--- |
| **New Inquiries** | `new` | Fresh leads from the on-site AI Concierge or landing page form. |
| **Discovery Call** | `discovery` | Understanding gate count, barrier integration (BFT, Centurion), and units. |
| **Exco Demo** | `demo` | 15-minute live walkthrough scheduled with HOA Chairman & Security Head. |
| **Proposal Sent** | `proposal` | Customized quote with tier pricing, SLA, and guard device checklist. |
| **Sandbox Pilot** | `pilot` | 48-hour live gate trial with 3 active resident slots. |
| **Closed Won** | `won` | Signed contract, estate portal deployed, guard training complete. |

### 3.2 Key Features
1. **Top KPI Metrics Bar**:
   - **Active Pipeline Value**: Real-time sum of ARR for all open deals.
   - **Active Estate Deals**: Count of qualifying estates.
   - **AI Concierge Inflow**: Live tally of leads captured by the AI bot.
   - **Sandbox Pilots Active**: Estates currently running 48-hour tests.
   - **Closed Won Estates**: Paying customer count.
2. **Dual View Modes**:
   - **Kanban Board**: Drag-and-drop or 1-tap stage advancement (`←` and `Move to [Next Stage] →`).
   - **Data Table**: Filterable by Nigerian cities (Lagos, Abuja, Port Harcourt, Ibadan), unit count, and search query.
3. **Lead Inspection Drawer**:
   - Displays full contact information (Name, Role, Phone, Email, Location, Units).
   - **AI Inquiry Transcript**: Shows the conversation the visitor had with the on-site AI Concierge so the sales team knows their exact questions before reaching out.
   - **Notes & Activity Log**: Timestamped record of calls, Exco meetings, and next steps.
   - **1-Click Sandbox Provisioning**: Directly routes to `/register/estate` with the lead's estate name, city, and units pre-filled.

---

## 4. Backend API Contract (For Node.js / Laravel Backend)

When syncing CRM leads to the persistent database, use the following endpoints:

### 4.1 Ingest AI / Web Lead
`POST /v1/crm/leads`

```json
{
  "estateName": "Victoria Crest Estate IV",
  "name": "Engr. Babatunde Alao",
  "role": "HOA Chairman",
  "phone": "+2348024035326",
  "email": "babatunde@victoriacrest.ng",
  "city": "Lagos",
  "units": 180,
  "dealValue": 2592000,
  "stage": "new",
  "source": "AI Concierge",
  "chatTranscript": "User: Does your scanner work offline?\nAI: Yes, CheckPass guards verify cryptographic tokens locally...",
  "notes": "Captured via on-site AI Concierge on 08/11/2026."
}
```

### 4.2 Fetch Pipeline Leads
`GET /v1/crm/leads?city=Lagos&stage=all`

### 4.3 Update Stage & Notes
`PATCH /v1/crm/leads/:id`

```json
{
  "stage": "demo",
  "notes": "Exco demo scheduled for Saturday 10:00 AM."
}
```

---

## 5. Webhook Notifications (Slack & WhatsApp)

To alert the CheckPass sales team when a hot estate lead is captured:

1. **Slack / Discord Webhook**:
   Send a notification payload:
   ```
   🚨 *NEW ESTATE LEAD CAPTURED*
   • Estate: Victoria Crest Estate IV (180 Units)
   • Contact: Engr. Babatunde Alao (HOA Chairman)
   • Phone: 0802 403 5326
   • Est. Value: ₦2,592,000 / year
   • Source: AI Concierge
   ```
2. **Instant Super Admin Sync**:
   The Super Admin can click **"Sync AI Inquiries"** on the CRM header to reload live leads stored from the on-site concierge.
