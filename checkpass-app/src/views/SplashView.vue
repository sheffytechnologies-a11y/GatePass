<template>
  <ion-page>
    <ion-content :fullscreen="true" class="splash-content" @click="handleSkip">
      <div class="splash-screen" :class="{ 'animating': isAnimating, 'flood-active': isFloodActive }">
        
        <!-- Phase 1: Bouncing Dot -->
        <div class="bouncing-dot-wrapper">
          <div class="motion-dot"></div>
        </div>

        <!-- Phase 2: Screen Flood Circle -->
        <div class="screen-fill-circle"></div>

        <!-- Phase 3 & 4: Clockwise Logo Assembly & Staggered Wordmark -->
        <div class="brand-centerpiece">
          <!-- Icon Box with 4 Clockwise Quadrants -->
          <div class="icon-box">
            <svg viewBox="0 0 119 119" fill="none" xmlns="http://www.w3.org/2000/svg">
              <!-- 1. Top-Right Quadrant -->
              <path class="quadrant q-top-right" d="M75.71 0C87.405 0.000315153 96.923 9.32446 97.2344 20.9443C108.739 21.3347 117.971 30.7582 118.021 42.3691L118.079 55.5654L96.5479 55.6592L96.3994 21.5322H62.5127V0H75.71Z" fill="#FFFFFF"/>
              
              <!-- 2. Bottom-Right Quadrant -->
              <path class="quadrant q-bottom-right" d="M118.079 62.6084L118.021 75.8047C117.971 87.4163 108.738 96.8391 97.2324 97.2285C96.9215 108.849 87.4046 118.174 75.709 118.174H62.5127V96.6416H96.3984L96.5469 62.5146L118.079 62.6084Z" fill="#FFFFFF"/>
              
              <!-- 3. Bottom-Left Quadrant -->
              <path class="quadrant q-bottom-left" d="M21.6807 96.6416H55.5674V118.174H42.3701C30.6745 118.174 21.1566 108.849 20.8457 97.2285C9.34033 96.8386 0.108192 87.4159 0.0576172 75.8047L0 62.6084L21.5322 62.5146L21.6807 96.6416Z" fill="#FFFFFF"/>
              
              <!-- 4. Top-Left Quadrant -->
              <path class="quadrant q-top-left" d="M55.5674 21.5322H21.6807L21.5322 55.6592L0 55.5654L0.0576172 42.3691C0.108192 30.7579 9.34032 21.3343 20.8457 20.9443C21.1571 9.32425 30.6748 0 42.3701 0H55.5674V21.5322Z" fill="#FFFFFF"/>
            </svg>
          </div>

          <!-- Staggered Cascading Letters (Sequenced smoothly after logo assembly) -->
          <div class="wordmark-container">
            <span class="wordmark-letter" style="animation-delay: 2.30s">C</span>
            <span class="wordmark-letter" style="animation-delay: 2.35s">h</span>
            <span class="wordmark-letter" style="animation-delay: 2.40s">e</span>
            <span class="wordmark-letter" style="animation-delay: 2.45s">c</span>
            <span class="wordmark-letter" style="animation-delay: 2.50s">k</span>
            <span class="wordmark-letter" style="animation-delay: 2.55s">p</span>
            <span class="wordmark-letter" style="animation-delay: 2.60s">a</span>
            <span class="wordmark-letter" style="animation-delay: 2.65s">s</span>
            <span class="wordmark-letter" style="animation-delay: 2.70s">s</span>
          </div>
        </div>

        <!-- Phase 5: Sheffy Technologies Footer -->
        <div class="footer-attribution">
          <svg width="220" height="54" viewBox="0 0 678 168" fill="none" xmlns="http://www.w3.org/2000/svg">
            <path d="M26.4043 139.483C43.2691 135.863 70.5139 125.157 86.8711 131.944C92.9668 134.361 94.3491 141.19 91.2871 146.601C84.2732 158.606 65.1037 164.505 49.0176 167.816H0V143.056C8.83468 142.604 17.7211 141.258 26.4043 139.483ZM0 35.7949C35.6277 25.436 112.941 20.7025 167.816 46.0342V167.816H90.0508C168.673 143.344 186.494 50.7223 81.5449 76.5059C47.4576 84.8803 13.9384 82.86 37.6025 59.0879C45.5549 51.0994 63.2411 43.25 102.55 39.8926C79.7506 37.6994 34.7986 42.1871 0 55.5938V35.7949ZM167.816 40.7441C137.389 21.5255 77.6592 5.8205 0 16.998V0H167.816V40.7441Z" fill="#FFFFFF"/>
            <path d="M256.091 20.907C286.609 20.907 293.677 25.1479 293.677 42.0284C293.677 42.0284 276.381 42.0284 273.055 42.0284C273.055 39.0348 271.89 37.2886 256.59 37.2886C241.788 37.2886 240.541 37.9538 240.541 40.1159C240.707 41.6958 241.788 41.779 256.424 42.9431C288.771 45.6041 295.756 46.0199 295.756 61.0709C295.756 76.0389 290.101 79.1156 256.673 79.1156C225.906 79.1156 221.166 76.122 221.166 59.0752C221.166 59.0752 238.712 59.0752 241.206 59.0752C241.206 62.5677 242.62 63.3993 257.505 63.3993C271.558 63.3993 273.138 63.4824 273.138 60.8215C273.138 58.9921 271.391 59.3247 256.091 58.0774C232.392 56.1648 220.334 55.0006 220.334 39.9496C220.334 22.6533 230.396 20.907 256.091 20.907Z" fill="#FFFFFF"/>
            <path d="M356.746 20.8239H376.038V79.1156H356.746V58.8258H321.987V79.1156H302.612V20.8239H321.987V41.0306H356.746V20.8239Z" fill="#FFFFFF"/>
            <path d="M387.423 20.8239H451.869V36.956H406.799V42.1116H448.958V57.6616H406.799V62.9835H451.869V79.1156H387.423V20.8239Z" fill="#FFFFFF"/>
            <path d="M461.24 20.7407H523.773V38.5359H480.616V44.1905H514.377V61.3204H480.616V79.1156H461.24V20.7407Z" fill="#FFFFFF"/>
            <path d="M532.556 20.7407H595.088V38.5359H551.931V44.1905H585.692V61.3204H551.931V79.1156H532.556V20.7407Z" fill="#FFFFFF"/>
            <path d="M653.997 20.7407H676.864L648.592 61.9857V79.1156H629.216V61.9857L601.11 20.7407H623.978L638.946 40.9474L653.997 20.7407Z" fill="#FFFFFF"/>
            <path d="M250.015 114.52H237.383V146.357H232.966V114.52H220.334V110.925H250.015V114.52Z" fill="#FFFFFF"/>
            <path d="M285.411 142.763V146.357H261.841V110.925H284.897V114.52H266.257V126.844H283.357V130.439H266.257V142.763H285.411Z" fill="#FFFFFF"/>
            <path d="M312.673 110.207C321.659 110.207 326.384 115.29 327.103 122.222L327.154 122.736H322.738L322.635 122.222C321.608 117.139 318.476 113.698 312.57 113.698C305.33 113.698 300.863 120.014 300.863 128.59C300.863 137.319 305.279 143.584 312.262 143.584C318.168 143.584 321.3 140.144 322.327 135.06L322.43 134.547H326.846L326.795 135.06C326.127 141.479 321.351 147.076 312.006 147.076C301.684 147.076 295.933 139.168 295.933 128.795C295.933 118.012 302.609 110.207 312.673 110.207Z" fill="#FFFFFF"/>
            <path d="M369.58 110.925V146.357H365.164V130.849H345.651V146.357H341.235V110.925H345.651V127.152H365.164V110.925H369.58Z" fill="#FFFFFF"/>
            <path d="M413.15 146.357H406.371L389.682 115.239C389.785 119.398 389.837 124.482 389.837 128.641V146.357H385.42V110.925H392.199L408.887 142.044C408.785 137.884 408.733 132.801 408.733 128.641V110.925H413.047L413.15 146.357Z" fill="#FFFFFF"/>
            <path d="M442.972 110.207C453.756 110.207 459.713 117.806 459.713 128.641C459.713 139.425 453.756 147.076 442.972 147.076C432.189 147.076 426.232 139.425 426.232 128.641C426.232 117.806 432.189 110.207 442.972 110.207ZM442.972 143.584C450.418 143.584 454.783 137.833 454.783 128.641C454.783 119.398 450.418 113.698 442.972 113.698C435.527 113.698 431.162 119.398 431.162 128.641C431.162 137.833 435.527 143.584 442.972 143.584Z" fill="#FFFFFF"/>
            <path d="M497.06 142.763V146.357H473.49V110.925H477.906V142.763H497.06Z" fill="#FFFFFF"/>
            <path d="M521.715 110.207C532.498 110.207 538.455 117.806 538.455 128.641C538.455 139.425 532.498 147.076 521.715 147.076C510.931 147.076 504.975 139.425 504.975 128.641C504.975 117.806 510.931 110.207 521.715 110.207ZM521.715 143.584C529.161 143.584 533.525 137.833 533.525 128.641C533.525 119.398 529.161 113.698 521.715 113.698C514.269 113.698 509.904 119.398 509.904 128.641C509.904 137.833 514.269 143.584 521.715 143.584Z" fill="#FFFFFF"/>
            <path d="M567.586 129.257H580.27V146.357H576.367V140.195L574.775 143.481C572.773 145.741 569.64 147.076 565.583 147.076C555.262 147.076 549.511 139.168 549.511 128.795C549.511 118.012 556.186 110.207 566.251 110.207C575.237 110.207 579.962 115.29 580.68 122.222L580.732 122.736H576.316L576.213 122.222C575.186 117.139 572.054 113.698 566.148 113.698C558.908 113.698 554.44 120.014 554.44 128.59C554.44 137.319 558.857 143.584 565.84 143.584C571.591 143.584 575.854 139.168 575.854 132.852H567.586V129.257Z" fill="#FFFFFF"/>
            <path d="M599.781 146.357H595.365V110.925H599.781V146.357Z" fill="#FFFFFF"/>
            <path d="M639.15 142.763V146.357H615.58V110.925H638.636V114.52H619.996V126.844H637.096V130.439H619.996V142.763H639.15Z" fill="#FFFFFF"/>
            <path d="M663.382 110.207C670.725 110.207 674.577 114.674 675.244 120.733H670.52C670.109 116.266 667.49 113.698 663.023 113.698C659.274 113.698 656.296 115.444 656.296 119.141C656.296 122.993 658.504 124.636 663.896 125.868C671.701 127.666 675.604 130.849 675.604 136.857C675.604 143.071 670.263 147.076 663.434 147.076C655.629 147.076 650.699 143.122 649.929 136.292H654.653C655.68 140.965 658.915 143.584 663.536 143.584C667.593 143.584 670.88 141.376 670.88 137.679C670.88 133.365 668.415 131.414 662.201 129.976C655.937 128.539 651.572 126.022 651.572 120.168C651.572 114.058 656.861 110.207 663.382 110.207Z" fill="#FFFFFF"/>
          </svg>
        </div>

      </div>
    </ion-content>
  </ion-page>
