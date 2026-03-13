<template>
  <ion-page>
    <ion-header class="ion-no-border">
      <ion-toolbar>
        <ion-buttons slot="start">
          <ion-back-button default-href="/tabs/passes" text="" />
        </ion-buttons>
        <ion-title>Pass Details</ion-title>
      </ion-toolbar>
    </ion-header>

    <ion-content :fullscreen="true" class="detail-content">
      <ion-refresher slot="fixed" @ionRefresh="onRefresh">
        <ion-refresher-content />
      </ion-refresher>

      <!-- Loading -->
      <div v-if="store.loading" class="content-pad">
        <ion-skeleton-text class="sk-card" animated />
        <ion-skeleton-text class="sk-qr" animated />
        <ion-skeleton-text class="sk-tiles" animated />
      </div>

      <!-- Pass not found -->
      <div v-else-if="store.error === 'PASS_NOT_FOUND'" class="content-pad center">
        <EmptyState icon="🔍" heading="Pass not found" body="This pass no longer exists." cta-label="Go to My Passes" @cta="router.push('/tabs/passes')" />
      </div>

      <!-- General error -->
      <div v-else-if="store.error" class="content-pad">
        <ErrorState message="Couldn't load pass details." @retry="load" />
      </div>

      <!-- Pass detail -->
      <div v-else-if="pass" class="content-pad">

        <!-- Visitor card -->
        <div class="visitor-card">
          <div class="visitor-avatar" :style="{ background: avatarGradient }">{{ initials }}</div>
          <div class="visitor-info">
            <div class="visitor-name">{{ pass.visitorName }}</div>
            <div class="visitor-unit">{{ pass.hostUnit }}</div>
          </div>
          <StatusBadge :status="displayStatus" />
        </div>

        <!-- QR (only for active/pending) -->
        <QRDisplay v-if="showQR" :value="pass.qrData" :pass-id="pass.id" />

        <!-- Pass info strip -->
        <div class="info-strip">
          <div class="info-row"><span>Pass ID</span><strong>{{ pass.id }}</strong></div>
          <div class="info-row"><span>Type</span><strong>{{ pass.type }}</strong></div>
          <div class="info-row"><span>Purpose</span><strong>{{ pass.purpose }}</strong></div>
          <div v-if="pass.vehiclePlate" class="info-row"><span>Vehicle</span><strong>{{ pass.vehiclePlate }}</strong></div>
          <div class="info-row">
            <span>{{ pass.type === 'Recurring' ? 'Days' : 'Expires' }}</span>
            <strong>{{ pass.type === 'Recurring' ? pass.recurringDays?.join(', ') : fmtDatetime(pass.expiresAt) }}</strong>
          </div>
        </div>

        <!-- Flagged items -->
        <div v-if="pass.flaggedItems.length > 0" class="flagged-section">
          <div class="section-label">Declared Items</div>
          <div v-for="item in pass.flaggedItems" :key="item.id" class="flagged-item">
            <div class="flagged-photo">
              <img v-if="item.photoUrl" :src="item.photoUrl" alt="Item photo" />
              <span v-else class="photo-placeholder">📦</span>
            </div>
            <div class="flagged-info">
              <div class="flagged-desc">{{ item.description }}</div>
              <div class="flagged-time">{{ fmtRelative(item.flaggedAt) }}</div>
            </div>
          </div>
        </div>

        <!-- Action tiles -->
        <div class="action-tiles">
          <button v-if="canDeclare" class="tile tile-warn" @click="openFlagSheet">
            <span class="tile-icon">📦</span>
            <span class="tile-label">Declare<br>Exit Item</span>
          </button>
          <button v-if="canRevoke" class="tile tile-danger" @click="confirmRevoke">
            <span class="tile-icon">🚫</span>
            <span class="tile-label">Revoke<br>Pass</span>
          </button>
          <button v-if="canExtend" class="tile" @click="openExtend">
            <span class="tile-icon">⏱️</span>
            <span class="tile-label">Extend<br>Pass</span>
          </button>
          <button v-if="canShare" class="tile" @click="sharePass">
            <span class="tile-icon">↗️</span>
            <span class="tile-label">Share<br>Pass</span>
          </button>
        </div>

        <!-- Contact visitor -->
        <div v-if="pass.visitorPhone" class="contact-row">
          <a :href="`tel:${pass.visitorPhone}`" class="contact-btn">
            <ion-icon :icon="callOutline" /> Call
          </a>
          <a :href="`https://wa.me/${pass.visitorPhone.replace('+','')}`" target="_blank" class="contact-btn whatsapp-btn">
            <ion-icon :icon="logoWhatsapp" /> WhatsApp
          </a>
        </div>

      </div>
    </ion-content>

    <!-- Flag Item Sheet -->
    <FlagItemSheet
      v-if="pass"
      :is-open="flagSheetOpen"
      :pass-id="pass.id"
      @close="flagSheetOpen = false"
      @declared="onItemsDeclared"
    />
  </ion-page>
