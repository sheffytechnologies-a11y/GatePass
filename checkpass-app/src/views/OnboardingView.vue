<template>
  <ion-page>
    <ion-content :fullscreen="true" class="onboarding-content">
      <div class="onboarding-wrapper">
        <div class="onboarding-container">

          <!-- Tall Portrait Hero Card (~78% of vertical space) -->
          <div class="hero-card">
            <!-- White Brand Logo (Inside Card at Top - Decreased Size) -->
            <div class="card-logo-wrap">
              <img src="/logo-dark.png" alt="Checkpass" class="card-logo-white" />
            </div>

            <!-- Slide Image (Fills Card) -->
            <transition name="fade-slide" mode="out-in">
              <div :key="step" class="card-image-layer">
                <img :src="slides[step].image" :alt="slides[step].heading" class="card-img" />
              </div>
            </transition>

            <!-- Slide Dots (Inside Card at Bottom) -->
            <div class="card-dots">
              <span
                v-for="i in slides.length"
                :key="i"
                class="card-dot"
                :class="{ active: step === i - 1 }"
                @click="step = i - 1"
              />
            </div>
          </div>

          <!-- Slide Text Content (Left-aligned, reduced size) -->
          <transition name="fade-text" mode="out-in">
            <div :key="step" class="slide-text">
              <h2>{{ slides[step].heading }}</h2>
              <p>{{ slides[step].body }}</p>
            </div>
          </transition>

          <!-- Bottom Action Bar (Single Full-Width Button + Quick Sign In) -->
          <div class="bottom-bar">
            <button
              class="continue-btn"
              :disabled="loading"
              @click="handleNext"
            >
              <ion-spinner v-if="loading" name="crescent" color="light" />
              <span v-else>{{ step < slides.length - 1 ? 'Continue' : 'Get Started' }}</span>
            </button>
            <button class="skip-login-btn" @click="skipToLogin">
              Already registered? <strong class="skip-highlight">Sign In</strong>
            </button>
          </div>

        </div>
      </div>
    </ion-content>
  </ion-page>
</template>

<script setup lang="ts">
import { ref, onMounted } from 'vue'
import { useRouter } from 'vue-router'
import { IonPage, IonContent, IonSpinner } from '@ionic/vue'
import { useAuthStore } from '@/stores/auth'
import { usePushPermission } from '@/composables/useToast'

const router  = useRouter()
const auth    = useAuthStore()
const { requestPermission } = usePushPermission()

const step    = ref(0)
const loading = ref(false)

onMounted(() => {
  const token = localStorage.getItem('w_token')
  if (token) {
    router.replace('/tabs/home')
  }
})

function skipToLogin() {
  auth.completeOnboarding()
  router.replace('/login')
}

const slides = [
  {
    image: '/onboarding-1.jpg',
    heading: 'Invite guests in seconds',
    body: 'Generate a pass, send it on WhatsApp, and your guest walks straight through the gate — no calls, no waiting, no drama.',
  },
  {
    image: '/onboarding-2.jpg',
    heading: 'Stay notified, stay safe',
    body: 'The moment security scans your visitor’s pass at the gate, you get a push alert. And if someone is moving items out, log them first — so nothing leaves unchecked.',
  },
  {
    image: '/onboarding-3.jpg',
    heading: 'Trusted hands within your gates',
    body: 'Plumber, cleaner, electrician — find service providers already known and reviewed by neighbours in your estate. No strangers, no guesswork.',
  },
  {
    image: '/onboarding-4.jpg',
    heading: 'This is how estate life should feel',
    body: 'Dues paid, receipts saved, gate alerts live, help always a tap away. Turn on alerts so Checkpass can keep you posted on what matters most.',
  },
]

async function handleNext() {
  if (step.value < slides.length - 1) {
    step.value++
  } else {
    loading.value = true
    try {
      await requestPermission()
    } finally {
      loading.value = false
      auth.completeOnboarding()
      router.replace('/login')
    }
  }
}
</script>

<style scoped>
.onboarding-content {
  --background: var(--w-bg);
}

/* Outer wrapper centers the phone canvas cleanly on laptops/desktops */
.onboarding-wrapper {
  min-height: 100vh;
  width: 100%;
  display: flex;
  align-items: center;
  justify-content: center;
  background: var(--w-bg);
}

/* Container: occupies viewport on mobile, looks like a framed device on laptop */
.onboarding-container {
  width: 100%;
  max-width: 410px;
  height: 100vh;
  max-height: 100vh;
  display: flex;
  flex-direction: column;
  justify-content: flex-start;
  padding: 26px 20px 24px;
  box-sizing: border-box;
  background: var(--w-bg);
  overflow: hidden;
}

@media (min-width: 600px) {
  .onboarding-wrapper {
    background: #E7E4DD;
    padding: 20px 0;
  }
  .onboarding-container {
    height: 94vh;
    max-height: 840px;
    border-radius: 36px;
    box-shadow: 0 20px 50px rgba(0, 0, 0, 0.12), 0 2px 10px rgba(0, 0, 0, 0.04);
    border: 3px solid #FFFFFF;
    padding: 18px 20px 26px;
  }
}

