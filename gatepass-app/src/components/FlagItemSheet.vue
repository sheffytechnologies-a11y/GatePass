<template>
  <ion-modal :is-open="isOpen" @didDismiss="$emit('close')" :initial-breakpoint="0.92" :breakpoints="[0, 0.92]">
    <ion-page>
      <ion-header class="ion-no-border">
        <ion-toolbar>
          <ion-title>Declare Exit Item</ion-title>
          <ion-buttons slot="end">
            <ion-button @click="$emit('close')">
              <ion-icon :icon="closeOutline" />
            </ion-button>
          </ion-buttons>
        </ion-toolbar>
      </ion-header>

      <ion-content class="sheet-content">
        <div class="sheet-pad">

          <!-- Camera area -->
          <div class="camera-zone" @click="takePicture">
            <template v-if="currentPhoto">
              <img :src="currentPhoto" class="photo-preview" alt="Item photo" />
              <button class="retake-btn" @click.stop="currentPhoto = ''; currentBase64 = ''">Retake</button>
            </template>
            <template v-else>
              <div class="camera-placeholder">
                <ion-icon :icon="cameraOutline" class="cam-icon" />
                <span>Tap to photograph the item</span>
              </div>
            </template>
          </div>

          <!-- Permission error -->
          <div v-if="permissionDenied" class="perm-error">
            <p v-if="permPermanent">
              Camera access has been blocked. Go to <strong>Settings &gt; Wardn &gt; Camera</strong> to enable it.
            </p>
            <p v-else>Camera access is needed to photograph items. Tap the camera area to try again.</p>
          </div>

          <!-- Gallery fallback -->
          <button class="gallery-link" @click="pickGallery">Or choose from gallery</button>

          <!-- Description -->
          <div class="field">
            <label>Description <span class="req">*</span></label>
            <textarea
              v-model="description"
              placeholder="Describe the item (e.g. MacBook Pro, grey bag)"
              class="desc-textarea"
              rows="3"
            />
          </div>

          <!-- Add to list -->
          <ion-button
            expand="block"
            fill="outline"
            color="primary"
            :disabled="!canAdd"
            @click="addItem"
          >
            + Add to list
          </ion-button>

          <!-- Items list -->
          <div v-if="items.length > 0" class="items-list">
            <div v-for="(item, idx) in items" :key="idx" class="item-row">
              <div class="item-thumb">
                <img v-if="item.dataUrl" :src="item.dataUrl" alt="item" />
                <span v-else>📦</span>
              </div>
              <div class="item-desc">{{ item.description }}</div>
              <button class="remove-btn" @click="items.splice(idx, 1)">✕</button>
            </div>
          </div>

          <!-- Send alert -->
          <ion-button
            expand="block"
            color="warning"
            :disabled="items.length === 0 || submitting"
            @click="sendAlert"
            class="alert-btn"
          >
            <ion-spinner v-if="submitting" name="crescent" />
            <span v-else>Send Alert to Security</span>
          </ion-button>

        </div>
      </ion-content>
    </ion-page>
  </ion-modal>
</template>

<script setup lang="ts">
import { ref, computed } from 'vue'
import { IonModal, IonPage, IonHeader, IonToolbar, IonTitle, IonButtons, IonButton, IonContent, IonIcon, IonSpinner } from '@ionic/vue'
import { cameraOutline, closeOutline } from 'ionicons/icons'
import { useCamera } from '@/composables/useToast'
import { delay } from '@/api/mock'

const props = defineProps<{ isOpen: boolean; passId: string }>()
const emit = defineEmits<{ close: []; declared: [count: number] }>()

const { capturePhoto, pickFromGallery, getPermissionStatus } = useCamera()

const currentPhoto   = ref('')
const currentBase64  = ref('')
const description    = ref('')
const permissionDenied = ref(false)
const permPermanent  = ref(false)
const submitting     = ref(false)

interface DeclaredItem { dataUrl: string; base64: string; description: string }
const items = ref<DeclaredItem[]>([])