</template>

<script setup lang="ts">
import { ref, onMounted, onBeforeUnmount } from 'vue'
import { useRouter } from 'vue-router'
import { IonPage, IonContent } from '@ionic/vue'

const router = useRouter()
const isAnimating = ref(false)
const isFloodActive = ref(false)
let timer: ReturnType<typeof setTimeout> | null = null
let floodTimer: ReturnType<typeof setTimeout> | null = null

function navigateNext() {
  if (timer) clearTimeout(timer)
  if (floodTimer) clearTimeout(floodTimer)

  const token = localStorage.getItem('w_token')
  const onboarded = localStorage.getItem('w_onboarded') === 'true'

  if (token) {
    router.replace('/tabs/home')
  } else if (onboarded) {
    router.replace('/login')
  } else {
    router.replace('/onboarding')
  }
}

function handleSkip() {
  navigateNext()
}

onMounted(() => {
  isAnimating.value = true

  // Phase 3: Screen background flood transition
  floodTimer = setTimeout(() => {
    isFloodActive.value = true
  }, 1400)

  // Transition automatically after full splash sequence
  timer = setTimeout(() => {
    navigateNext()
  }, 3900)
})

onBeforeUnmount(() => {
  if (timer) clearTimeout(timer)
  if (floodTimer) clearTimeout(floodTimer)
})
</script>

