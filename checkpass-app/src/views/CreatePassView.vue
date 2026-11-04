<template>
  <ion-page>
    <!-- Top toolbar only shown during pass creation form. Hidden on success page. -->
    <ion-header v-if="!isDone" class="ion-no-border">
      <ion-toolbar>
        <ion-buttons slot="start">
          <ion-back-button default-href="/tabs/home" text="" />
        </ion-buttons>
        <ion-title>Create Pass</ion-title>
      </ion-toolbar>
    </ion-header>

    <ion-content :fullscreen="true" class="create-content">
      <div class="content-pad">

        <!-- ══ PASS CREATED SUCCESS STATE ════════════════════════ -->
        <transition name="fade-up">
          <div v-if="isDone && createdPass" class="pass-success-view animate-fade-in-up">
            <div class="success-icon-wrap">
              <div class="check-circle animate-check-pop">✓</div>
            </div>

            <div class="success-heading">Pass created!</div>
            <p class="success-subheading">
              Your visitor pass for <strong>{{ createdPass.visitorName }}</strong> is ready.
            </p>

            <!-- Action CTAs: Primary "View Pass", Secondary "Back to Home" -->
            <div class="success-cta-stack">
              <button class="cta-primary-btn" @click="goToPassDetails">
                View Pass
              </button>
              <button class="cta-secondary-btn" @click="goToHome">
                Back to Home
              </button>
            </div>
          </div>
        </transition>

        <!-- ══ FORM STATE ════════════════════════════════════════ -->
        <div v-if="!isDone" class="form-state">

          <!-- Visitor Name -->
          <div class="field">
            <label>Visitor Name <span class="req">*</span></label>
            <input v-model="form.visitorName" type="text" placeholder="e.g. Chidi Okeke" maxlength="60" class="text-input" />
          </div>

          <!-- Visit Purpose -->
          <div class="field">
            <label>Visit Purpose <span class="req">*</span></label>
            <div class="chip-group">
              <button
                v-for="p in PURPOSES" :key="p"
                class="chip" :class="{ active: form.purpose === p }"
                @click="form.purpose = p"
              >{{ p }}</button>
            </div>
          </div>

          <!-- Pass Type -->
          <div class="field">
            <label>Pass Type <span class="req">*</span></label>
            <div class="chip-group">
              <button
                class="chip" :class="{ active: form.type === 'One-time' }"
                @click="form.type = 'One-time'"
              >One-time</button>
              <button
                class="chip" :class="{ active: form.type === 'Recurring' }"
                @click="form.type = 'Recurring'"
              >Recurring</button>
            </div>
          </div>

          <!-- One-time: Date -->
          <div v-if="form.type === 'One-time'" class="field">
            <label>Visit Date <span class="req">*</span></label>
            <input v-model="form.visitDate" type="date" :min="todayStr" class="text-input" />
          </div>

          <!-- Recurring: Days of week -->
          <div v-if="form.type === 'Recurring'" class="field">
            <label>Days of the Week <span class="req">*</span></label>
            <div class="day-chips">
              <button
                v-for="d in DAYS" :key="d"
                class="day-chip" :class="{ active: form.recurringDays.includes(d) }"
                @click="toggleDay(d)"
              >{{ d }}</button>
            </div>
          </div>

          <!-- Expiry time -->
          <div class="field">
            <label>Pass Expires At <span class="req">*</span></label>
            <input v-model="form.expiryTime" type="time" class="text-input" />
          </div>

          <!-- Visitor Phone -->
          <div class="field">
            <label>Visitor Phone</label>
            <div class="input-wrap">
              <span class="prefix">+234</span>
              <input v-model="form.visitorPhone" type="tel" inputmode="numeric" placeholder="080 0000 0000" class="bare-input" />
            </div>
          </div>

          <!-- Visitor Photo (Optional) -->
          <div class="field">
            <label>Visitor Photo <span class="photo-opt-label">(Optional)</span></label>
            <div class="photo-upload-wrap">
              <div v-if="form.photoUrl" class="photo-preview-box">
                <img :src="form.photoUrl" alt="Visitor photo" class="photo-preview-img" />
                <button type="button" class="photo-remove-btn" @click="form.photoUrl = ''">&times;</button>
              </div>
              <label v-else class="photo-upload-trigger">
                <input type="file" accept="image/*" class="sr-only" @change="handlePhotoUpload" />
                <div class="photo-upload-box">
                  <svg width="22" height="22" viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="2" stroke-linecap="round" stroke-linejoin="round">
                    <path d="M23 19a2 2 0 0 1-2 2H3a2 2 0 0 1-2-2V8a2 2 0 0 1 2-2h4l2-3h6l2 3h4a2 2 0 0 1 2 2z"/>
                    <circle cx="12" cy="13" r="4"/>
                  </svg>
                  <span>Upload or take visitor photo</span>
                </div>
              </label>
            </div>
          </div>

          <!-- Additional Details (collapsible) -->
          <div class="collapsible-section">
            <button class="collapsible-toggle" @click="showAdditional = !showAdditional">
              <span>Additional Details</span>
              <ion-icon :icon="showAdditional ? chevronUpOutline : chevronDownOutline" />
            </button>

            <div v-if="showAdditional" class="collapsible-body">
              <!-- Vehicle Plate -->
              <div class="field">
                <label>Vehicle Plate Number</label>
                <input
                  v-model="form.vehiclePlate"
                  type="text"
                  placeholder="e.g. ABC 123 XY"
                  maxlength="15"
                  class="text-input uppercase"
                />
              </div>
            </div>
          </div>

          <!-- Validation Error Alert -->
          <div v-if="validationError" class="validation-error">
            {{ validationError }}
          </div>

          <!-- Submit -->
          <ion-button
            expand="block"
            class="submit-btn"
            :disabled="submitting"
            @click="handleSubmit"
          >
            <ion-spinner v-if="submitting" name="crescent" />
            <span v-else>CREATE PASS</span>
          </ion-button>

        </div>
      </div>
    </ion-content>
  </ion-page>
