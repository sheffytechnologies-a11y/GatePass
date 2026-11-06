# CheckPass Onboarding Guide & Interactive Tooltip Tour Architecture
*A Comprehensive UX, Flow, Content & Technical Blueprint for Web Admin & Mobile App*

---

## 1. Executive Summary & Core Philosophy

When a user registers a new account on **Checkpass**—whether an **Estate Admin** setting up their community on the web dashboard or a **Resident/Security Guard** opening the mobile app for the first time—the initial 60 seconds dictate their platform adoption and trust.

A modern product tour must **never feel like an intrusive tutorial or a wall of modal popups**. Modern enterprise products (Linear, Stripe, Superhuman, Notion) follow five ironclad principles:

1. **Personal First Touch**: Address the user immediately by their actual first name (e.g., *"Welcome, Adebayo!"*), framing the experience around their estate, not generic software modules.
2. **Action-Driven Value, Not UI Labels**: Tooltips do not say *"This is the Properties button"*; they say *"Structure Your House Units: Add blocks, flats, and plots so residents and guards have accurate addresses mapped in seconds."*
3. **Respectful Autonomy with Instant Skip**: Every step must have a visible, un-shamed **"Skip tour"** action. Never trap the user; if they want to explore freely, they can dismiss it in one click.
4. **Spotlight Focus & Visual Hierarchy**: The target element is elevated with a clean spotlight cutout and subtle backdrop dimming (`rgba(11, 31, 23, 0.55)`), keeping the user's attention anchored on the relevant feature.
5. **Replayability & Zero Nagging**: State is persisted (`localStorage` + user profile sync). Once completed or skipped, it never pops up uninvited again, but can be relaunched anytime from the **Help & Support** or **Profile** menu.

```
┌────────────────────────────────────────────────────────────────────────┐
│                        CHECKPASS ONBOARDING SPECTRUM                  │
├────────────────────────────────────────────────────────────────────────┤
│                                                                        │
│   [First Login] ──► [Personal Welcome Card] ──► [Spotlight Tour]      │
│                            │ (Skip Anytime)            │ (3-4 Steps)   │
│                            ▼                           ▼               │
│                    [Active Dashboard]        [Celebration Finish]      │
│                            ▲                           │               │
│                            └───────────────────────────┘               │
│                                (Persist Completion)                    │
└────────────────────────────────────────────────────────────────────────┘
```

---

## 2. Tooltip & Walkthrough Anatomy (Modern Standards)

### 2.1 Component Visual Hierarchy
A modern tooltip tour component consists of 5 modular layers:

```
┌──────────────────────────────────────────────────────────┐
│ 1. Scrim / Backdrop   : Semi-transparent backdrop with  │
│                         smooth blur and cutout spotlight │
├──────────────────────────────────────────────────────────┤
│ 2. Floating Popover   : Elevated card (16px radius,     │
│                         brand border, high contrast)     │
├──────────────────────────────────────────────────────────┤
│ 3. Header & Stepper   : "Step 1 of 4" pill + Close (X)  │
├──────────────────────────────────────────────────────────┤
│ 4. Content Area       : Punchy Title + 1-2 sentence body │
│                         (Warm, confident, jargon-free)   │
├──────────────────────────────────────────────────────────┤
│ 5. Action Footer      : "Skip tour" (secondary text)    │
│                         "Back" / "Next" (primary green)  │
└──────────────────────────────────────────────────────────┘
```

### 2.2 Micro-Interactions & Animation Specs
- **Entrance**: Scale from `0.96` to `1.0` with `translateY(4px) -> translateY(0)` over `240ms` cubic-bezier `(0.16, 1, 0.3, 1)`.
- **Spotlight Transition**: When moving between target DOM elements, the spotlight smoothly shifts its coordinates and dimensions using `transition: all 300ms cubic-bezier(0.16, 1, 0.3, 1)`.
- **Beacon Glow**: On the target element, a subtle pulsing beacon (`#0A5B38` halo) directs peripheral vision without causing visual fatigue.
- **Keyboard Navigation**:
  - `Enter` or `Right Arrow`: Advance to next step.
  - `Left Arrow`: Go back to previous step.
  - `Escape`: Instantly dismiss and persist skip.

---

## 3. Web Admin Onboarding Walkthrough (`checkpass-admin`)

### 3.1 Persona & Mental Model
- **Target User**: Estate Developer, Facilities Manager, Residents' Association (Exco) Chairman.
- **Core Motivation**: *"I just signed up. How do I get my estate off paper logbooks and start issuing passes?"*
- **Max Steps**: 4 curated steps (avoids the 5+ step drop-off cliff).

---

### 3.2 Step-by-Step Admin Tour Blueprint

#### **Step 0: The Personalized Welcome Modal (Centred)**
*Triggers immediately upon landing on `/dashboard` if `checkpass_admin_tour_seen` is falsy.*