const canAdd = computed(() => description.value.trim().length > 0)

async function takePicture() {
  permissionDenied.value = false
  const status = await getPermissionStatus()
  if (status === 'denied') {
    permissionDenied.value = true
    permPermanent.value = true
    return
  }

  const result = await capturePhoto()
  if (!result) {
    permissionDenied.value = true
    permPermanent.value = false
    return
  }
  currentPhoto.value  = result.dataUrl
  currentBase64.value = result.base64
}

async function pickGallery() {
  const result = await pickFromGallery()
  if (!result) return
  currentPhoto.value  = result.dataUrl
  currentBase64.value = result.base64
}

function addItem() {
  if (!canAdd.value) return
  items.value.push({
    dataUrl: currentPhoto.value,
    base64: currentBase64.value,
    description: description.value.trim(),
  })
  currentPhoto.value  = ''
  currentBase64.value = ''
  description.value   = ''
}

async function sendAlert() {
  submitting.value = true
  try {
    // ── Replace with real API calls ──────────────────────────
    // for (const item of items.value) {
    //   await passesApi.flagItem(props.passId, item.base64, item.description)
    // }
    await delay(800)
    emit('declared', items.value.length)
    items.value = []
  } catch {
    // Parent shows error toast
  } finally {
    submitting.value = false
  }
}
</script>

<style scoped>
.sheet-content { --background: var(--w-bg); }
.sheet-pad { padding: 16px 16px 40px; display: flex; flex-direction: column; gap: 16px; }
.camera-zone {
  background: var(--w-surface); border: 2px dashed var(--w-border);
  border-radius: var(--w-radius-lg); min-height: 180px;
  display: flex; flex-direction: column; align-items: center; justify-content: center;
  cursor: pointer; overflow: hidden; position: relative;
}
.camera-placeholder { display: flex; flex-direction: column; align-items: center; gap: 10px; color: var(--w-muted); }
.cam-icon  { font-size: 40px; }
.photo-preview { width: 100%; object-fit: cover; max-height: 220px; }
.retake-btn {
  position: absolute; bottom: 10px; right: 10px;
  background: rgba(0,0,0,0.6); color: white; border: none;
  padding: 6px 12px; border-radius: 20px; font-size: 12px; cursor: pointer;
}
.perm-error { background: var(--w-danger-light); border-radius: var(--w-radius-md); padding: 12px 14px; font-size: 13px; color: var(--w-danger); }
.gallery-link { background: none; border: none; color: var(--w-primary); font-size: 13px; font-weight: 600; cursor: pointer; text-align: center; padding: 0; }
.field label  { display: block; font-size: 13px; font-weight: 600; margin-bottom: 8px; color: var(--w-text); }
.req { color: var(--w-danger); }
.desc-textarea {
  width: 100%; border: 1.5px solid var(--w-border); border-radius: var(--w-radius-md);
  padding: 12px 14px; font-size: 15px; background: var(--w-surface);
  color: var(--w-text); font-family: var(--w-font-body); outline: none; resize: none;
}
.desc-textarea:focus { border-color: var(--w-primary); }
.items-list { display: flex; flex-direction: column; gap: 8px; }
.item-row { display: flex; align-items: center; gap: 10px; background: var(--w-surface); border-radius: var(--w-radius-md); padding: 10px 12px; }
.item-thumb { width: 40px; height: 40px; border-radius: var(--w-radius-sm); background: var(--w-border); display: flex; align-items: center; justify-content: center; overflow: hidden; flex-shrink: 0; }
.item-thumb img { width: 100%; height: 100%; object-fit: cover; }
.item-desc  { flex: 1; font-size: 14px; color: var(--w-text); }
.remove-btn { background: none; border: none; color: var(--w-muted); font-size: 18px; cursor: pointer; padding: 4px; }
.alert-btn  { --border-radius: 14px; height: 52px; font-size: 16px; font-weight: 700; }
</style>