</template>

<script setup lang="ts">
import { ref, computed } from 'vue'
import { useRouter } from 'vue-router'
import { IonPage, IonHeader, IonToolbar, IonTitle, IonBackButton, IonButtons, IonButton, IonContent, IonSpinner, IonIcon } from '@ionic/vue'
import { chevronDownOutline, chevronUpOutline } from 'ionicons/icons'
import type { Pass } from '@/types'
import { useToast } from '@/composables/useToast'
import client from '@/api/client'

const router = useRouter()
const { showToast } = useToast()

const PURPOSES = ['Personal Visit', 'Delivery', 'Service', 'Business', 'Other']
const DAYS     = ['Mon', 'Tue', 'Wed', 'Thu', 'Fri', 'Sat', 'Sun']

const todayStr = new Date().toISOString().split('T')[0]

const form = ref({
  visitorName: '', purpose: '', type: 'One-time',
  visitDate: todayStr, expiryTime: '18:00',
  visitorPhone: '', vehiclePlate: '',
  recurringDays: [] as string[],
  photoUrl: '',
})

const submitting       = ref(false)
const isDone           = ref(false)
const createdPass      = ref<Pass | null>(null)
const showAdditional   = ref(false)
const submitAttempted  = ref(false)

const validationError = computed(() => {
  if (!submitAttempted.value) return null
  if (!form.value.visitorName.trim()) return 'Visitor name is required.'
  if (!form.value.purpose)            return 'Please select a visit purpose.'
  if (form.value.type === 'Recurring' && form.value.recurringDays.length === 0) {
    return 'Please select at least one day for recurring pass.'
  }
  return null
})

function handlePhotoUpload(e: Event) {
  const target = e.target as HTMLInputElement
  const file = target.files?.[0]
  if (!file) return
  if (file.size > 5 * 1024 * 1024) {
    showToast('Photo must be under 5MB', 'warning')
    return
  }
  const reader = new FileReader()
  reader.onload = (evt) => {
    form.value.photoUrl = evt.target?.result as string
  }
  reader.readAsDataURL(file)
}

function toggleDay(d: string) {
  const arr = form.value.recurringDays
  const i   = arr.indexOf(d)
  if (i >= 0) arr.splice(i, 1)
  else arr.push(d)
}

