<!-- src/components/NotifBell.vue -->
<template>
  <button class="bell-btn" @click="$emit('click')">
    <ion-icon :icon="notificationsOutline" class="bell-icon" />
    <transition name="badge-pop">
      <span v-if="count > 0" class="badge">{{ count > 9 ? '9+' : count }}</span>
    </transition>
  </button>
</template>

<script setup lang="ts">
import { IonIcon } from '@ionic/vue'
import { notificationsOutline } from 'ionicons/icons'
defineProps<{ count: number }>()
defineEmits(['click'])
</script>

<style scoped>
.bell-btn { background: none; border: none; cursor: pointer; position: relative; padding: 8px; }
.bell-icon { font-size: 24px; color: var(--w-text); }
.badge {
  position: absolute; top: 4px; right: 4px;
  background: var(--w-danger); color: white;
  font-size: 10px; font-weight: 700;
  min-width: 17px; height: 17px;
  border-radius: 10px;
  display: flex; align-items: center; justify-content: center;
  padding: 0 4px;
  border: 2px solid var(--w-surface);
}
.badge-pop-enter-active { animation: check-pop 0.3s ease; }
</style>
