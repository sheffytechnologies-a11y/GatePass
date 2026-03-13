<template>
  <ion-page>
    <ion-header class="ion-no-border">
      <ion-toolbar>
        <ion-title>Emergency</ion-title>
      </ion-toolbar>
    </ion-header>

    <ion-content :fullscreen="true" class="emergency-content">
      <div class="content-pad">

        <!-- SOS button -->
        <div class="sos-wrap">
          <div class="sos-ripple" v-if="!alertSent" />
          <button class="sos-btn" :class="{ sent: alertSent }" @click="confirmSOS" :disabled="sending">
            <ion-spinner v-if="sending" name="crescent" color="light" />
            <span v-else-if="alertSent">✓ Sent</span>
            <span v-else>SOS</span>
          </button>
        </div>
        <p class="sos-caption">{{ selectedType ? `Send ${selectedType} Alert` : 'Tap to send emergency alert' }}</p>

        <!-- Emergency types -->
        <div class="types-grid">
          <button
            v-for="t in TYPES" :key="t.value"
            class="type-card"
            :class="{ selected: selectedType === t.value }"
            @click="selectedType = t.value"
          >
            <span class="type-icon">{{ t.icon }}</span>
            <span class="type-label">{{ t.label }}</span>
          </button>
        </div>

        <!-- Emergency contacts -->
        <div class="contacts-section">
          <div class="section-label">Estate Emergency Contacts</div>
          <div class="contact-list">
            <a v-for="c in CONTACTS" :key="c.name" :href="`tel:${c.phone}`" class="contact-row">
              <div class="contact-info">
                <div class="contact-name">{{ c.name }}</div>
                <div class="contact-phone">{{ c.phone }}</div>
              </div>
              <ion-icon :icon="callOutline" class="call-icon" />
            </a>
          </div>
        </div>

        <!-- Recent incidents -->
        <div class="incidents-section" v-if="recentIncidents.length > 0">
          <div class="section-label">Recent Alerts You Sent</div>
          <div class="incident-list">
            <div v-for="inc in recentIncidents" :key="inc.id" class="incident-row">
              <span class="incident-type">{{ inc.type }}</span>
              <span class="incident-time">{{ fmtRelative(inc.createdAt) }}</span>
            </div>
          </div>
        </div>

      </div>
    </ion-content>
  </ion-page>
</template>

<script setup lang="ts">
import { ref } from 'vue'
import { IonPage, IonHeader, IonToolbar, IonTitle, IonContent, IonIcon, IonSpinner, alertController } from '@ionic/vue'
import { callOutline } from 'ionicons/icons'
import { useToast } from '@/composables/useToast'
import { delay } from '@/api/mock'

const { showToast } = useToast()

const selectedType = ref('')
const sending      = ref(false)
const alertSent    = ref(false)

const TYPES = [
  { value: 'Security Incident', label: 'Security Incident', icon: '🚨' },
  { value: 'Fire',              label: 'Fire',              icon: '🔥' },
  { value: 'Medical',           label: 'Medical',           icon: '🏥' },
  { value: 'Intruder',          label: 'Intruder',          icon: '⚠️' },
]

const CONTACTS = [
  { name: 'PHDL Security Control', phone: '08100000000' },
  { name: 'Estate Manager',        phone: '08100000001' },
  { name: 'Fire Service',          phone: '080123456789' },
]

const recentIncidents = ref<{ id: string; type: string; createdAt: string }[]>([])

async function confirmSOS() {
  const alert = await alertController.create({
    header: 'Send security alert?',
    message: 'This will notify PHDL Security immediately. Only use this for real emergencies.',
    buttons: [
      { text: 'Cancel', role: 'cancel' },
      {
        text: 'Send Alert Now',
        role: 'destructive',
        handler: sendSOS,
      },
    ],
  })
  await alert.present()
}

