<template>
  <ion-modal :is-open="isOpen" @didDismiss="$emit('close')" :breakpoints="[0, 1]" :initial-breakpoint="1">
    <ion-page>
      <ion-content class="scanner-content">
        <div class="scanner-wrap">

          <!-- Close -->
          <button class="close-btn" @click="$emit('close')">✕</button>

          <!-- Mode Switcher: Scan QR vs Phone / Code -->
          <div class="mode-tabs">
            <button
              type="button"
              class="mode-tab"
              :class="{ active: mode === 'qr' }"
              @click="switchMode('qr')"
            >
              Scan QR
            </button>
            <button
              type="button"
              class="mode-tab"
              :class="{ active: mode === 'manual' }"
              @click="switchMode('manual')"
            >
              Phone / Code
            </button>
          </div>

          <!-- ── MODE 1: CAMERA QR SCANNER ── -->
          <template v-if="mode === 'qr'">
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
              <div class="main-sub">Make sure the code is clear and well-lit, or switch to Phone / Code</div>
              <img v-if="capturedDataUrl" :src="capturedDataUrl" class="preview-img" />
              <div class="btn-row">
                <button class="scan-btn" @click="capture">Try Again</button>
                <button class="secondary-btn" @click="switchMode('manual')">Enter Code</button>
              </div>
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
              <div class="btn-row">
                <button class="scan-btn" @click="capture">Try Again</button>
                <button class="secondary-btn" @click="switchMode('manual')">Use Phone / Code</button>
              </div>
            </template>
          </template>

          <!-- ── MODE 2: PHONE NUMBER OR 6-DIGIT GATE CODE LOOKUP ── -->
          <template v-else>
            <!-- 2a. Multi-pass picker when duplicate phone number matches multiple active passes -->
            <template v-if="matchedPasses.length > 1">
              <div class="icon-ring">
                <span class="qr-icon">👥</span>
              </div>
              <div class="main-title">{{ matchedPasses.length }} Active Passes Found</div>
              <div class="main-sub">Ask visitor which host or unit they are here to visit</div>

              <div class="multi-pass-list">
                <div
                  v-for="p in matchedPasses"
                  :key="p.id"
                  class="multi-pass-card"
                  @click="selectPass(p)"
                >
                  <div class="mp-card-top">
                    <span class="mp-visitor">{{ p.visitorName }}</span>
                    <span class="mp-badge" :class="p.status.toLowerCase().replace(/\s+/g, '-')">{{ p.status }}</span>
                  </div>
                  <div class="mp-host">
                    <span class="mp-icon">🏠</span>
                    <span class="mp-host-text">{{ p.hostName ? p.hostName + ' · ' : '' }}{{ p.hostUnit || 'No unit specified' }}</span>
                  </div>
                  <div class="mp-meta">
                    <span v-if="p.passCode" class="mp-code">Code: <strong>{{ formatCode(p.passCode) }}</strong></span>
                    <span class="mp-purpose">{{ p.purpose }}</span>
                  </div>
                </div>
              </div>

              <button
                type="button"
                class="secondary-btn"
                style="margin-top: 10px; width: 100%; max-width: 320px;"
                @click="resetManualLookup"
              >
                Search Another Number
              </button>
            </template>

            <!-- 2b. Standard single input form -->
            <template v-else>
              <div class="icon-ring">
                <span class="qr-icon">🔢</span>
              </div>
              <div class="main-title">Verify by Phone or Code</div>
              <div class="main-sub">Enter the visitor's 6-digit Gate Code or phone number</div>

              <form class="manual-form" @submit.prevent="submitManualLookup">
                <input
                  v-model="manualQuery"
                  type="text"
                  inputmode="numeric"
                  class="manual-input"
                  placeholder="e.g. 482910 or 08023456789"
                  autocomplete="off"
                  autofocus
                />
                <div v-if="manualError" class="manual-error">{{ manualError }}</div>
                <button
                  type="submit"
                  class="scan-btn"
                  style="width:100%"
                  :disabled="manualLoading || !manualQuery.trim()"
                >
                  <ion-spinner v-if="manualLoading" name="crescent" style="width:18px;height:18px" />
                  <span v-else>Verify Pass</span>
                </button>
              </form>
            </template>
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
import { passesApi } from '@/api/passes'
import type { Pass } from '@/types'

