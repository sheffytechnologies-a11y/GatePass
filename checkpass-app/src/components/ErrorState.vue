<!-- src/components/ErrorState.vue -->
<template>
  <div class="error-state">
    <ion-icon :icon="alertCircleOutline" class="error-icon" />
    <div class="error-msg">{{ message }}</div>
    <ion-button fill="outline" color="medium" size="small" @click="$emit('retry')">
      Try again
    </ion-button>
  </div>
</template>

<script setup lang="ts">
import { IonIcon, IonButton } from '@ionic/vue'
import { alertCircleOutline } from 'ionicons/icons'
defineProps<{ message: string }>()
defineEmits(['retry'])
</script>

<style scoped>
.error-state { display: flex; flex-direction: column; align-items: center; padding: 32px 24px; gap: 10px; text-align: center; }
.error-icon  { font-size: 36px; color: var(--w-muted); }
.error-msg   { font-size: 14px; color: var(--w-muted); }
</style>
