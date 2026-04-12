<template>
  <ion-page>
    <ion-header class="ion-no-border">
      <ion-toolbar>
        <ion-title>{{ isDone ? 'Pass Created' : 'Create Pass' }}</ion-title>
        <ion-buttons v-if="isDone" slot="end">
          <ion-button @click="resetForm" color="primary">New Pass</ion-button>
        </ion-buttons>
      </ion-toolbar>
    </ion-header>

    <ion-content :fullscreen="true" class="create-content">
      <div class="content-pad">

        <!-- ══ DONE STATE ═══════════════════════════════════════ -->
        <transition name="fade-up">
          <div v-if="isDone && createdPass" class="done-state animate-fade-in-up">
            <div class="check-circle animate-check-pop">✓</div>
            <div class="done-heading">Pass created!</div>

            <QRDisplay :value="createdPass.qrData" :pass-id="createdPass.id" />

            <div class="pass-summary">
              <div class="summary-row"><span>Visitor</span><strong>{{ createdPass.visitorName }}</strong></div>
              <div class="summary-row"><span>Purpose</span><strong>{{ createdPass.purpose }}</strong></div>
              <div class="summary-row"><span>Expires</span><strong>{{ fmtExpiry(createdPass.expiresAt) }}</strong></div>
              <div class="summary-row"><span>Your address</span><strong>{{ createdPass.hostUnit }}</strong></div>
            </div>

            <div class="share-section">
              <div class="share-heading">Share this pass with your visitor</div>
              <div class="share-grid">
                <button class="share-btn whatsapp" @click="shareWhatsApp">
                  <span class="share-icon">💬</span>
                  <span>WhatsApp</span>
                </button>
                <button class="share-btn sms" @click="shareSMS">
                  <span class="share-icon">📱</span>
                  <span>SMS</span>
                </button>
                <button class="share-btn copy" @click="copyLink">
                  <span class="share-icon">🔗</span>
                  <span>Copy Link</span>
                </button>
                <button class="share-btn email" @click="shareEmail">
                  <span class="share-icon">✉️</span>
                  <span>Email</span>
                </button>
              </div>
            </div>

            <ion-button expand="block" fill="outline" color="primary" @click="router.push(`/pass/${createdPass.id}`)">
              View Pass Details
            </ion-button>
            <ion-button expand="block" color="primary" @click="resetForm">
              Create Another Pass
            </ion-button>
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
              <button class="chip" :class="{ active: form.type === 'One-time' }" @click="form.type = 'One-time'">One-time</button>
              <button class="chip" :class="{ active: form.type === 'Recurring' }" @click="form.type = 'Recurring'">Recurring</button>
            </div>
          </div>

          <!-- Recurring days -->
          <div v-if="form.type === 'Recurring'" class="field">
            <label>Recurring Days <span class="req">*</span></label>
            <div class="chip-group">
              <button
                v-for="d in DAYS" :key="d"
                class="chip day-chip" :class="{ active: form.recurringDays.includes(d) }"
                @click="toggleDay(d)"
              >{{ d }}</button>
            </div>
          </div>

          <!-- Visit Date (one-time only) -->
          <div v-if="form.type === 'One-time'" class="field">
            <label>Visit Date <span class="req">*</span></label>
            <input v-model="form.visitDate" type="date" :min="todayStr" class="text-input" />
          </div>

          <!-- Expiry Time -->
          <div class="field">
            <label>Pass Expires At <span class="req">*</span></label>
            <input v-model="form.expiryTime" type="time" class="text-input" />
          </div>

          <!-- Visitor Phone -->
          <div class="field">
            <label>Visitor Phone <span class="optional">(optional — for WhatsApp share)</span></label>
            <div class="input-wrap">
              <span class="prefix">+234</span>
              <input v-model="form.visitorPhone" type="tel" inputmode="numeric" placeholder="080 0000 0000" class="bare-input" />
            </div>
          </div>

          <!-- Additional Details (collapsible) -->
          <button class="collapsible-toggle" @click="showAdditional = !showAdditional">
            Additional Details
            <ion-icon :icon="showAdditional ? chevronUpOutline : chevronDownOutline" />
          </button>
          <div v-if="showAdditional" class="field">
            <label>Vehicle Plate <span class="optional">(optional)</span></label>
            <input v-model="form.vehiclePlate" type="text" placeholder="e.g. LAG-123-AA" class="text-input" style="text-transform:uppercase" />
          </div>

          <!-- Validation errors -->
          <div v-if="submitAttempted && !isValid" class="validation-error">
            Please fill in all required fields.
          </div>

          <!-- Submit -->
          <ion-button
            expand="block" color="primary"
            :disabled="!isValid || submitting"
            @click="handleSubmit"
            class="submit-btn"
          >
            <ion-spinner v-if="submitting" name="crescent" />
            <span v-else>Create Pass</span>
          </ion-button>

        </div>
      </div>
    </ion-content>
  </ion-page>