defineProps<{ isOpen: boolean }>()
const emit  = defineEmits<{ close: []; scanned: [value: string] }>()

type State = 'idle' | 'processing' | 'notfound' | 'success' | 'error'
const mode            = ref<'qr' | 'manual'>('qr')
const state           = ref<State>('idle')
const errorMsg        = ref('')
const capturedDataUrl = ref('')

const manualQuery   = ref('')
const manualLoading = ref(false)
const manualError   = ref('')
const matchedPasses = ref<Pass[]>([])

function switchMode(next: 'qr' | 'manual') {
  mode.value = next
  manualError.value = ''
  matchedPasses.value = []
}

function resetManualLookup() {
  matchedPasses.value = []
  manualError.value = ''
}

function formatCode(code?: string | null): string {
  if (!code) return ''
  const clean = code.replace(/\s+/g, '')
  return clean.length === 6 ? `${clean.slice(0, 3)} ${clean.slice(3)}` : clean
}

function selectPass(p: Pass) {
  matchedPasses.value = []
  manualQuery.value = ''
  emit('scanned', p.id)
  emit('close')
}

async function submitManualLookup() {
  const q = manualQuery.value.trim()
  if (!q) return
  manualLoading.value = true
  manualError.value = ''
  matchedPasses.value = []

  try {
    const res = await passesApi.lookup(q)
    const matches: Pass[] = res.data?.passes?.length
      ? res.data.passes
      : (res.data?.pass ? [res.data.pass] : [])

    if (matches.length === 1) {
      manualQuery.value = ''
      matchedPasses.value = []
      emit('scanned', matches[0].id)
      emit('close')
    } else if (matches.length > 1) {
      matchedPasses.value = matches
    } else {
      manualError.value = 'No active pass found for this code or phone number.'
    }
  } catch (err: any) {
    manualError.value = err.response?.data?.message || 'No active pass found for this code or phone number.'
  } finally {
    manualLoading.value = false
  }
}

