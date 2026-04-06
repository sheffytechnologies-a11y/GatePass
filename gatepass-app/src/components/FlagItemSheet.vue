<template>
  <ion-modal
    :is-open="isOpen"
    @didDismiss="onDismiss"
    :initial-breakpoint="0.88"
    :breakpoints="[0, 0.88]"
  >
    <ion-page class="sheet-wrap">
      <!-- Header -->
      <div class="sheet-header">
        <div class="sheet-handle" />
        <div class="header-row">
          <div>
            <div class="sheet-title">Declare Exit Item</div>
            <div class="sheet-sub">Security will be notified immediately</div>
          </div>
          <button class="close-btn" @click="$emit('close')">✕</button>
        </div>
      </div>

    <ion-content class="sheet-content">
      <div class="sheet-body">
        <!-- Photo zone -->
        <div class="section-label">PHOTO OF ITEM</div>
        <div class="camera-zone" @click="openPhotoSheet">
          <template v-if="currentPhoto">
            <img :src="currentPhoto" class="photo-preview" alt="Item photo" />
            <button class="retake-btn" @click.stop="currentPhoto = ''; currentBlob = null">Retake</button>
          </template>
          <template v-else>
            <div class="cam-icon-wrap">
              <span class="cam-emoji">📷</span>
            </div>
            <div class="cam-label">Tap to take photo</div>
            <div class="cam-sub">Capture or upload from gallery</div>
          </template>
        </div>

        <!-- Permission error -->
        <div v-if="permissionDenied" class="perm-error">
          Camera access blocked. Go to <strong>Settings → Gatepass → Camera</strong> to enable it.
        </div>

        <!-- Description -->
        <div class="section-label">ITEM DESCRIPTION <span class="req">*</span></div>
        <textarea
          v-model="description"
          placeholder="e.g. Black HP laptop, serial GH-2233 — belongs to resident"
          class="desc-textarea"
          rows="4"
        />

        <!-- Submit -->
        <div class="submit-area">
          <button
            class="alert-btn"
            :disabled="!canSend || submitting"
            @click="sendAlert"
          >
            <ion-spinner v-if="submitting" name="crescent" color="light" style="width:20px;height:20px" />
            <template v-else>🔔 Send Alert to Security</template>
          </button>
          <div class="submit-caption">Gate guard will be notified instantly</div>
        </div>
      </div>
    </ion-content>
    </ion-page>
  </ion-modal>
</template>

<script setup lang="ts">
import { ref, computed } from 'vue'
import { IonModal, IonPage, IonContent, IonSpinner, actionSheetController } from '@ionic/vue'
import { useCamera } from '@/composables/useToast'
import { useToast } from '@/composables/useToast'
import client from '@/api/client'

const props = defineProps<{ isOpen: boolean; passId: string }>()
const emit  = defineEmits<{ close: []; declared: [count: number] }>()

const { capturePhoto, pickFromGallery } = useCamera()
const { showToast } = useToast()

const currentPhoto  = ref('')
const currentBlob   = ref<Blob | null>(null)
const description   = ref('')
const permissionDenied = ref(false)
const submitting    = ref(false)

const canSend = computed(() => description.value.trim().length > 0)

async function openPhotoSheet() {
  const sheet = await actionSheetController.create({
    header: 'Add Photo',
    buttons: [
      { text: 'Take Photo', handler: takePhoto },
      { text: 'Choose from Gallery', handler: pickGallery },
      { text: 'Cancel', role: 'cancel' },
    ],
  })
  await sheet.present()
}

async function takePhoto() {
  permissionDenied.value = false
  const result = await capturePhoto()
  if (!result) { permissionDenied.value = true; return }
  currentPhoto.value = result.dataUrl
  currentBlob.value  = base64ToBlob(result.base64, 'image/jpeg')
}

async function pickGallery() {
  const result = await pickFromGallery()
  if (!result) return
  currentPhoto.value = result.dataUrl
  currentBlob.value  = base64ToBlob(result.base64, 'image/jpeg')
}

function base64ToBlob(base64: string, mime: string): Blob {
  const byteChars = atob(base64)
  const bytes = new Uint8Array(byteChars.length)
  for (let i = 0; i < byteChars.length; i++) bytes[i] = byteChars.charCodeAt(i)
  return new Blob([bytes], { type: mime })
}

