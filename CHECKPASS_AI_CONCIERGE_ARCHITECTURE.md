# CheckPass AI Concierge & Floating Website Agent Architecture
*A Comprehensive Study, UX/UI Blueprint & Advanced Feature Expansion for checkpass.ng*

---

## 1. Executive Summary & Vision

The user-submitted reference image showcases a classic floating customer support widget: a floating circular launcher button with an unread message badge (`2`) and a teaser speech bubble preview (*"Hey there! How can we help you?"*).

For **CheckPass**, a standard third-party chatbot (like a generic Zendesk, Intercom, or Tidio iframe) would be a missed opportunity. CheckPass deals with **high-stakes estate security, exco decision-makers, facility managers, and gated communities**.

Instead of a generic support widget, CheckPass needs an **Enterprise Estate Operations AI Concierge**:
- **Branded & Ultra-Lightweight**: Built natively in Vue 3 (0 external widget scripts, 0 tracker lag, 0 monthly third-party subscription).
- **Proactive & Context-Aware**: Adapts its teaser message based on what page section the user is currently inspecting (e.g., Hero vs. How It Works vs. Pricing).
- **Instant Knowledge & Zero Latency**: Instantaneous responses to core questions (WhatsApp passes, offline gate clearance, pricing, guard training).
- **Interactive In-Chat Utilities**: Includes an in-chat Estate Scope Calculator and Interactive Sample Pass Generator.
- **Direct WhatsApp Bridge**: Seamless 1-tap handoff to WhatsApp with conversation context, catering to Nigerian estate managers who conduct business on WhatsApp.

```
┌────────────────────────────────────────────────────────────────────────┐
│                   CHECKPASS AI CONCIERGE FUNNEL                        │
├────────────────────────────────────────────────────────────────────────┤
│                                                                        │
│   [Visitor Browsing] ──► [Contextual Teaser Bubble] ──► [Tap Launcher] │
│                                  │                                     │
│                                  ▼                                     │
│                     [Interactive AI Chat Window]                       │
│                     ├── Quick Inquiry Chips                            │
│                     ├── Instant Estate Scope Calculation               │
│                     ├── Simulated WhatsApp QR Pass                     │
│                     └── Lead Qualification                             │
│                                  │                                     │
│                                  ▼                                     │
│           ┌──────────────────────┴──────────────────────┐              │
│           ▼                                             ▼              │
│   [Book Live Demo]                             [Chat on WhatsApp]      │
│   (Instant Calendar)                           (Pre-filled Context)    │
└────────────────────────────────────────────────────────────────────────┘
```

---

## 2. UI/UX Anatomy of the Floating Widget

### 2.1 The Closed State (Launcher + Teaser Bubble)
```
                                        ┌───────────────────────────────┐
                                        │ 👋 Hey there!                  │
                                        │ How can we help your estate?  │
                                        └───────────────┬───────────────┘
                                                       /
                                                      /
                                              ┌─────┐◄ 
                                              │ [2] │ (Red Notification Badge)
                                           ┌──┴─────┴──┐
                                           │    💬     │ (Brand Emerald #0A5B38)
                                           └───────────┘
```

#### Specifications:
- **Floating Action Button (FAB)**:
  - Dimensions: `58px × 58px`, `border-radius: 50%`.
  - Position: `position: fixed; bottom: 28px; right: 28px; z-index: 9999;`.
  - Background: Brand Forest Green (`#0A5B38`), subtle gradient, box-shadow: `0 12px 32px rgba(10, 91, 56, 0.35)`.
  - Icon: Phosphor `ChatCenteredText` or `Chats` icon in duotone white (`#FFFFFF`).
  - Active/Hover State: `transform: scale(1.06) translateY(-2px);` with gold glow.
- **Unread Notification Badge**:
  - Small circular pill `20px × 20px`, background `#E53E3E`, bold white font `0.72rem`, pulsing alert animation.
- **Floating Teaser Speech Bubble**:
  - Positioned to the left of the button with a small arrow pointer.
  - Automatically slides in after **3.5 seconds** on page load.
  - Features a subtle `(X)` close button so respectful visitors can dismiss it.
  - If untouched for 12 seconds, gently fades out to prevent screen clutter.

---

