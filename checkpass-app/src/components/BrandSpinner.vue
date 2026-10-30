<template>
  <div
    class="brand-spinner"
    :style="{
      width: `${size}px`,
      height: `${size}px`,
      color: color,
    }"
    role="status"
    aria-label="Loading..."
  >
    <svg viewBox="0 0 119 119" fill="none" xmlns="http://www.w3.org/2000/svg">
      <!-- 1. Top-Right Quadrant -->
      <path
        class="spinner-quadrant sq-top-right"
        d="M75.71 0C87.405 0.000315153 96.923 9.32446 97.2344 20.9443C108.739 21.3347 117.971 30.7582 118.021 42.3691L118.079 55.5654L96.5479 55.6592L96.3994 21.5322H62.5127V0H75.71Z"
        fill="currentColor"
      />
      <!-- 2. Bottom-Right Quadrant -->
      <path
        class="spinner-quadrant sq-bottom-right"
        d="M118.079 62.6084L118.021 75.8047C117.971 87.4163 108.738 96.8391 97.2324 97.2285C96.9215 108.849 87.4046 118.174 75.709 118.174H62.5127V96.6416H96.3984L96.5469 62.5146L118.079 62.6084Z"
        fill="currentColor"
      />
      <!-- 3. Bottom-Left Quadrant -->
      <path
        class="spinner-quadrant sq-bottom-left"
        d="M21.6807 96.6416H55.5674V118.174H42.3701C30.6745 118.174 21.1566 108.849 20.8457 97.2285C9.34033 96.8386 0.108192 87.4159 0.0576172 75.8047L0 62.6084L21.5322 62.5146L21.6807 96.6416Z"
        fill="currentColor"
      />
      <!-- 4. Top-Left Quadrant -->
      <path
        class="spinner-quadrant sq-top-left"
        d="M55.5674 21.5322H21.6807L21.5322 55.6592L0 55.5654L0.0576172 42.3691C0.108192 30.7579 9.34032 21.3343 20.8457 20.9443C21.1571 9.32425 30.6748 0 42.3701 0H55.5674V21.5322Z"
        fill="currentColor"
      />
    </svg>
  </div>
</template>

<script setup lang="ts">
withDefaults(
  defineProps<{
    size?: number | string
    color?: string
  }>(),
  {
    size: 24,
    color: 'currentColor',
  }
)
</script>

<style scoped>
.brand-spinner {
  display: inline-flex;
  align-items: center;
  justify-content: center;
  position: relative;
  flex-shrink: 0;
  vertical-align: middle;
}

.brand-spinner svg {
  width: 100%;
  height: 100%;
  overflow: visible;
}

.spinner-quadrant {
  transform-origin: 59.5px 59.5px;
  will-change: transform, opacity;
  animation: quadrantPulse 1.0s cubic-bezier(0.4, 0, 0.2, 1) infinite;
}

/* Clockwise Chase Sequence: 1. Top-Right -> 2. Bottom-Right -> 3. Bottom-Left -> 4. Top-Left */
.sq-top-right {
  animation-delay: 0s;
}

.sq-bottom-right {
  animation-delay: 0.25s;
}

.sq-bottom-left {
  animation-delay: 0.50s;
}

.sq-top-left {
  animation-delay: 0.75s;
}

@keyframes quadrantPulse {
  0% {
    opacity: 0.22;
    transform: scale(0.92);
  }
  25% {
    opacity: 1;
    transform: scale(1.06);
  }
  50% {
    opacity: 0.35;
    transform: scale(0.94);
  }
  100% {
    opacity: 0.22;
    transform: scale(0.92);
  }
}
</style>