async function sendAlert() {
  if (!canSend.value || submitting.value) return
  submitting.value = true
  try {
    const formData = new FormData()
    formData.append('name', description.value.trim())
    formData.append('description', description.value.trim())
    if (currentBlob.value) {
      formData.append('photo', currentBlob.value, 'item.jpg')
    }
    await client.post(`/v1/passes/${props.passId}/items`, formData, {
      headers: { 'Content-Type': 'multipart/form-data' },
    })
    emit('declared', 1)
    reset()
  } catch {
    showToast('Failed to send alert. Try again.', 'error')
  } finally {
    submitting.value = false
  }
}

function reset() {
  currentPhoto.value = ''
  currentBlob.value  = null
  description.value  = ''
  permissionDenied.value = false
}

function onDismiss() {
  reset()
  emit('close')
}
</script>

<style scoped>
/* Modal wrapper fills the sheet */
.sheet-wrap {
  display: flex; flex-direction: column; height: 100%;
  background: var(--w-bg, #fff);
}
.sheet-content { --background: var(--w-bg, #fff); }

/* Header */
.sheet-header {
  padding: 10px 20px 0;
  border-bottom: 1px solid var(--w-border, #eee);
  background: var(--w-surface, #fff);
}
.sheet-handle {
  width: 36px; height: 4px; border-radius: 2px;
  background: var(--w-border, #ddd); margin: 0 auto 14px;
}
.header-row {
  display: flex; justify-content: space-between; align-items: flex-start;
  padding-bottom: 16px;
}
.sheet-title { font-size: 20px; font-weight: 700; color: var(--w-text, #111); }
.sheet-sub   { font-size: 13px; color: var(--w-muted, #888); margin-top: 3px; }
.close-btn {
  width: 32px; height: 32px; border-radius: 50%;
  background: var(--w-border, #eee); border: none;
  font-size: 14px; color: var(--w-muted, #666); cursor: pointer;
  display: flex; align-items: center; justify-content: center;
}

/* Scrollable body */
.sheet-body {
  flex: 1; overflow-y: auto; padding: 20px 20px 40px;
  display: flex; flex-direction: column; gap: 10px;
}

.section-label {
  font-size: 11px; font-weight: 700; letter-spacing: 0.8px;
  color: var(--w-muted, #888); text-transform: uppercase;
}
.req { color: #e55; }

/* Camera zone */
.camera-zone {
  background: var(--w-surface, #f5f5f5);
  border: 2px dashed #ccc;
  border-radius: 16px;
  min-height: 140px;
  display: flex; flex-direction: column;
  align-items: center; justify-content: center; gap: 8px;
  cursor: pointer; position: relative; overflow: hidden;
}
.cam-icon-wrap {
  width: 52px; height: 52px; border-radius: 50%;
  background: #1A4731;
  display: flex; align-items: center; justify-content: center;
}
.cam-emoji   { font-size: 24px; }
.cam-label   { font-size: 15px; font-weight: 700; color: var(--w-text, #111); }
.cam-sub     { font-size: 13px; color: var(--w-muted, #888); }
.photo-preview { width: 100%; object-fit: cover; max-height: 240px; border-radius: 14px; }
.retake-btn {
  position: absolute; bottom: 10px; right: 10px;
  background: rgba(0,0,0,0.55); color: #fff; border: none;
  padding: 5px 14px; border-radius: 20px; font-size: 12px;
  font-weight: 600; cursor: pointer;
}

.perm-error {
  background: #fff0f0; border-radius: 10px;
  padding: 10px 14px; font-size: 13px; color: #c0392b;
}

/* Textarea */
.desc-textarea {
  width: 100%; border: none; border-radius: 14px;
  padding: 16px; font-size: 15px;
  background: var(--w-surface, #f5f5f5);
  color: var(--w-text, #111); font-family: var(--w-font-body, sans-serif);
  outline: none; resize: none; box-sizing: border-box;
  min-height: 100px;
}
.desc-textarea::placeholder { color: #bbb; }

/* Submit */
.submit-area { display: flex; flex-direction: column; align-items: center; gap: 8px; margin-top: 4px; }
.alert-btn {
  width: 100%; padding: 17px;
  background: #F5A98A; border: none; border-radius: 50px;
  font-size: 16px; font-weight: 700; color: #fff;
  cursor: pointer; transition: opacity 0.15s;
  display: flex; align-items: center; justify-content: center; gap: 8px;
}
.alert-btn:disabled { opacity: 0.55; cursor: not-allowed; }
.alert-btn:active:not(:disabled) { opacity: 0.85; }
.submit-caption { font-size: 12px; color: var(--w-muted, #888); }
</style>