### 2.2 The Open State (Conversational Drawer)
- **Dimensions**: `width: min(390px, calc(100vw - 32px)); height: min(600px, calc(100vh - 120px));`.
- **Corner Radius**: `24px` with smooth glassmorphism header.
- **Header**:
  - Agent Avatar: CheckPass shield emblem with a pulsing green "Online" beacon.
  - Agent Title: **CheckPass AI Concierge**
  - Subtitle: *"Typically replies instantly · Mon–Sat"*
  - Controls: Minimize `_` and Close `✕`.
- **Body / Message Stream**:
  - Bubble contrast: Agent messages on subtle mint `#EBF8F2`, user messages in `#0A5B38` with white text.
  - Animated 3-dot typing indicator when the AI processes a response.
- **Quick-Suggestion Chips (One-Tap Prompts)**:
  - Horizontal pill strip above the input bar:
    - `📱 How do WhatsApp passes work?`
    - `⚡ What if internet is down at the gate?`
    - `💰 How much does it cost?`
    - `📅 Book a 15-min demo`
- **Input Bar**:
  - Text input with auto-expanding textarea, emoji toggle, and quick Send button.

---

## 3. Recommended Advanced Features & Functions

To make this feature truly world-class and optimized for estate management adoption in Nigeria, we recommend these 5 high-leverage innovations:

### Innovation 1: Proactive Page-Section Context Awareness
Instead of a static *"Hey there! How can we help you?"*, the AI reads which section the visitor is actively viewing:

| Page Section | Visitor Intent | Dynamic Teaser Message |
| :--- | :--- | :--- |
| **Hero / Home** | Exploring value proposition | *"👋 Looking to eliminate gate queues and manual logbooks?"* |
| **How It Works** | Evaluating technical workflow | *"Curious how our WhatsApp guest passes work without app downloads?"* |
| **Features (Access & Moves)** | Security & asset concern | *"Want to know how we verify move-out trucks before opening the gate?"* |
| **Pricing / Scope Planner** | Budget & procurement | *"Want an instant deployment timeline and custom quote for your units?"* |
| **Contact Page** | Ready to speak to a human | *"Want an instant WhatsApp response from our Lagos deployment team?"* |

---

### Innovation 2: Instant Estate Scope & Pricing Calculator (In-Chat)
When a user asks: *"How much will it cost for 150 duplexes in Lekki?"*, the AI does not just output dry text. It renders an **interactive visual calculation card** directly inside the chat feed:

```
┌───────────────────────────────────────────────┐
│ 🏡 Custom Estate Estimate                     │
│ ───────────────────────────────────────────── │
│ Community Size : 150 House Units              │
│ Recommended Tier: Growth Estate Package       │
│ Setup & Training: 1-2 Weeks Managed Onboarding│
│ Guard Terminals : 2 Optical Handheld Scanners │
│ Included       : WhatsApp + SMS Pass Tokens   │
│ ───────────────────────────────────────────── │
│ [ 📅 Book Site Survey ]  [ 💬 Chat on WA ]   │
└───────────────────────────────────────────────┘
```

---

### Innovation 3: Direct WhatsApp Handoff with Full Context
In Nigeria, facility managers and estate chairmen frequently prefer WhatsApp for vendor discussions.
- At any point in the chat, a permanent action button says: **"Continue on WhatsApp →"**.
- Clicking this button compiles the conversation's key details (Estate Name, Number of Units, specific questions asked) into an encoded WhatsApp URL:
  ```
  https://wa.me/2348132489619?text=Hello%20CheckPass%2C%20I%20was%20inquiring%20about%20a%20150-unit%20estate%20in%20Lekki...
  ```
- Result: **Zero lost leads**, seamless transition from automated web AI to personal relationship management.

---

### Innovation 4: Interactive "Live Sample Pass" Demo Inside Chat
When a prospect asks *"What does a visitor pass look like?"*, the AI generates an interactive visual mock pass directly in the conversation:
- Features a real QR code simulation.
- Shows host name, unit address (e.g. *Unit B12, Palm Springs Estate*), and 4-second clearance status.
- Gives the prospect an instant "aha!" moment without leaving the chat.

---

### Innovation 5: Dual-Engine Knowledge Architecture (Hybrid AI)
To ensure **100% uptime, zero latency, and zero ongoing API billing costs** for standard questions, we implement a **2-Tier Engine**:
1. **Tier 1 (Instant Local Semantic Engine)**:
   - Evaluates incoming queries against a curated vector/intent dictionary of CheckPass operations (Pass generation, gate verification, emergency alerts, dues billing, pricing, Nigerian estate regulations).
   - Responds in **under 40 milliseconds** with rich, formatted answers and action chips.