</template>

<script setup lang="ts">
import { ref, computed, onMounted } from 'vue'
import { useRoute, useRouter } from 'vue-router'
import {
  IonPage, IonHeader, IonToolbar, IonTitle, IonButtons, IonBackButton,
  IonContent, IonRefresher, IonRefresherContent, IonSkeletonText, IonIcon,
  alertController, actionSheetController,
} from '@ionic/vue'
import { callOutline, logoWhatsapp } from 'ionicons/icons'
import { usePassesStore } from '@/stores/passes'
import { useToast } from '@/composables/useToast'
import StatusBadge from '@/components/StatusBadge.vue'
import QRDisplay from '@/components/QRDisplay.vue'
import EmptyState from '@/components/EmptyState.vue'
import ErrorState from '@/components/ErrorState.vue'
import FlagItemSheet from '@/components/FlagItemSheet.vue'

const route  = useRoute()
const router = useRouter()
const store  = usePassesStore()
const { showToast } = useToast()

const flagSheetOpen = ref(false)
const pass = computed(() => store.activePass)
const displayStatus = computed(() => pass.value ? store.displayStatus(pass.value) : 'Pending')

const showQR     = computed(() => pass.value && ['On-site','Pending'].includes(pass.value.status))
const canDeclare = computed(() => pass.value && ['On-site','Pending','Item Flagged'].includes(displayStatus.value) && pass.value.status !== 'Revoked' && pass.value.status !== 'Exited')
const canRevoke  = computed(() => pass.value && !['Exited','Revoked','Expired'].includes(pass.value.status))
const canExtend  = computed(() => pass.value && !['Exited','Revoked'].includes(pass.value.status))
const canShare   = computed(() => pass.value && pass.value.status !== 'Revoked')

const GRADIENTS = [
  'linear-gradient(135deg,#0A5C38,#00C97A)',
  'linear-gradient(135deg,#1A3F8F,#4A90D9)',
  'linear-gradient(135deg,#7C3AED,#C084FC)',
  'linear-gradient(135deg,#D97706,#FBBF24)',
]
const initials = computed(() => pass.value?.visitorName.split(' ').map(w => w[0]).slice(0,2).join('').toUpperCase() || '')
const avatarGradient = computed(() => {
  const idx = (pass.value?.visitorName.charCodeAt(0) || 0) % GRADIENTS.length
  return GRADIENTS[idx]
})

async function load() { await store.fetchById(route.params.id as string) }
async function onRefresh(e: CustomEvent) { await load(); (e.target as HTMLIonRefresherElement).complete() }

async function confirmRevoke() {
  const isOnsite = pass.value?.status === 'On-site'
  const alert = await alertController.create({
    header: 'Revoke this pass?',
    message: `${pass.value?.visitorName}'s pass will be cancelled immediately.${isOnsite ? ' This visitor is currently on-site.' : ''} They won't be able to enter the estate with this pass. This cannot be undone.`,
    buttons: [
      { text: 'Cancel', role: 'cancel' },
      {
        text: 'Yes, Revoke',
        role: 'destructive',
        cssClass: 'alert-danger',
        handler: async () => {
          try {
            await store.revokePass(pass.value!.id)
            showToast('Pass revoked', 'success')
          } catch {
            showToast('Something went wrong. Try again.', 'error')
          }
        },
      },
    ],
  })
  await alert.present()
}

async function openExtend() {
  const id = pass.value!.id
  const sheet = await actionSheetController.create({
    header: 'Extend Pass',
    buttons: [
      {
        text: '+1 Hour',
        handler: () => extendBy(60),
      },
      {
        text: '+3 Hours',
        handler: () => extendBy(180),
      },
      {
        text: 'Until end of day',
        handler: async () => {
          const eod = new Date()
          eod.setHours(23, 59, 0, 0)
          await doExtend(id, eod.toISOString())
        },
      },
      { text: 'Cancel', role: 'cancel' },
    ],
  })
  await sheet.present()
}

async function extendBy(minutes: number) {
  const current = new Date(pass.value!.expiresAt)
  const newTime = new Date(current.getTime() + minutes * 60 * 1000)
  await doExtend(pass.value!.id, newTime.toISOString())
}