- **Target**: Viewport Center Modal.
- **Badge**: `👋 Quick Estate Setup`
- **Headline**: `Welcome to Checkpass, {{ firstName }}!`
- **Body**: 
  > *"Let's take a 60-second tour to show you how to structure your estate, onboard residents, and monitor visitor clearance in real time."*
- **Actions**:
  - Primary: `Start 1-Min Tour →`
  - Secondary: `I'll explore on my own` (Skip)

---

#### **Step 1: Estate & Multi-Workspace Switcher**
- **DOM Selector**: `.topbar-actions .estate-switcher` (or `.hero-switcher-row`)
- **Badge**: `Step 1 of 4 · Workspace`
- **Headline**: `Your Estate Command Center`
- **Body**: 
  > *"Managing multiple zones or sister estates? Switch between communities or update your active property configuration anytime from this switcher."*
- **Position**: `bottom-end`
- **Actions**: `Skip tour` | `Next Step →`

---

#### **Step 2: Properties & House Units**
- **DOM Selector**: `.side-nav a[href='/properties']`
- **Badge**: `Step 2 of 4 · Structure`
- **Headline**: `Add Blocks, Flats & Plots`
- **Body**: 
  > *"Every resident is anchored to a home unit. Configure your streets, buildings, and unit numbers here so all access passes map to verified residences."*
- **Position**: `right-start` (desktop) / `bottom` (mobile)
- **Actions**: `Back` | `Next Step →`

---

#### **Step 3: Residents & Security Guards**
- **DOM Selector**: `.side-nav a[href='/residents']`
- **Badge**: `Step 3 of 4 · Occupants & Gate`
- **Headline**: `Onboard Residents & Gate Staff`
- **Body**: 
  > *"Invite residents with their mobile number. They receive instant access to create visitor invites and pay dues, while guards get login details for the optical scanner."*
- **Position**: `right-start`
- **Actions**: `Back` | `Next Step →`

---

#### **Step 4: Passes & Gate Clearance Logs**
- **DOM Selector**: `.side-nav a[href='/passes']`
- **Badge**: `Step 4 of 4 · Access Control`
- **Headline**: `Real-Time Visitor Verification`
- **Body**: 
  > *"Monitor every optical QR scan, active guest pass, and move-out asset verification in real time. No paper logs, no missing entries."*
- **Position**: `right-start`
- **Actions**: `Back` | `Finish Tour ✨`

---

#### **Step 5: Completion Toast / Milestone Card**
- **Headline**: `You're Ready, {{ firstName }}!`
- **Body**: *"Start by adding your first unit or jump straight into the dashboard."*
- **Actions**: `+ Add First Property` | `Go to Dashboard`

---

## 4. Mobile App Onboarding Walkthrough (`checkpass-app`)

The mobile app has **two completely distinct personas**:
1. **Resident**: Needs to issue a visitor pass in 2 taps, pay estate levies, or trigger an emergency SOS.
2. **Security Guard**: Needs to scan QR passes, verify offline phrases, and inspect move-outs.

---

### 4.1 Resident Flow Walkthrough (On `HomeView.vue`)

#### **Step 0: Resident Welcome Card**
- **Headline**: `Welcome Home, {{ firstName }}!`
- **Body**: *"Here's a 30-second guide to managing visitor access, paying estate dues, and contacting gate security from your phone."*
- **Actions**: `Show Me How →` | `Skip`

#### **Step 1: The Centre Fast-Pass Button (`+`)**
- **DOM Selector**: `.tab-create .create-fab` (The central round floating button)
- **Badge**: `1 of 4 · Quick Passes`
- **Headline**: `Issue Guest Invites in 2 Taps`
- **Body**: *"Tap the '+' button anytime to create a digital QR pass and share it directly with your visitor via WhatsApp or SMS."*
- **Position**: `top-center` (pointing down to bottom bar)
- **Actions**: `Skip` | `Next →`

#### **Step 2: Gate Entry & Active Passes**
- **DOM Selector**: `.pass-list` (or first Pass Card)
- **Badge**: `2 of 4 · Gate Clearance`
- **Headline**: `Live Verification Updates`
- **Body**: *"See exactly when your guest arrives at the gate and is cleared by security. No intercom calls needed."*
- **Position**: `bottom-center`
- **Actions**: `Back` | `Next →`

#### **Step 3: Estate Dues & Service Charges**
- **DOM Selector**: `ion-tab-button[tab='fees']`
- **Badge**: `3 of 4 · Payments`
- **Headline**: `Zero-Dispute Levy Payments`
- **Body**: *"View pending service charges, pay securely, and download verified digital receipts ready for estate audits."*
- **Position**: `top-center`
- **Actions**: `Back` | `Next →`

#### **Step 4: Emergency SOS Alert**
- **DOM Selector**: `ion-tab-button[tab='emergency']`
- **Badge**: `4 of 4 · Safety`
- **Headline**: `Instant Security Dispatch`
- **Body**: *"In an emergency, trigger a direct alert to the main guardhouse and estate management with your exact home unit location."*
- **Position**: `top-center`
- **Actions**: `Back` | `Got It! ✨`