<style scoped>
.splash-content {
  --background: #FFFFFF;
  cursor: pointer;
}

.splash-screen {
  position: relative;
  width: 100%;
  height: 100%;
  min-height: 100vh;
  background-color: #FFFFFF;
  overflow: hidden;
  display: flex;
  flex-direction: column;
  align-items: center;
  justify-content: center;
  user-select: none;
  -webkit-user-select: none;
}

/* ─── PHASE 1 & 2: THE BOUNCING DOT & EXPANDING CIRCLE ─── */
.bouncing-dot-wrapper {
  position: absolute;
  top: 0;
  left: 0;
  width: 100%;
  height: 100%;
  pointer-events: none;
  display: flex;
  align-items: center;
  justify-content: center;
  z-index: 10;
}

.motion-dot {
  width: 22px;
  height: 22px;
  border-radius: 50%;
  background-color: var(--w-primary, #005E34);
  will-change: transform;
  transform: translate3d(0, 0, 0);
}

.screen-fill-circle {
  position: absolute;
  top: 14%;
  left: 50%;
  width: 32px;
  height: 32px;
  margin-left: -16px;
  margin-top: -16px;
  border-radius: 50%;
  background-color: var(--w-primary, #005E34);
  z-index: 8;
  pointer-events: none;
  transform: scale(0.001);
  opacity: 0;
  will-change: transform, opacity;
}

/* ─── PHASE 3 & 4: LOGO & WORDMARK ─── */
.brand-centerpiece {
  position: relative;
  z-index: 20;
  display: flex;
  align-items: center;
  justify-content: center;
  height: 60px;
  will-change: transform;
}

.icon-box {
  width: 54px;
  height: 54px;
  position: relative;
  display: flex;
  align-items: center;
  justify-content: center;
  flex-shrink: 0;
  will-change: transform;
}

.icon-box svg {
  width: 100%;
  height: 100%;
  overflow: visible;
}

.quadrant {
  transform-origin: center;
  will-change: transform, opacity;
  opacity: 0;
}

.wordmark-container {
  display: flex;
  align-items: center;
  overflow: hidden;
  margin-left: 10px;
  height: 54px;
  padding-right: 4px;
}

.wordmark-letter {
  font-family: 'Plus Jakarta Sans', system-ui, -apple-system, sans-serif;
  font-size: 34px;
  font-weight: 700;
  color: #FFFFFF;
  letter-spacing: -0.025em;
  display: inline-block;
  opacity: 0;
  will-change: transform, opacity;
  transform: translate3d(-35px, 0, 0);
}

/* ─── PHASE 5: SHEFFY TECHNOLOGIES SUB-BRAND ─── */
.footer-attribution {
  position: absolute;
  bottom: 44px;
  left: 50%;
  transform: translate3d(-50%, 15px, 0);
  opacity: 0;
  z-index: 20;
  display: flex;
  flex-direction: column;
  align-items: center;
  pointer-events: none;
  will-change: transform, opacity;
}

.footer-attribution svg {
  height: 22px;
  width: auto;
}

/* ─── KEYFRAME ANIMATIONS ─── */
/* Dot Physics: Pure circle drops (slow start -> fast bottom), bounces to top, then smoothly zooms out filling screen */
.animating .motion-dot {
  animation: dotBounceAndFill 1.45s cubic-bezier(0.4, 0, 0.2, 1) forwards;
}

@keyframes dotBounceAndFill {
  0% {
    transform: translate3d(0, -20px, 0) scale(1);
    opacity: 1;
  }
  /* Starts slow */
  16% {
    transform: translate3d(0, 25px, 0) scale(1);
  }
  /* Accelerates down like gravity */
  34% {
    transform: translate3d(0, 160px, 0) scale(1);
  }
  /* Hits close to bottom - PERFECT CIRCLE, NO OVAL */
  48% {
    transform: translate3d(0, 310px, 0) scale(1);
  }
  /* Rebounds back up */
  62% {
    transform: translate3d(0, 120px, 0) scale(1);
  }
  75% {
    transform: translate3d(0, -120px, 0) scale(1);
  }
  /* Reaches top apex */
  84% {
    transform: translate3d(0, -280px, 0) scale(1);
  }
  /* Smoothly zooms out, filling the entire screen with green */
  90% {
    transform: translate3d(0, -280px, 0) scale(8);
  }
  96% {
    transform: translate3d(0, -280px, 0) scale(35);
  }
  100% {
    transform: translate3d(0, -280px, 0) scale(85);
    opacity: 1;
  }
}

.animating.flood-active {
  background-color: var(--w-primary, #005E34);
}

/* Clockwise Assembly (starts right after the screen is filled with green) */
.animating .q-top-right {
  animation: assembleQuadrant 0.30s cubic-bezier(0.34, 1.4, 0.64, 1) 1.50s forwards;
}

.animating .q-bottom-right {
  animation: assembleQuadrant 0.30s cubic-bezier(0.34, 1.4, 0.64, 1) 1.65s forwards;
}

.animating .q-bottom-left {
  animation: assembleQuadrant 0.30s cubic-bezier(0.34, 1.4, 0.64, 1) 1.80s forwards;
}

.animating .q-top-left {
  animation: assembleQuadrant 0.30s cubic-bezier(0.34, 1.4, 0.64, 1) 1.95s forwards;
}

@keyframes assembleQuadrant {
  0% {
    opacity: 0;
    transform: scale(0.65) translate3d(0, 0, 0);
  }
  70% {
    opacity: 1;
    transform: scale(1.06) translate3d(0, 0, 0);
  }
  100% {
    opacity: 1;
    transform: scale(1) translate3d(0, 0, 0);
  }
}

/* Icon glide left */
.animating .icon-box {
  animation: glideIconLeft 0.65s cubic-bezier(0.22, 1, 0.36, 1) 2.25s forwards;
}

@keyframes glideIconLeft {
  0% {
    transform: translate3d(0, 0, 0) scale(1);
  }
  100% {
    transform: translate3d(-4px, 0, 0) scale(0.85);
  }
}

/* Wordmark Letters */
.animating .wordmark-letter {
  animation: revealLetter 0.4s cubic-bezier(0.16, 1, 0.3, 1) forwards;
}

@keyframes revealLetter {
  0% {
    opacity: 0;
    transform: translate3d(-35px, 0, 0) scale(0.9);
  }
  50% {
    opacity: 1;
  }
  100% {
    opacity: 1;
    transform: translate3d(0, 0, 0) scale(1);
  }
}

/* Sheffy Tech Logo */
.animating .footer-attribution {
  animation: revealFooter 0.6s cubic-bezier(0.16, 1, 0.3, 1) 2.9s forwards;
}

@keyframes revealFooter {
  0% {
    opacity: 0;
    transform: translate3d(-50%, 15px, 0);
  }
  100% {
    opacity: 1;
    transform: translate3d(-50%, 0, 0);
  }
}
</style>
