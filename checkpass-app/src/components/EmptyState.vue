<!-- src/components/EmptyState.vue -->
<template>
  <div class="empty-state">
    <div class="icon">{{ icon }}</div>
    <div class="heading">{{ heading }}</div>
    <div class="body">{{ body }}</div>
    <ion-button v-if="ctaLabel" @click="$emit('cta')" fill="solid" color="primary" class="cta">
      {{ ctaLabel }}
    </ion-button>
  </div>
</template>

<script setup lang="ts">
import { IonButton } from '@ionic/vue'
defineProps<{ icon: string; heading: string; body: string; ctaLabel?: string }>()
defineEmits(['cta'])
</script>

<style scoped>
.empty-state { display: flex; flex-direction: column; align-items: center; padding: 48px 24px; text-align: center; }
.icon   { font-size: 48px; margin-bottom: 16px; }
.heading{ font-weight: 700; font-size: 18px; color: var(--w-text); margin-bottom: 8px; }
.body   { font-size: 14px; color: var(--w-muted); line-height: 1.5; }
.cta    { margin-top: 24px; --border-radius: 12px; }
</style>