async function doExtend(id: string, newExpiresAt: string) {
  try {
    await store.extendPass(id, newExpiresAt)
    showToast(`Pass extended to ${new Date(newExpiresAt).toLocaleTimeString('en-NG', { hour:'2-digit', minute:'2-digit', hour12:true })}`, 'success')
  } catch {
    showToast("Couldn't extend pass. Try again.", 'error')
  }
}

function openFlagSheet() { flagSheetOpen.value = true }

function onItemsDeclared(n: number) {
  flagSheetOpen.value = false
  showToast(`Security notified — ${n} item(s) declared`, 'warning')
  load()
}

function sharePass() {
  const p = pass.value!
  const link = `https://wardn.ng/pass/${p.id}`
  navigator.clipboard.writeText(link).then(() => showToast('Pass link copied', 'success'))
}

function fmtDatetime(iso: string) {
  return new Date(iso).toLocaleString('en-NG', { dateStyle: 'medium', timeStyle: 'short', hour12: true })
}
function fmtRelative(iso: string) {
  const diff = (Date.now() - new Date(iso).getTime()) / 1000
  if (diff < 3600) return `${Math.floor(diff / 60)}m ago`
  return `${Math.floor(diff / 3600)}h ago`
}

onMounted(load)
</script>

<style scoped>
.detail-content { --background: var(--w-bg); }
.content-pad { padding: 16px 16px 100px; display: flex; flex-direction: column; gap: 14px; }
.center { align-items: center; justify-content: center; min-height: 60vh; }
.sk-card  { height: 90px;  border-radius: var(--w-radius-lg); display: block; }
.sk-qr    { height: 260px; border-radius: var(--w-radius-lg); display: block; }
.sk-tiles { height: 80px;  border-radius: var(--w-radius-lg); display: block; }

.visitor-card { background: var(--w-surface); border-radius: var(--w-radius-lg); padding: 18px; display: flex; align-items: center; gap: 14px; }
.visitor-avatar { width: 52px; height: 52px; border-radius: 50%; display: flex; align-items: center; justify-content: center; color: white; font-size: 18px; font-weight: 700; flex-shrink: 0; }
.visitor-info   { flex: 1; }
.visitor-name   { font-size: 18px; font-weight: 700; color: var(--w-text); }
.visitor-unit   { font-size: 13px; color: var(--w-muted); margin-top: 3px; }

.info-strip { background: var(--w-surface); border-radius: var(--w-radius-md); overflow: hidden; }
.info-row { display: flex; justify-content: space-between; padding: 13px 16px; border-bottom: 1px solid var(--w-border); font-size: 14px; }
.info-row:last-child { border-bottom: none; }
.info-row span { color: var(--w-muted); }

.flagged-section { background: var(--w-warning-light); border-radius: var(--w-radius-md); padding: 14px 16px; }
.section-label { font-size: 13px; font-weight: 700; color: var(--w-warning); margin-bottom: 10px; }
.flagged-item { display: flex; gap: 12px; align-items: flex-start; }
.flagged-photo { width: 48px; height: 48px; border-radius: var(--w-radius-sm); background: var(--w-border); overflow: hidden; display: flex; align-items: center; justify-content: center; flex-shrink: 0; }
.flagged-photo img { width: 100%; height: 100%; object-fit: cover; }
.photo-placeholder { font-size: 22px; }
.flagged-desc { font-size: 14px; color: var(--w-text); font-weight: 500; }
.flagged-time { font-size: 12px; color: var(--w-muted); margin-top: 3px; }

.action-tiles { display: grid; grid-template-columns: repeat(4, 1fr); gap: 8px; }
.tile {
  display: flex; flex-direction: column; align-items: center; justify-content: center; gap: 6px;
  padding: 14px 8px; border-radius: var(--w-radius-md); border: 1.5px solid var(--w-border);
  background: var(--w-surface); cursor: pointer; font-family: var(--w-font-body); transition: all 0.15s;
}
.tile:active  { transform: scale(0.96); }
.tile-warn    { border-color: var(--w-warning); background: var(--w-warning-light); }
.tile-danger  { border-color: var(--w-danger); background: var(--w-danger-light); }
.tile-icon    { font-size: 22px; }
.tile-label   { font-size: 11px; font-weight: 600; text-align: center; line-height: 1.3; color: var(--w-text); }

.contact-row { display: flex; gap: 10px; }
.contact-btn {
  flex: 1; display: flex; align-items: center; justify-content: center; gap: 8px;
  padding: 13px; border-radius: var(--w-radius-md); border: 1.5px solid var(--w-border);
  background: var(--w-surface); font-size: 14px; font-weight: 600; color: var(--w-text);
  text-decoration: none; transition: all 0.15s;
}
.whatsapp-btn { border-color: #25D366; color: #25D366; }
</style>