/* ── Hero Card (Extended to match exact red line specification) ── */
.hero-card {
  position: relative;
  width: 100%;
  height: 58vh;
  max-height: 480px;
  min-height: 320px;
  flex: 0 0 auto;
  border-radius: 26px;
  overflow: hidden;
  background: #0A5C38;
  box-shadow: 0 12px 30px -8px rgba(0, 0, 0, 0.16), 0 3px 10px rgba(0, 0, 0, 0.04);
}

/* White logo overlay at top center inside the card - decreased size */
.card-logo-wrap {
  position: absolute;
  top: 16px;
  left: 0;
  right: 0;
  display: flex;
  align-items: center;
  justify-content: center;
  z-index: 10;
  pointer-events: none;
}

.card-logo-white {
  height: 18px;
  max-width: 110px;
  object-fit: contain;
  filter: brightness(0) invert(1);
  opacity: 0.94;
}

/* Image Layer */
.card-image-layer {
  width: 100%;
  height: 100%;
  position: absolute;
  inset: 0;
}

.card-img {
  width: 100%;
  height: 100%;
  object-fit: cover;
  object-position: center center;
  display: block;
}

/* Dots inside the card at bottom */
.card-dots {
  position: absolute;
  bottom: 14px;
  left: 0;
  right: 0;
  display: flex;
  align-items: center;
  justify-content: center;
  gap: 6px;
  z-index: 10;
}

.card-dot {
  width: 5px;
  height: 5px;
  border-radius: 50%;
  background: rgba(255, 255, 255, 0.42);
  transition: all 0.28s cubic-bezier(0.4, 0, 0.2, 1);
  cursor: pointer;
}

.card-dot.active {
  width: 18px;
  height: 5px;
  border-radius: 3px;
  background: #FFFFFF;
  box-shadow: 0 1px 4px rgba(0, 0, 0, 0.25);
}

/* ── Text Content (Primary green 20% larger header, dark body text) ── */
.slide-text {
  padding: 0 4px;
  text-align: left;
  margin-top: 18px;
  flex: 0 0 auto;
}

.slide-text h2 {
  font-family: var(--w-font-display);
  font-size: 22px;
  line-height: 1.25;
  font-weight: 700;
  color: var(--w-primary, #0A5C38);
  margin: 0 0 8px;
  letter-spacing: -0.015em;
}

.slide-text p {
  font-family: var(--w-font-body);
  font-size: 14px;
  line-height: 1.5;
  color: var(--w-text, #1A1A1A);
  font-weight: 400;
  margin: 0;
}

/* ── Bottom Bar (Significant space between text and button) ── */
.bottom-bar {
  flex: 0 0 auto;
  margin-top: auto;
  padding-top: 18px;
  padding-bottom: 2px;
}

.continue-btn {
  width: 100%;
  height: 50px;
  border-radius: 14px;
  border: none;
  background: var(--w-primary);
  color: #FFFFFF;
  font-family: var(--w-font-display);
  font-size: 16px;
  font-weight: 700;
  letter-spacing: -0.01em;
  display: flex;
  align-items: center;
  justify-content: center;
  cursor: pointer;
  box-shadow: 0 4px 14px rgba(10, 92, 56, 0.22);
  transition: background-color 0.2s, transform 0.1s;
}

.continue-btn:hover {
  background: var(--ion-color-primary-tint, #1a7047);
}

.continue-btn:active {
  transform: scale(0.985);
}

.continue-btn:disabled {
  opacity: 0.75;
  cursor: wait;
}

.skip-login-btn {
  width: 100%;
  border: none;
  background: transparent;
  color: var(--w-muted, #6F7685);
  font-family: var(--w-font-body);
  font-size: 13.5px;
  font-weight: 500;
  padding: 10px 0 4px;
  cursor: pointer;
  display: block;
  text-align: center;
  transition: opacity 0.15s ease;
}

.skip-login-btn:active {
  opacity: 0.7;
}

.skip-highlight {
  color: var(--w-primary, #0A5C38);
  font-weight: 700;
}

/* ── Transitions ── */
.fade-slide-enter-active,
.fade-slide-leave-active {
  transition: opacity 0.26s ease, transform 0.26s ease;
}

.fade-slide-enter-from {
  opacity: 0;
  transform: scale(1.02);
}

.fade-slide-leave-to {
  opacity: 0;
  transform: scale(0.98);
}

.fade-text-enter-active,
.fade-text-leave-active {
  transition: opacity 0.22s ease, transform 0.22s ease;
}

.fade-text-enter-from {
  opacity: 0;
  transform: translateY(6px);
}

.fade-text-leave-to {
  opacity: 0;
  transform: translateY(-6px);
}
</style>