async function sendSOS() {
  sending.value = true
  try {
    // ── Replace with real API call ──────────────────────────
    // await client.post('/emergency', { type: selectedType.value || 'General', unit: auth.resident?.flatAddress })
    await delay(800)
    alertSent.value = true
    showToast('Alert sent — PHDL Security notified', 'warning')
    recentIncidents.value.unshift({ id: Date.now().toString(), type: selectedType.value || 'General', createdAt: new Date().toISOString() })
    setTimeout(() => { alertSent.value = false }, 3000)
  } catch {
    showToast('Alert failed. Call security directly: 08100000000', 'error')
  } finally {
    sending.value = false
  }
}

function fmtRelative(iso: string) {
  const diff = (Date.now() - new Date(iso).getTime()) / 1000
  if (diff < 60) return 'Just now'
  if (diff < 3600) return `${Math.floor(diff/60)}m ago`
  return `${Math.floor(diff/3600)}h ago`
}
</script>

<style scoped>
.emergency-content { --background: var(--w-bg); }
.content-pad { padding: 20px 16px 100px; display: flex; flex-direction: column; gap: 24px; align-items: center; }
.sos-wrap   { position: relative; width: 140px; height: 140px; display: flex; align-items: center; justify-content: center; }
.sos-ripple {
  position: absolute; width: 140px; height: 140px; border-radius: 50%;
  border: 3px solid var(--w-danger); animation: ripple-ring 2s ease-out infinite; opacity: 0.5;
}
.sos-btn {
  width: 110px; height: 110px; border-radius: 50%;
  background: var(--w-danger); color: white; border: none;
  font-size: 28px; font-weight: 800; cursor: pointer;
  font-family: var(--w-font-display); box-shadow: 0 8px 24px rgba(220,38,38,0.45);
  transition: all 0.2s; z-index: 1;
}
.sos-btn:active { transform: scale(0.95); }
.sos-btn.sent   { background: var(--w-primary); }
.sos-caption { font-size: 15px; color: var(--w-muted); font-weight: 600; margin-top: -12px; }

.types-grid { display: grid; grid-template-columns: 1fr 1fr; gap: 10px; width: 100%; }
.type-card {
  display: flex; flex-direction: column; align-items: center; gap: 8px;
  padding: 16px; border-radius: var(--w-radius-md); border: 1.5px solid var(--w-border);
  background: var(--w-surface); cursor: pointer; font-family: var(--w-font-body); transition: all 0.15s;
}
.type-card.selected { border-color: var(--w-danger); background: var(--w-danger-light); }
.type-icon  { font-size: 28px; }
.type-label { font-size: 13px; font-weight: 600; color: var(--w-text); text-align: center; }

.contacts-section, .incidents-section { width: 100%; }
.section-label { font-size: 13px; font-weight: 700; color: var(--w-muted); text-transform: uppercase; letter-spacing: 0.5px; margin-bottom: 10px; }
.contact-list { background: var(--w-surface); border-radius: var(--w-radius-md); overflow: hidden; }
.contact-row  { display: flex; align-items: center; justify-content: space-between; padding: 14px 16px; border-bottom: 1px solid var(--w-border); text-decoration: none; }
.contact-row:last-child { border-bottom: none; }
.contact-name  { font-size: 14px; font-weight: 600; color: var(--w-text); }
.contact-phone { font-size: 13px; color: var(--w-muted); margin-top: 2px; }
.call-icon     { font-size: 22px; color: var(--w-primary); }
.incident-list { background: var(--w-surface); border-radius: var(--w-radius-md); overflow: hidden; }
.incident-row  { display: flex; justify-content: space-between; align-items: center; padding: 12px 16px; border-bottom: 1px solid var(--w-border); font-size: 14px; }
.incident-row:last-child { border-bottom: none; }
.incident-type { font-weight: 600; color: var(--w-danger); }
.incident-time { color: var(--w-muted); font-size: 13px; }
</style>
