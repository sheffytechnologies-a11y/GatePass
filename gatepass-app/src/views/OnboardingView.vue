<template>
  <ion-page>
    <ion-content :fullscreen="true" class="onboarding-content">
      <div class="slides-wrap">

        <!-- Slides -->
        <transition name="slide" mode="out-in">
          <div v-if="step < 3" :key="step" class="slide">
            <div class="slide-illustration">{{ slides[step].emoji }}</div>
            <h2>{{ slides[step].heading }}</h2>
            <p>{{ slides[step].body }}</p>
          </div>

          <!-- Push permission primer (step 3) -->
          <div v-else key="primer" class="slide primer-slide">
            <div class="slide-illustration">🔔</div>
            <h2>Stay in the loop</h2>
            <p>We'll notify you when your visitor arrives, when a pass expires, and if any issue is flagged at the gate.</p>
          </div>
        </transition>

        <!-- Dots -->
        <div class="dots">
          <span v-for="i in 4" :key="i" class="dot" :class="{ active: step === i - 1 }" />
        </div>

        <!-- Actions -->
        <div class="actions">
          <template v-if="step < 3">
            <ion-button expand="block" color="primary" @click="step++" class="next-btn">
              Continue
            </ion-button>
          </template>
          <template v-else>
            <ion-button expand="block" color="primary" @click="enableNotifications" :disabled="loading" class="next-btn">
              <ion-spinner v-if="loading" name="crescent" />
              <span v-else>Turn on notifications</span>
            </ion-button>
            <button class="skip-link" @click="finish">Not now</button>
          </template>
        </div>

      </div>
    </ion-content>
  </ion-page>
</template>

<script setup lang="ts">
import { ref } from 'vue'
import { useRouter } from 'vue-router'
import { IonPage, IonContent, IonButton, IonSpinner } from '@ionic/vue'
import { useAuthStore } from '@/stores/auth'
import { usePushPermission } from '@/composables/useToast'

const router  = useRouter()
const auth    = useAuthStore()
const { requestPermission } = usePushPermission()

const step    = ref(0)
const loading = ref(false)

const slides = [
  {
    emoji: '🚪',
    heading: 'Control who comes in',
    body: 'Create a visitor pass in seconds. Share it. Your guest scans it at the gate.',
  },
  {
    emoji: '📱',
    heading: 'Know when they arrive',
    body: 'Get a notification the moment your visitor is scanned at the gate.',
  },
  {
    emoji: '📦',
    heading: 'Protect what leaves with them',
    body: "Before a visitor exits, declare any items they're taking out. Security sees it instantly.",
  },
]

async function enableNotifications() {
  loading.value = true
  try {
    await requestPermission()
  } finally {
    loading.value = false
    finish()
  }
}

function finish() {
  auth.completeOnboarding()
  router.replace('/tabs/home')
}
</script>

<style scoped>
.onboarding-content { --background: var(--w-bg); }
.slides-wrap {
  min-height: 100vh;
  display: flex; flex-direction: column; justify-content: center;
  padding: 60px 32px 48px;
}
.slide { text-align: center; animation: fade-in-up 0.3s ease; }
.slide-illustration { font-size: 80px; margin-bottom: 32px; }
.slide h2 { font-family: var(--w-font-display); font-size: 26px; font-weight: 700; color: var(--w-text); margin: 0 0 14px; }
.slide p  { font-size: 16px; color: var(--w-muted); line-height: 1.6; margin: 0; }
.dots { display: flex; justify-content: center; gap: 8px; margin: 40px 0; }
.dot  { width: 8px; height: 8px; border-radius: 50%; background: var(--w-border); transition: all 0.3s; }
.dot.active { background: var(--w-primary); width: 24px; border-radius: 4px; }
.actions    { display: flex; flex-direction: column; gap: 12px; }
.next-btn   { --border-radius: 14px; height: 52px; font-size: 16px; font-weight: 700; }
.skip-link  { background: none; border: none; color: var(--w-muted); font-size: 14px; text-align: center; cursor: pointer; padding: 8px; }

/* Slide transition */
.slide-enter-active, .slide-leave-active { transition: all 0.25s ease; }
.slide-enter-from { opacity: 0; transform: translateX(30px); }
.slide-leave-to   { opacity: 0; transform: translateX(-30px); }
</style>