async function capture() {
  state.value = 'processing'
  errorMsg.value = ''
  capturedDataUrl.value = ''

  try {
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
  padding: 64px 28px 60px;
  gap: 16px; text-align: center;
  position: relative;
}

.close-btn {
  position: absolute; top: 20px; right: 20px;
  width: 36px; height: 36px; border-radius: 50%;
  background: rgba(255,255,255,0.1); border: none;
  color: #fff; font-size: 16px; cursor: pointer;
}

.mode-tabs {
  display: inline-flex;
  background: rgba(255,255,255,0.08);
  border: 1px solid rgba(255,255,255,0.14);
  border-radius: 9999px;
  padding: 4px;
  gap: 4px;
  margin-bottom: 10px;
}

.mode-tab {
  border: none;
  background: transparent;
  color: rgba(255,255,255,0.68);
  font-size: 13.5px;
  font-weight: 600;
  padding: 8px 18px;
  border-radius: 9999px;
  cursor: pointer;
  transition: all 0.18s ease;
}

.mode-tab.active {
  background: #00C97A;
  color: #052919;
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

.btn-row {
  display: flex;
  gap: 10px;
  flex-wrap: wrap;
  justify-content: center;
}

.scan-btn {
  margin-top: 8px; padding: 14px 32px;
  background: #00C97A; border: none; border-radius: 50px;
  font-size: 15px; font-weight: 700; color: #052919;
  cursor: pointer; transition: opacity 0.15s;
  display: inline-flex; align-items: center; justify-content: center;
}
.scan-btn:disabled { opacity: 0.5; cursor: not-allowed; }
.scan-btn:active { opacity: 0.85; }

.secondary-btn {
  margin-top: 8px; padding: 14px 24px;
  background: rgba(255,255,255,0.12);
  border: 1px solid rgba(255,255,255,0.2);
  border-radius: 50px;
  font-size: 15px; font-weight: 600; color: #fff;
  cursor: pointer;
}

.manual-form {
  width: 100%;
  max-width: 320px;
  display: flex;
  flex-direction: column;
  gap: 12px;
  margin-top: 6px;
}

.manual-input {
  width: 100%;
  height: 52px;
  border-radius: 14px;
  border: 1.5px solid rgba(255,255,255,0.22);
  background: rgba(255,255,255,0.08);
  color: #fff;
  font-size: 18px;
  font-weight: 700;
  letter-spacing: 0.06em;
  text-align: center;
  padding: 0 16px;
  outline: none;
}
.manual-input::placeholder {
  color: rgba(255,255,255,0.4);
  font-size: 13px;
  font-weight: 500;
  letter-spacing: normal;
}
.manual-input:focus {
  border-color: #00C97A;
  background: rgba(255,255,255,0.12);
}

.manual-error {
  background: rgba(220, 38, 38, 0.2);
  border: 1px solid rgba(248, 113, 113, 0.45);
  color: #FCA5A5;
  font-size: 13px;
  padding: 10px 12px;
  border-radius: 10px;
}

.preview-img {
  width: 180px; height: 180px; object-fit: cover;
  border-radius: 14px; border: 1.5px solid rgba(255,255,255,0.15);
}

.multi-pass-list {
  width: 100%;
  max-width: 340px;
  display: flex;
  flex-direction: column;
  gap: 10px;
  max-height: 48vh;
  overflow-y: auto;
  padding: 4px 2px;
  margin-top: 6px;
}

.multi-pass-card {
  background: rgba(255, 255, 255, 0.08);
  border: 1.5px solid rgba(255, 255, 255, 0.16);
  border-radius: 14px;
  padding: 12px 14px;
  text-align: left;
  cursor: pointer;
  transition: all 0.18s ease;
  display: flex;
  flex-direction: column;
  gap: 6px;
}
.multi-pass-card:hover,
.multi-pass-card:active {
  background: rgba(0, 201, 122, 0.14);
  border-color: #00C97A;
  transform: translateY(-1px);
}

.mp-card-top {
  display: flex;
  justify-content: space-between;
  align-items: center;
  gap: 8px;
}

.mp-visitor {
  color: #fff;
  font-size: 15px;
  font-weight: 700;
  white-space: nowrap;
  overflow: hidden;
  text-overflow: ellipsis;
}

.mp-badge {
  font-size: 11px;
  font-weight: 700;
  padding: 3px 8px;
  border-radius: 9999px;
  text-transform: uppercase;
  letter-spacing: 0.04em;
  flex-shrink: 0;
}
.mp-badge.pending {
  background: rgba(245, 158, 11, 0.2);
  color: #FCD34D;
  border: 1px solid rgba(245, 158, 11, 0.4);
}
.mp-badge.on-site {
  background: rgba(0, 201, 122, 0.2);
  color: #34D399;
  border: 1px solid rgba(0, 201, 122, 0.4);
}
.mp-badge.expired {
  background: rgba(239, 68, 68, 0.2);
  color: #FCA5A5;
  border: 1px solid rgba(239, 68, 68, 0.4);
}

.mp-host {
  display: flex;
  align-items: center;
  gap: 6px;
  font-size: 13px;
  color: rgba(255, 255, 255, 0.85);
}
.mp-icon {
  font-size: 14px;
}
.mp-host-text {
  font-weight: 500;
  white-space: nowrap;
  overflow: hidden;
  text-overflow: ellipsis;
}

.mp-meta {
  display: flex;
  justify-content: space-between;
  align-items: center;
  font-size: 12px;
  color: rgba(255, 255, 255, 0.55);
  margin-top: 2px;
  padding-top: 4px;
  border-top: 1px solid rgba(255, 255, 255, 0.08);
}

.mp-code {
  color: #00C97A;
  font-family: monospace;
  font-size: 13px;
}

.mp-purpose {
  font-style: italic;
  white-space: nowrap;
  overflow: hidden;
  text-overflow: ellipsis;
}
</style>
