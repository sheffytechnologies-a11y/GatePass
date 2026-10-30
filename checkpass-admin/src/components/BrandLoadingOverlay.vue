<template>
  <Transition name="fade-overlay">
    <div v-if="visible" class="brand-loading-overlay">
      <div class="overlay-spinner-box">
        <BrandSpinner :size="size" :color="color" />
      </div>
    </div>
  </Transition>
</template>

<script setup lang="ts">
import BrandSpinner from './BrandSpinner.vue'

withDefaults(
  defineProps<{
    visible: boolean
    size?: number | string
    color?: string
  }>(),
  {
    size: 92,
    color: '#FFFFFF',
  }
)
</script>

<style scoped>
.brand-loading-overlay {
  position: fixed;
  inset: 0;
  background: rgba(0, 0, 0, 0.48);
  backdrop-filter: blur(4px);
  -webkit-backdrop-filter: blur(4px);
  display: flex;
  align-items: center;
  justify-content: center;
  z-index: 9999;
  user-select: none;
  -webkit-user-select: none;
}

.overlay-spinner-box {
  display: flex;
  align-items: center;
  justify-content: center;
  filter: drop-shadow(0 10px 30px rgba(0, 0, 0, 0.45));
}

.fade-overlay-enter-active,
.fade-overlay-leave-active {
  transition: opacity 0.25s ease;
}

.fade-overlay-enter-from,
.fade-overlay-leave-to {
  opacity: 0;
}
</style>
