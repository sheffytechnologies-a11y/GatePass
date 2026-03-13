<!-- src/components/SkeletonList.vue -->
<template>
  <div class="skeleton-list">
    <div v-for="i in rows" :key="i" class="skeleton-row">
      <ion-skeleton-text class="sk-avatar" animated />
      <div class="sk-info">
        <ion-skeleton-text class="sk-name" animated />
        <ion-skeleton-text class="sk-meta" animated />
      </div>
      <ion-skeleton-text class="sk-badge" animated />
    </div>
  </div>
</template>

<script setup lang="ts">
import { IonSkeletonText } from '@ionic/vue'
withDefaults(defineProps<{ rows?: number }>(), { rows: 3 })
</script>

<style scoped>
.skeleton-list { display: flex; flex-direction: column; gap: 10px; }
.skeleton-row  { display: flex; align-items: center; gap: 12px; background: var(--w-surface); border-radius: var(--w-radius-md); padding: 14px 16px; }
.sk-avatar { width: 44px; height: 44px; border-radius: 50%; flex-shrink: 0; }
.sk-info   { flex: 1; display: flex; flex-direction: column; gap: 6px; }
.sk-name   { height: 16px; border-radius: 6px; width: 60%; }
.sk-meta   { height: 13px; border-radius: 6px; width: 40%; }
.sk-badge  { height: 22px; width: 70px; border-radius: 20px; flex-shrink: 0; }
</style>
