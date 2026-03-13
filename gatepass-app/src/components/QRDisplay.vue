<!-- src/components/QRDisplay.vue -->
<template>
  <div class="qr-card">
    <img v-if="qrDataUrl" :src="qrDataUrl" :width="size" :height="size" alt="Pass QR code" />
    <div v-if="passId" class="pass-id">Pass ID: {{ passId }}</div>
  </div>
</template>

<script setup lang="ts">
import QRCode from 'qrcode'
import { ref, watch } from 'vue'

const props = withDefaults(defineProps<{ value: string; size?: number; passId?: string }>(), { size: 220 })
const qrDataUrl = ref('')

watch(
  () => [props.value, props.size] as const,
  async ([value, size]) => {
    if (!value) {
      qrDataUrl.value = ''
      return
    }

    qrDataUrl.value = await QRCode.toDataURL(value, {
      width: size,
      margin: 1,
      errorCorrectionLevel: 'M'
    })
  },
  { immediate: true }
)
</script>

<style scoped>
.qr-card {
  background: var(--w-surface);
  border-radius: var(--w-radius-lg);
  padding: 24px;
  display: flex; flex-direction: column; align-items: center; gap: 14px;
  box-shadow: var(--w-shadow-sm);
}
.pass-id { font-size: 13px; color: var(--w-muted); font-weight: 600; letter-spacing: 0.5px; }
</style>