async function handleSubmit() {
  submitAttempted.value = true
  if (validationError.value) return

  submitting.value = true
  try {
    const rawVisitorPhone = form.value.visitorPhone ? form.value.visitorPhone.replace(/\D/g, '') : ''
    const cleanVisitorDigits = rawVisitorPhone.startsWith('234')
      ? rawVisitorPhone.slice(3)
      : rawVisitorPhone.startsWith('0')
        ? rawVisitorPhone.slice(1)
        : rawVisitorPhone
    const formattedVisitorPhone = cleanVisitorDigits ? `+234${cleanVisitorDigits}` : null

    const res = await client.post('/v1/passes', {
      visitorName:   form.value.visitorName.trim(),
      visitorPhone:  formattedVisitorPhone,
      purpose:       form.value.purpose,
      type:          form.value.type,
      expiresAt:     buildExpiresAt(),
      recurringDays: form.value.type === 'Recurring' ? form.value.recurringDays : null,
      vehiclePlate:  form.value.vehiclePlate ? form.value.vehiclePlate.trim().toUpperCase() : null,
    })
    createdPass.value = res.data.pass
    if (form.value.photoUrl && createdPass.value) {
      (createdPass.value as any).photoUrl = form.value.photoUrl
      try {
        localStorage.setItem(`checkpass_photo_${createdPass.value.id}`, form.value.photoUrl)
      } catch {}
    }
    isDone.value = true
    showToast(`Pass created for ${form.value.visitorName}`, 'success')
  } catch {
    showToast("Couldn't create pass. Try again.", 'error')
  } finally {
    submitting.value = false
  }
}

function buildExpiresAt(): string {
  const date = form.value.type === 'One-time' ? form.value.visitDate : todayStr
  return new Date(`${date}T${form.value.expiryTime}:00`).toISOString()
}

function goToPassDetails() {
  if (createdPass.value) {
    const passId = createdPass.value.id
    isDone.value = false
    router.push(`/pass/${passId}`)
  }
}

function goToHome() {
  isDone.value = false
  router.push('/tabs/home')
}

</script>

<style scoped>
.create-content { --background: var(--w-bg); }
.content-pad { padding: 16px; max-width: 480px; margin: 0 auto; }

/* Form state */
.form-state { display: flex; flex-direction: column; gap: 16px; }
.field { display: flex; flex-direction: column; gap: 6px; }
.field label { font-size: 13px; font-weight: 600; color: var(--w-text); }
.req { color: var(--w-danger); }
.text-input { background: var(--w-surface); border: 1.5px solid var(--w-border); border-radius: var(--w-radius-md); padding: 12px 14px; font-size: 15px; color: var(--w-text); outline: none; font-family: var(--w-font-body); }
.text-input:focus { border-color: var(--w-primary); }
.text-input.uppercase { text-transform: uppercase; }

.chip-group { display: flex; flex-wrap: wrap; gap: 8px; }
.chip { padding: 8px 14px; border-radius: var(--w-radius-full); font-size: 13px; font-weight: 500; border: 1.5px solid var(--w-border); background: var(--w-surface); color: var(--w-text); cursor: pointer; transition: all 0.15s; font-family: var(--w-font-body); }
.chip.active { background: var(--w-primary); color: white; border-color: var(--w-primary); }

.day-chips { display: flex; gap: 6px; flex-wrap: wrap; }
.day-chip { width: 40px; height: 40px; border-radius: 50%; border: 1.5px solid var(--w-border); background: var(--w-surface); color: var(--w-text); font-size: 12px; font-weight: 600; cursor: pointer; transition: all 0.15s; display: flex; align-items: center; justify-content: center; font-family: var(--w-font-body); }
.day-chip.active { background: var(--w-primary); color: white; border-color: var(--w-primary); }

.input-wrap { display: flex; align-items: center; background: var(--w-surface); border: 1.5px solid var(--w-border); border-radius: var(--w-radius-md); overflow: hidden; }
.prefix { padding: 0 10px 0 14px; font-size: 15px; color: var(--w-muted); font-weight: 600; border-right: 1px solid var(--w-border); }
.bare-input { flex: 1; border: none; outline: none; padding: 13px 14px; font-size: 15px; background: transparent; font-family: var(--w-font-body); }
.collapsible-toggle { background: none; border: none; display: flex; align-items: center; gap: 6px; font-size: 14px; font-weight: 600; color: var(--w-primary); cursor: pointer; padding: 0; }
.validation-error { font-size: 13px; color: var(--w-danger); background: var(--w-danger-light); padding: 10px 14px; border-radius: var(--w-radius-sm); }
.submit-btn { --border-radius: 14px; height: 52px; font-size: 16px; font-weight: 700; margin-top: 8px; }