---

### 4.2 Security Guard Flow Walkthrough (On `HomeView.vue`)

#### **Step 1: The Optical QR Scanner**
- **DOM Selector**: `.scan-qr-btn`
- **Badge**: `1 of 3 · Verification`
- **Headline**: `Sub-4s QR Gate Clearance`
- **Body**: *"Point your camera at the visitor's QR code on their phone or printed slip to confirm host resident and unit instantly."*

#### **Step 2: Visitors Without Phones (Passphrase Entry)**
- **DOM Selector**: `.phrase-entry-btn`
- **Badge**: `2 of 3 · Offline Access`
- **Headline**: `Passphrase Verification`
- **Body**: *"If a visitor arrives with a dead battery, verify the host unit's daily access passphrase to clear them securely."*

#### **Step 3: Live On-Premises Roster**
- **DOM Selector**: `.security-stats`
- **Badge**: `3 of 3 · Gate Census`
- **Headline**: `Track Who Is Inside`
- **Body**: *"Keep a real-time count of active guests, vehicles on-site, and contractor move-out authorisations."*

---

## 5. Modern Content Tone & Copywriting Matrix

| Metric | Traditional Clunky Tooltip | Modern Checkpass Tooltip | Why It Works |
| :--- | :--- | :--- | :--- |
| **Welcome** | *"Welcome to the Application. Click next to proceed."* | *"Welcome, {{ firstName }}! Let's get {{ estateName }} set up in 60 seconds."* | Personal, specific to their estate, sets a realistic time expectation (60s). |
| **Properties** | *"Properties Menu: This is where estate units are configured."* | *"Structure Your Units: Add blocks, flats, and plots so every resident is mapped accurately."* | Explains the real-world operational outcome, not software navigation. |
| **Residents** | *"User Management: Click to add residents and view their records."* | *"Invite Residents & Guards: Send automated login links via WhatsApp or SMS in one tap."* | Highlights the zero-friction communication channels they actually use in Nigeria. |
| **Gate Passes** | *"Visitor Logs: See database entries of visitors."* | *"Real-Time Clearance: Track every optical scan and vehicle entry with zero paper logbooks."* | Focuses on speed (4s) and eliminating paper hassle. |
| **Skip Action** | *"Cancel"* or small hidden `(X)` | `Skip tour` (clear secondary link) | Transparent, unpressured, builds immediate respect and goodwill. |

---

## 6. Technical Architecture & Reusable Component System

### 6.1 State Management (`useOnboardingTour` Composable)
The walkthrough state is encapsulated into a reusable composable for both web and mobile:

```typescript
// src/composables/useOnboardingTour.ts
import { ref, computed } from 'vue'

export interface TourStep {
  id: string
  targetSelector: string
  badge: string
  title: string
  body: string
  position?: 'top' | 'bottom' | 'left' | 'right' | 'center'
}

export function useOnboardingTour(storageKey: string, steps: TourStep[]) {
  const currentStepIndex = ref(0)
  const isTourActive = ref(false)

  const isCompleted = computed(() => {
    return localStorage.getItem(storageKey) === 'completed'
  })

  function startTour(force = false) {
    if (!isCompleted.value || force) {
      currentStepIndex.value = 0
      isTourActive.value = true
    }
  }

  function nextStep() {
    if (currentStepIndex.value < steps.length - 1) {
      currentStepIndex.value++
    } else {
      completeTour()
    }
  }

  function prevStep() {
    if (currentStepIndex.value > 0) {
      currentStepIndex.value--
    }
  }

  function completeTour() {
    isTourActive.value = false
    localStorage.setItem(storageKey, 'completed')
  }

  function skipTour() {
    isTourActive.value = false
    localStorage.setItem(storageKey, 'completed')
  }

  function resetTour() {
    localStorage.removeItem(storageKey)
    startTour(true)
  }

  return {
    currentStepIndex,
    isTourActive,
    isCompleted,
    currentStep: computed(() => steps[currentStepIndex.value]),
    startTour,
    nextStep,
    prevStep,
    completeTour,
    skipTour,
    resetTour,
  }
}
```

---

## 7. Next Steps & Implementation Roadmap

1. **Phase 1: Admin Web Tour Component (`checkpass-admin`)**
   - Create `src/components/common/OnboardingTour.vue` with spotlight masking.
   - Wire `authStore.displayName` to pull the admin's first name.
   - Mount in `AppLayout.vue` so it triggers seamlessly on first `/dashboard` load.

2. **Phase 2: Mobile App Tour Component (`checkpass-app`)**
   - Create `src/components/common/MobileTourCoachmark.vue`.
   - Separate Resident vs Security flows in `HomeView.vue`.
   - Hook into `authStore.onboardingComplete` and `authStore.resident`.

3. **Phase 3: Settings & Help Replay Button**
   - Add a *"Replay Product Tour"* button in user dropdowns on both platforms.