2. **Tier 2 (Generative AI Streaming Endpoint - Optional LLM Backing)**:
   - For open-ended or complex conversational queries, routes seamlessly to an LLM endpoint (Gemini API or backend `/api/ai/concierge`) with streaming tokens.

---

## 4. Full Knowledge Base Blueprint (Pre-Programmed Q&A)

```typescript
export const CHECKPASS_AI_KNOWLEDGE_BASE = [
  {
    category: 'visitor_access',
    intents: ['how it works', 'visitor pass', 'guest invite', 'whatsapp pass', 'qr code'],
    headline: 'Instant WhatsApp QR Guest Invites',
    response: `With CheckPass, residents generate visitor passes in just two taps from their phone. A unique, secure QR code is created and shared directly to the guest via WhatsApp or SMS. When the guest arrives at the gate, security scans the code in under four seconds—no paper logbooks and no intercom phone calls needed.`,
    quickReplies: ['What if there is no internet at gate?', 'How do residents get the app?', 'Book a demo']
  },
  {
    category: 'offline_security',
    intents: ['no internet', 'offline', 'network down', 'bad network', 'mtn down'],
    headline: 'Zero-Downtime Offline Verification',
    response: `CheckPass optical scanners use cryptographic token caching. Even when mobile networks or estate Wi-Fi are completely down, security guards can scan and authenticate valid passes locally without an internet connection. Logs sync automatically once connectivity restores.`,
    quickReplies: ['What equipment do guards need?', 'See pricing', 'Book a demo']
  },
  {
    category: 'pricing_and_setup',
    intents: ['price', 'cost', 'how much', 'setup fee', 'subscription'],
    headline: 'Custom Tailored Estate Packages',
    response: `CheckPass pricing is structured to fit your estate's size—from boutique communities (under 50 units) to master-planned estates (300+ units). Every deployment includes full property configuration, guard handheld devices, resident onboarding, and staff training.`,
    quickReplies: ['Estimate for my estate', 'Chat with sales on WhatsApp', 'Talk to a human']
  },
  {
    category: 'financial_management',
    intents: ['dues', 'service charge', 'levy', 'payment', 'receipt', 'financial'],
    headline: 'Automated Dues & Service Charge Ledger',
    response: `Residents can view pending maintenance levies, diesel fees, and security charges directly on their app. They can pay digitally and receive instant, verifiable receipts. Estate managers get a real-time dashboard showing paid vs. defaulting units ready for AGM auditing.`,
    quickReplies: ['How do audits work?', 'Book a demo', 'Ask another question']
  }
]
```

---

## 5. Technical Architecture & File Structure

We can implement this directly as a sleek, native Vue 3 component inside `checkpass-admin`:

```
checkpass-admin/
├── src/
│   ├── components/
│   │   └── concierge/
│   │       ├── ConciergeWidget.vue       # Main FAB button + Drawer
│   │       ├── ConciergeTeaser.vue       # Floating speech bubble & badge
│   │       ├── ConciergeChatFeed.vue      # Message stream & typing dots
│   │       ├── ConciergeScopeCalc.vue     # Interactive in-chat quote calculator
│   │       └── ConciergeQuickChips.vue    # Actionable prompt pill buttons
│   ├── composables/
│   │   └── useConciergeAI.ts             # State machine, intent matcher, WhatsApp bridge
```

---

## 6. Implementation Plan & Deliverables

1. **Step 1: The Visual Floating Trigger (`ConciergeWidget.vue`)**
   - Clean floating emerald circle with badge counter.
   - Smooth popup teaser bubble with dismiss button.
2. **Step 2: Conversational Engine & Intent Graph (`useConciergeAI.ts`)**
   - Pre-loaded CheckPass knowledge graph covering access, offline verification, pricing, and dues.
   - Dynamic Lead Capture state machine (captures name, units, location).
3. **Step 3: Interactive In-Chat Micro-Apps**
   - Scope Calculator (calculate estimated timeline and setup based on house units).
   - 1-Tap WhatsApp Lead Transfer bridge.
4. **Step 4: Landing Page Integration & Verification**
   - Integrated into `LandingView.vue`.
   - Built and verified with `vue-tsc && vite build`.
