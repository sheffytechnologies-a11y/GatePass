<template>
  <ion-modal :is-open="isOpen" @didDismiss="$emit('close')" :breakpoints="[0, 1]" :initial-breakpoint="1">
    <ion-page>
      <ion-content class="scanner-content">
        <div class="scanner-wrap">

          <!-- Close -->
          <button class="close-btn" @click="$emit('close')">✕</button>

          <!-- Idle / ready state -->
          <template v-if="state === 'idle'">
            <div class="icon-ring">
              <span class="qr-icon">📷</span>
            </div>
            <div class="main-title">Scan Visitor Pass</div>
            <div class="main-sub">Open camera and point it at the visitor's QR code</div>
            <button class="scan-btn" @click="capture">Open Camera to Scan</button>
          </template>

          <!-- Processing -->
          <template v-else-if="state === 'processing'">
            <div class="icon-ring icon-ring--spin">
              <ion-spinner name="crescent" color="light" style="width:32px;height:32px" />
            </div>
            <div class="main-title">Reading QR code…</div>
          </template>

          <!-- Not found -->
          <template v-else-if="state === 'notfound'">
            <div class="icon-ring icon-ring--warn">
              <span class="qr-icon">🔍</span>
            </div>
            <div class="main-title">No QR code found</div>
            <div class="main-sub">Make sure the code is clear and well-lit, then try again</div>
            <!-- show captured image for reference -->
            <img v-if="capturedDataUrl" :src="capturedDataUrl" class="preview-img" />
            <button class="scan-btn" @click="capture">Try Again</button>
          </template>

          <!-- Success -->
          <template v-else-if="state === 'success'">
            <div class="icon-ring icon-ring--success">
              <span class="qr-icon">✅</span>
            </div>
            <div class="main-title">Pass found!</div>
            <div class="main-sub">Opening pass details…</div>
          </template>

          <!-- Error -->
          <template v-else-if="state === 'error'">
            <div class="icon-ring icon-ring--warn">
              <span class="qr-icon">⚠️</span>
            </div>
            <div class="main-title">Camera unavailable</div>
            <div class="main-sub">{{ errorMsg }}</div>
            <button class="scan-btn" @click="capture">Try Again</button>
          </template>

        </div>
      </ion-content>
    </ion-page>
  </ion-modal>
</template>

<script setup lang="ts">
import { ref } from 'vue'
import { IonModal, IonPage, IonContent, IonSpinner } from '@ionic/vue'
import { Camera, CameraResultType, CameraSource } from '@capacitor/camera'
import jsQR from 'jsqr'

const props = defineProps<{ isOpen: boolean }>()
const emit  = defineEmits<{ close: []; scanned: [value: string] }>()

type State = 'idle' | 'processing' | 'notfound' | 'success' | 'error'
const state          = ref<State>('idle')
const errorMsg       = ref('')
const capturedDataUrl = ref('')

async function capture() {
  state.value = 'processing'
  errorMsg.value = ''
  capturedDataUrl.value = ''

  try {
    // Check / request permission
    const perm = await Camera.checkPermissions()
    if (perm.camera === 'denied') {
      errorMsg.value = 'Camera permission denied. Go to Settings → App → Camera to enable it.'
      state.value = 'error'
      return
    }
    if (perm.camera !== 'granted') {
      const req = await Camera.requestPermissions({ permissions: ['camera'] })
      if (req.camera !== 'granted') {
        errorMsg.value = 'Camera permission was not granted.'
        state.value = 'error'
        return
      }
    }

    // Open native camera
    const photo = await Camera.getPhoto({
      quality: 90,
      resultType: CameraResultType.DataUrl,
      source: CameraSource.Camera,
      allowEditing: false,
      correctOrientation: true,
    })

    if (!photo.dataUrl) {
      state.value = 'notfound'
      return
    }

    capturedDataUrl.value = photo.dataUrl
    const code = await decodeQR(photo.dataUrl)

    if (code) {
      state.value = 'success'
      setTimeout(() => {
        emit('scanned', code)
        emit('close')
      }, 800)
    } else {
      state.value = 'notfound'
    }
  } catch (e: any) {
    // User cancelled camera — go back to idle silently
    if (e?.message?.includes('cancelled') || e?.message?.includes('cancel') || e?.message?.includes('No image')) {
      state.value = 'idle'
      return
    }
    errorMsg.value = 'Could not access camera. Please try again.'
    state.value = 'error'
  }
}

function decodeQR(dataUrl: string): Promise<string | null> {
  return new Promise((resolve) => {
    const img = new Image()
    img.onload = () => {
      const canvas = document.createElement('canvas')
      canvas.width  = img.width
      canvas.height = img.height
      const ctx = canvas.getContext('2d')!
      ctx.drawImage(img, 0, 0)
      const imageData = ctx.getImageData(0, 0, canvas.width, canvas.height)
      const code = jsQR(imageData.data, imageData.width, imageData.height, {
        inversionAttempts: 'attemptBoth',
      })
      resolve(code?.data ?? null)
    }
    img.onerror = () => resolve(null)
    img.src = dataUrl
  })
}
</script>

<style scoped>
.scanner-content { --background: #0D1F17; }

.scanner-wrap {
  min-height: 100vh;
  display: flex; flex-direction: column;
  align-items: center; justify-content: center;
  padding: 40px 32px 60px;
  gap: 16px; text-align: center;
  position: relative;
}

.close-btn {
  position: absolute; top: 20px; right: 20px;
  width: 36px; height: 36px; border-radius: 50%;
  background: rgba(255,255,255,0.1); border: none;
  color: #fff; font-size: 16px; cursor: pointer;
}

.icon-ring {
  width: 88px; height: 88px; border-radius: 50%;
  background: rgba(0, 201, 122, 0.15);
  border: 2px solid rgba(0, 201, 122, 0.4);
  display: flex; align-items: center; justify-content: center;
  margin-bottom: 8px;
}
.icon-ring--warn    { background: rgba(245,158,11,0.15); border-color: rgba(245,158,11,0.4); }
.icon-ring--success { background: rgba(0,201,122,0.25);  border-color: #00C97A; }
.icon-ring--spin    { border-color: rgba(255,255,255,0.2); }
.qr-icon { font-size: 36px; }

.main-title { font-size: 22px; font-weight: 700; color: #fff; }
.main-sub   { font-size: 14px; color: rgba(255,255,255,0.55); line-height: 1.5; max-width: 280px; }

.scan-btn {
  margin-top: 8px; padding: 16px 40px;
  background: #00C97A; border: none; border-radius: 50px;
  font-size: 16px; font-weight: 700; color: #fff;
  cursor: pointer; transition: opacity 0.15s;
}
.scan-btn:active { opacity: 0.8; }

.preview-img {
  width: 220px; height: 220px; object-fit: cover;
  border-radius: 14px; opacity: 0.6;
}
</style>
