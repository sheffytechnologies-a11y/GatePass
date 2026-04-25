<template>
  <div v-if="open" class="scanner-overlay" @click.self="closeSheet">
    <div class="scanner-sheet card">
      <button class="scanner-close" @click="closeSheet">✕</button>

      <template v-if="!scannerSupported">
        <div class="scanner-state">
          <div class="scanner-icon">⌨️</div>
          <h3>Enter phone number manually</h3>
          <p>This device does not support live QR scanning. Enter the visitor phone number to find the pass.</p>
          <input v-model="manualCode" class="form-input" type="tel" placeholder="Enter visitor phone number" />
          <button class="btn btn-primary scanner-action" @click="submitManualCode">Use phone number</button>
        </div>
      </template>

      <template v-else>
        <div class="scanner-state">
          <div class="scanner-icon">📷</div>
          <h3>Scan visitor pass</h3>
          <p>Point the camera at the visitor QR code. If scanning fails, enter the visitor phone number manually.</p>
        </div>

        <div class="video-frame">
          <video ref="videoRef" autoplay playsinline muted class="scanner-video" />
          <div class="scan-window"></div>
        </div>

        <div class="scanner-manual">
          <input v-model="manualCode" class="form-input" type="tel" placeholder="Enter visitor phone number" />
          <button class="btn btn-outline" @click="submitManualCode">Use phone number</button>
        </div>
      </template>
    </div>
  </div>
</template>

<script setup lang="ts">
import { nextTick, onBeforeUnmount, ref, watch } from 'vue'

const props = defineProps<{ open: boolean }>()
const emit = defineEmits<{ close: []; scanned: [value: string] }>()

const videoRef = ref<HTMLVideoElement | null>(null)
const manualCode = ref('')
const stream = ref<MediaStream | null>(null)
const scannerSupported = typeof navigator !== 'undefined' && !!navigator.mediaDevices && typeof window !== 'undefined' && !!(window as any).BarcodeDetector
let scanTimer: number | null = null

async function startScanner() {
  if (!scannerSupported) return
  await nextTick()

  stream.value = await navigator.mediaDevices.getUserMedia({
    video: { facingMode: { ideal: 'environment' } },
    audio: false,
  })

  if (videoRef.value) {
    videoRef.value.srcObject = stream.value
  }

  const Detector = (window as any).BarcodeDetector
  const detector = new Detector({ formats: ['qr_code'] })

  scanTimer = window.setInterval(async () => {
    if (!videoRef.value || videoRef.value.readyState < 2) return
    try {
      const results = await detector.detect(videoRef.value)
      if (results?.length) {
        const value = String(results[0].rawValue || '').trim()
        if (value) {
          emit('scanned', value)
          closeSheet()
        }
      }
    } catch {
      // Ignore intermittent detection failures.
    }
  }, 900)
}

function stopScanner() {
  if (scanTimer !== null) {
    window.clearInterval(scanTimer)
    scanTimer = null
  }

  stream.value?.getTracks().forEach((track) => track.stop())
  stream.value = null
}

function closeSheet() {
  stopScanner()
  emit('close')
}

function submitManualCode() {
  const value = manualCode.value.trim()
  if (!value) return
  emit('scanned', value)
  closeSheet()
}

watch(() => props.open, async (open) => {
  if (open) {
    manualCode.value = ''
    try {
      await startScanner()
    } catch {
      stopScanner()
    }
  } else {
    stopScanner()
  }
}, { immediate: true })

onBeforeUnmount(stopScanner)
</script>

<style scoped>
.scanner-overlay {
  position: fixed;
  inset: 0;
  z-index: 30;
  background: rgba(7, 16, 12, 0.64);
  display: flex;
  align-items: flex-end;
  justify-content: center;
  padding: 16px;
}
.scanner-sheet {
  width: min(var(--shell-width), 100%);
  border-radius: 28px;
  padding: 18px;
  position: relative;
}
.scanner-close {
  position: absolute;
  top: 14px;
  right: 14px;
  width: 36px;
  height: 36px;
  border-radius: 50%;
  border: none;
  background: #edf2ee;
  color: var(--c-text);
}
.scanner-state { padding: 10px 4px 16px; }
.scanner-icon { font-size: 28px; margin-bottom: 8px; }
.scanner-state h3 { font-size: 22px; font-family: var(--font-display); line-height: 1.05; }
.scanner-state p { margin-top: 8px; color: var(--c-muted); font-size: 13px; }
.video-frame {
  position: relative;
  border-radius: 22px;
  overflow: hidden;
  background: #102a21;
  aspect-ratio: 3 / 4;
}
.scanner-video { width: 100%; height: 100%; object-fit: cover; display: block; }
.scan-window {
  position: absolute;
  inset: 18% 16%;
  border: 2px solid rgba(255,255,255,0.86);
  border-radius: 26px;
  box-shadow: 0 0 0 9999px rgba(0,0,0,0.24);
}
.scanner-manual {
  display: grid;
  grid-template-columns: 1fr auto;
  gap: 10px;
  margin-top: 14px;
}
.scanner-action { margin-top: 12px; justify-content: center; }
</style>