/* ── PASS CREATED SUCCESS VIEW ── */
.pass-success-view {
  display: flex;
  flex-direction: column;
  align-items: center;
  justify-content: center;
  text-align: center;
  min-height: calc(100vh - 120px);
  padding: 40px 16px 32px;
  max-width: 440px;
  margin: 0 auto;
}
.success-icon-wrap {
  margin-bottom: 20px;
}
.check-circle {
  width: 76px;
  height: 76px;
  border-radius: 50%;
  background: var(--w-primary, #0A5C38);
  color: #FFFFFF;
  font-size: 36px;
  font-weight: 800;
  display: flex;
  align-items: center;
  justify-content: center;
  box-shadow: 0 8px 24px rgba(10, 92, 56, 0.28);
}
.success-heading {
  font-size: 26px;
  font-weight: 800;
  color: #111827;
  letter-spacing: -0.02em;
  margin-bottom: 8px;
}
.success-subheading {
  font-size: 14.5px;
  color: #6B7280;
  line-height: 1.5;
  margin-bottom: 28px;
  max-width: 320px;
}
.success-subheading strong {
  color: #111827;
}
.pass-summary-card {
  width: 100%;
  background: #FFFFFF;
  border: 1.5px solid #E5E7EB;
  border-radius: 18px;
  padding: 8px 0;
  margin-bottom: 32px;
  box-shadow: 0 4px 16px rgba(0, 0, 0, 0.04);
}
.summary-item {
  display: flex;
  justify-content: space-between;
  align-items: center;
  padding: 13px 20px;
  border-bottom: 1px solid #F3F4F6;
  font-size: 14px;
}
.summary-item:last-child {
  border-bottom: none;
}
.summary-lbl {
  color: #6B7280;
  font-weight: 500;
}
.summary-val {
  color: #111827;
  font-weight: 700;
}
.capitalize {
  text-transform: capitalize;
}
.success-cta-stack {
  width: 100%;
  display: flex;
  flex-direction: column;
  gap: 12px;
}
.cta-primary-btn {
  width: 100%;
  height: 52px;
  background: var(--w-primary, #0A5C38);
  color: #FFFFFF;
  border: none;
  border-radius: 14px;
  font-size: 15.5px;
  font-weight: 700;
  cursor: pointer;
  box-shadow: 0 4px 14px rgba(10, 92, 56, 0.25);
  transition: all 0.15s ease;
}
.cta-primary-btn:active {
  transform: scale(0.98);
}
.cta-secondary-btn {
  width: 100%;
  height: 50px;
  background: #FFFFFF;
  color: #374151;
  border: 1.5px solid #D1D5DB;
  border-radius: 14px;
  font-size: 15px;
  font-weight: 600;
  cursor: pointer;
  transition: all 0.15s ease;
}
.cta-secondary-btn:active {
  background: #F9FAFB;
  transform: scale(0.98);
}

.photo-opt-label { font-size: 11px; font-weight: 400; color: var(--w-muted); margin-left: 4px; }
.photo-upload-wrap { margin-top: 4px; }
.photo-upload-trigger { display: block; cursor: pointer; }
.sr-only { position: absolute; width: 1px; height: 1px; padding: 0; margin: -1px; overflow: hidden; clip: rect(0,0,0,0); border: 0; }
.photo-upload-box {
  display: flex;
  align-items: center;
  justify-content: center;
  gap: 10px;
  padding: 14px;
  background: var(--w-surface);
  border: 1.5px dashed var(--w-border);
  border-radius: var(--w-radius-md);
  color: var(--w-text);
  font-size: 13px;
  font-weight: 500;
  transition: all 0.15s ease;
}
.photo-upload-box:active { background: rgba(10, 92, 56, 0.05); }
.photo-upload-box svg { color: var(--w-primary, #0A5C38); }
.photo-preview-box {
  position: relative;
  display: inline-block;
  width: 72px;
  height: 72px;
  border-radius: var(--w-radius-md);
  overflow: hidden;
  border: 2px solid var(--w-primary, #0A5C38);
}
.photo-preview-img { width: 100%; height: 100%; object-fit: cover; }
.photo-remove-btn {
  position: absolute;
  top: 2px;
  right: 2px;
  width: 20px;
  height: 20px;
  border-radius: 50%;
  background: rgba(0, 0, 0, 0.65);
  color: #ffffff;
  border: none;
  font-size: 14px;
  line-height: 1;
  display: flex;
  align-items: center;
  justify-content: center;
  cursor: pointer;
}

</style>