</template>

<script setup lang="ts">
import { ref, computed } from 'vue'
import { useRouter } from 'vue-router'
import { IonPage, IonHeader, IonToolbar, IonTitle, IonButtons, IonButton, IonContent, IonSpinner, IonIcon } from '@ionic/vue'
import { chevronDownOutline, chevronUpOutline } from 'ionicons/icons'
import type { Pass } from '@/types'
import { useToast } from '@/composables/useToast'
import QRDisplay from '@/components/QRDisplay.vue'
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
})

const isDone         = ref(false)
const submitting     = ref(false)
const submitAttempted= ref(false)
const showAdditional = ref(false)
const createdPass    = ref<Pass | null>(null)

const isValid = computed(() => {
  if (!form.value.visitorName.trim()) return false
  if (!form.value.purpose) return false
  if (!form.value.type) return false
  if (!form.value.expiryTime) return false
  if (form.value.type === 'Recurring' && form.value.recurringDays.length === 0) return false
  return true
})

function toggleDay(d: string) {
  const idx = form.value.recurringDays.indexOf(d)
  if (idx >= 0) form.value.recurringDays.splice(idx, 1)
  else form.value.recurringDays.push(d)
}

async function handleSubmit() {
  submitAttempted.value = true
  if (!isValid.value) return

  submitting.value = true
  try {
    const res = await client.post('/v1/passes', {
      visitorName:   form.value.visitorName,
      visitorPhone:  form.value.visitorPhone || null,
      purpose:       form.value.purpose,
      type:          form.value.type,
      expiresAt:     buildExpiresAt(),
      recurringDays: form.value.type === 'Recurring' ? form.value.recurringDays : null,
      vehiclePlate:  form.value.vehiclePlate || null,
    })
    createdPass.value = res.data.pass
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

function resetForm() {
  isDone.value = false
  createdPass.value = null
  submitAttempted.value = false
  form.value = { visitorName: '', purpose: '', type: 'One-time', visitDate: todayStr, expiryTime: '18:00', visitorPhone: '', vehiclePlate: '', recurringDays: [] }
}

function fmtExpiry(iso: string) {
  return new Date(iso).toLocaleString('en-NG', { dateStyle: 'medium', timeStyle: 'short', hour12: true })
}

function buildShareMsg() {
  const p = createdPass.value!
  const link = `https://gatepass-mob-app.web.app/pass/${p.id}`
  return `Hi ${p.visitorName}, here is your visitor pass for PHDL Estate. Show this QR code to the security guard at the gate on arrival.\n\nPass ID: ${p.id}\nValid until: ${fmtExpiry(p.expiresAt)}\nHost: ${p.hostName} — ${p.hostUnit}\n\nPass link: ${link}`
}

function shareWhatsApp() {
  const url = `https://wa.me/?text=${encodeURIComponent(buildShareMsg())}`
  window.open(url, '_blank')
  showToast('Opened in WhatsApp', 'success')
}

function shareSMS() {
  const p = createdPass.value!
  const msg = `WARDN PASS: ${p.visitorName}, show QR at PHDL Estate gate. Valid till ${new Date(p.expiresAt).toLocaleTimeString('en-NG', { hour: '2-digit', minute: '2-digit', hour12: true })}. Link: wardn.ng/p/${p.id} — ${p.hostName}`
  window.open(`sms:?body=${encodeURIComponent(msg)}`)
}

function copyLink() {
  const link = `https://gatepass-mob-app.web.app/pass/${createdPass.value!.id}`
  navigator.clipboard.writeText(link).then(() => showToast('Pass link copied', 'success'))
}

function shareEmail() {
  const subject = encodeURIComponent('Your visitor pass for PHDL Estate')
  const body    = encodeURIComponent(buildShareMsg())
  window.open(`mailto:?subject=${subject}&body=${body}`)
}
</script>

<style scoped>
.create-content { --background: var(--w-bg); }
.content-pad    { padding: 16px 16px 100px; }
.form-state     { display: flex; flex-direction: column; gap: 20px; }
.field label    { display: block; font-size: 13px; font-weight: 600; color: var(--w-text); margin-bottom: 8px; }
.req   { color: var(--w-danger); }
.optional { font-weight: 400; color: var(--w-muted); }
.text-input {
  width: 100%; border: 1.5px solid var(--w-border); border-radius: var(--w-radius-md);
  padding: 13px 14px; font-size: 15px; background: var(--w-surface);
  color: var(--w-text); font-family: var(--w-font-body); outline: none;
}
.text-input:focus { border-color: var(--w-primary); }
.chip-group { display: flex; flex-wrap: wrap; gap: 8px; }
.chip {
  padding: 8px 14px; border-radius: 20px; border: 1.5px solid var(--w-border);
  background: var(--w-surface); font-size: 14px; color: var(--w-text);
  cursor: pointer; transition: all 0.15s; font-family: var(--w-font-body);
}
.chip.active { background: var(--w-primary); border-color: var(--w-primary); color: white; font-weight: 600; }
.day-chip { padding: 8px 12px; }
.input-wrap { display: flex; align-items: center; background: var(--w-surface); border: 1.5px solid var(--w-border); border-radius: var(--w-radius-md); overflow: hidden; }
.prefix { padding: 0 10px 0 14px; font-size: 15px; color: var(--w-muted); font-weight: 600; border-right: 1px solid var(--w-border); }
.bare-input { flex: 1; border: none; outline: none; padding: 13px 14px; font-size: 15px; background: transparent; font-family: var(--w-font-body); }
.collapsible-toggle { background: none; border: none; display: flex; align-items: center; gap: 6px; font-size: 14px; font-weight: 600; color: var(--w-primary); cursor: pointer; padding: 0; }
.validation-error { font-size: 13px; color: var(--w-danger); background: var(--w-danger-light); padding: 10px 14px; border-radius: var(--w-radius-sm); }
.submit-btn { --border-radius: 14px; height: 52px; font-size: 16px; font-weight: 700; margin-top: 8px; }

/* Done state */
.done-state { display: flex; flex-direction: column; gap: 20px; align-items: center; }
.check-circle { width: 72px; height: 72px; border-radius: 50%; background: var(--w-primary); color: white; font-size: 32px; display: flex; align-items: center; justify-content: center; font-weight: 700; }
.done-heading { font-family: var(--w-font-display); font-size: 24px; font-weight: 700; color: var(--w-text); }
.pass-summary { width: 100%; background: var(--w-surface); border-radius: var(--w-radius-md); overflow: hidden; }
.summary-row  { display: flex; justify-content: space-between; align-items: center; padding: 12px 16px; border-bottom: 1px solid var(--w-border); font-size: 14px; }
.summary-row:last-child { border-bottom: none; }
.summary-row span { color: var(--w-muted); }
.share-section { width: 100%; }
.share-heading { font-size: 14px; font-weight: 600; color: var(--w-text); margin-bottom: 12px; text-align: center; }
.share-grid { display: grid; grid-template-columns: 1fr 1fr; gap: 10px; }
.share-btn {
  display: flex; flex-direction: column; align-items: center; gap: 6px;
  padding: 14px 10px; border-radius: var(--w-radius-md);
  border: 1.5px solid var(--w-border); background: var(--w-surface);
  font-size: 13px; font-weight: 600; cursor: pointer; font-family: var(--w-font-body);
  transition: all 0.15s;
}
.share-btn:active { transform: scale(0.97); }
.share-btn.whatsapp { border-color: #25D366; color: #25D366; }
.share-icon { font-size: 22px; }
</style>
