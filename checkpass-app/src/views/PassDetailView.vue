<template>
  <ion-page>
    <ion-header class="ion-no-border pass-detail-header">
      <div class="pass-detail-nav-bar">
        <div class="nav-left">
          <button class="nav-back-btn" @click="router.back()" aria-label="Go back">
            <svg width="22" height="22" viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="2.2" stroke-linecap="round" stroke-linejoin="round">
              <line x1="19" y1="12" x2="5" y2="12"></line>
              <polyline points="12 19 5 12 12 5"></polyline>
            </svg>
          </button>
          <h1 class="nav-title">Pass Details</h1>
        </div>
        <div class="nav-right">
          <button v-if="canShare" class="nav-share-btn" title="Share Pass" @click="sharePass" aria-label="Share Pass">
            <svg width="22" height="22" viewBox="0 0 256 256" fill="currentColor">
              <path d="M176,160a39.88,39.88,0,0,0-28.53,12L98.67,143.51a40,40,0,0,0,0-31L147.47,84A40,40,0,1,0,136,56a39.63,39.63,0,0,0,2.16,13L89.36,97.51a40,40,0,1,0,0,61L138.16,187A39.63,39.63,0,0,0,136,200a40,40,0,1,0,40-40Zm0-128a24,24,0,1,1-24,24A24,24,0,0,1,176,32ZM64,152a24,24,0,1,1,24-24A24,24,0,0,1,64,152Zm112,72a24,24,0,1,1,24-24A24,24,0,0,1,176,224Z"/>
            </svg>
          </button>
        </div>
      </div>
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

        <!-- Branded Seamless Pass Invite Card (Option 01) -->
        <PassInviteCard
          ref="inviteCardRef"
          :pass="pass"
          :show-q-r="Boolean(showQR)"
          :estate-name="auth.resident?.estateName"
        />

        <!-- Actions (Moved above Declared Items) -->
        <div class="actions-section">
          <!-- Security Gate Guard Actions (if applicable) -->
          <div v-if="canAllowEntry || canMarkExited" class="guard-actions-row">
            <button v-if="canAllowEntry" class="guard-btn guard-btn-allow" @click="handleAllowEntry">
              <span>✅ Allow Entry</span>
            </button>
            <button v-if="canMarkExited" class="guard-btn guard-btn-exit" @click="handleMarkExited">
              <span>🚪 Mark Exited</span>
            </button>
          </div>

          <!-- Share on its own row -->
          <div v-if="canShare" class="action-row-share">
            <button class="btn-action btn-share-full" @click="sharePass">
              <svg width="19" height="19" viewBox="0 0 256 256" fill="currentColor">
                <path d="M176,160a39.88,39.88,0,0,0-28.53,12L98.67,143.51a40,40,0,0,0,0-31L147.47,84A40,40,0,1,0,136,56a39.63,39.63,0,0,0,2.16,13L89.36,97.51a40,40,0,1,0,0,61L138.16,187A39.63,39.63,0,0,0,136,200a40,40,0,1,0,40-40Zm0-128a24,24,0,1,1-24,24A24,24,0,0,1,176,32ZM64,152a24,24,0,1,1,24-24A24,24,0,0,1,64,152Zm112,72a24,24,0,1,1,24-24A24,24,0,0,1,176,224Z"/>
              </svg>
              <span>Share</span>
            </button>
          </div>

          <!-- Call & Revoke side-by-side below Share -->
          <div class="action-row-secondary">
            <!-- Call Visitor -->
            <button
              class="btn-action btn-call-half"
              :class="{ 'btn-call-disabled': !pass.visitorPhone }"
              @click="handleCallVisitor"
            >
              <svg width="18" height="18" viewBox="0 0 256 256" fill="currentColor">
                <path d="M222.37,158.46l-47.11-21.11a16,16,0,0,0-15.71,2.53L136.08,160a124.63,124.63,0,0,1-56.12-56.12l20.12-23.47a16,16,0,0,0,2.53-15.71L81.5,17.59A16,16,0,0,0,66.86,8,56.26,56.26,0,0,0,10.6,63.49,192.29,192.29,0,0,0,192.51,245.4a56.26,56.26,0,0,0,55.49-56.26A16,16,0,0,0,222.37,158.46ZM192.51,229.4A176.26,176.26,0,0,1,26.6,63.49,40.23,40.23,0,0,1,66.86,24l47.11,21.11L94.46,68a12,12,0,0,0-2.31,13.27,140.72,140.72,0,0,0,82.58,82.58A12,12,0,0,0,188,161.54l22.89-19.52L232,163.13A40.23,40.23,0,0,1,192.51,229.4Z"/>
              </svg>
              <span>Call Visitor</span>
            </button>

            <!-- Revoke: white background, red text & red icon -->
            <button v-if="canRevoke" class="btn-action btn-revoke-half" @click="confirmRevoke">
              <svg width="18" height="18" viewBox="0 0 256 256" fill="currentColor">
                <path d="M128,24A104,104,0,1,0,232,128,104.11,104.11,0,0,0,128,24Zm0,192a87.64,87.64,0,0,1-54.67-19.19l117.86-117.86A88,88,0,0,1,128,216Zm62.67-34.14L72.81,64.2A88,88,0,0,1,190.67,181.86Z"/>
              </svg>
              <span>Revoke</span>
            </button>
          </div>
        </div>

        <!-- Declared Exit Items (Moved below Actions) -->
        <div class="flagged-section">
          <div class="declared-head">
            <div class="declared-title">Declared Exit Items ({{ pass.flaggedItems.length }})</div>
            <button v-if="canDeclare" class="declared-add" @click="openFlagSheet">+ Add item</button>
          </div>

          <div v-if="pass.flaggedItems.length === 0" class="declared-empty">No declared items yet.</div>

          <div v-for="item in pass.flaggedItems" :key="item.id" class="flagged-item">
            <div class="flagged-main">
              <div class="flagged-photo" :class="{ 'flagged-photo--img': !!item.photoUrl }">
                <img v-if="item.photoUrl" :src="item.photoUrl" alt="Item photo" />
                <span v-else class="photo-placeholder">📦</span>
              </div>
              <div class="flagged-info">
                <div class="flagged-desc">{{ item.description }}</div>
                <div class="flagged-meta">{{ item.photoUrl ? '📷 Photo attached' : 'No photo attached' }}</div>
              </div>
              <div class="flagged-status">Flagged</div>
            </div>
            <div class="flagged-time">{{ fmtRelative(item.flaggedAt) }}</div>
          </div>
        </div>

      </div>
    </ion-content>

    <!-- Flag Item Sheet -->
    <FlagItemSheet
      v-if="pass && flagSheetOpen"
      :is-open="true"
      :pass-id="pass.id"
      @close="flagSheetOpen = false"
      @declared="onItemsDeclared"
    />

    <!-- Exit Clearance Modal (GAP-03) -->
    <ExitClearanceModal
      v-if="pass && exitClearanceOpen"
      :is-open="true"
      :pass="pass"
      @close="exitClearanceOpen = false"
      @exited="onExitCompleted"
    />
  </ion-page>
</template>

<script setup lang="ts">
import { ref, computed, onMounted } from 'vue'
import { useRoute, useRouter } from 'vue-router'
import {
  IonPage, IonHeader,
  IonContent, IonRefresher, IonRefresherContent, IonSkeletonText,
  alertController,
} from '@ionic/vue'
import { usePassesStore } from '@/stores/passes'
import { useAuthStore } from '@/stores/auth'
import { passesApi } from '@/api/passes'
import { useToast } from '@/composables/useToast'
import PassInviteCard from '@/components/PassInviteCard.vue'
import { generateInviteCardImage } from '@/utils/inviteCardRenderer'
import EmptyState from '@/components/EmptyState.vue'
import ErrorState from '@/components/ErrorState.vue'
import FlagItemSheet from '@/components/FlagItemSheet.vue'
import ExitClearanceModal from '@/components/ExitClearanceModal.vue'

const route  = useRoute()
const router = useRouter()
const store  = usePassesStore()
const auth   = useAuthStore()
const { showToast } = useToast()

const flagSheetOpen = ref(false)
const exitClearanceOpen = ref(false)
const inviteCardRef = ref<InstanceType<typeof PassInviteCard> | null>(null)
const pass = computed(() => store.activePass)
const displayStatus = computed(() => pass.value ? store.displayStatus(pass.value) : 'Pending')

const showQR        = computed(() => pass.value && ['On-site','Pending'].includes(pass.value.status))
const canDeclare    = computed(() => pass.value && ['On-site','Pending','Item Flagged'].includes(displayStatus.value) && pass.value.status !== 'Revoked' && pass.value.status !== 'Exited')
const canRevoke     = computed(() => pass.value && !['Exited','Revoked','Expired'].includes(pass.value.status))
const canShare      = computed(() => Boolean(pass.value && showQR.value))
const canAllowEntry = computed(() => Boolean(auth.isSecurityUser && pass.value && pass.value.status === 'Pending'))
const canMarkExited = computed(() => Boolean(auth.isSecurityUser && pass.value && pass.value.status === 'On-site'))

async function handleAllowEntry() {
  if (!pass.value) return
  try {
    await passesApi.allowEntry(pass.value.id)
    showToast('Visitor entry recorded — resident notified', 'success')
    await load()
  } catch (err: any) {
    showToast(err.response?.data?.message || 'Failed to record entry.', 'error')
  }
}

function handleMarkExited() {
  if (!pass.value) return
  exitClearanceOpen.value = true
}

async function onExitCompleted() {
  exitClearanceOpen.value = false
  await load()
}

async function load() { await store.fetchById(route.params.id as string) }
async function onRefresh(e: CustomEvent) { await load(); (e.target as HTMLIonRefresherElement).complete() }

function handleCallVisitor() {
  if (pass.value?.visitorPhone) {
    window.location.href = `tel:${pass.value.visitorPhone}`
  } else {
    showToast('No phone number provided for this visitor.', 'warning')
  }
}

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

function openFlagSheet() { flagSheetOpen.value = true }

function onItemsDeclared(n: number) {
  flagSheetOpen.value = false
  showToast(`Security notified — ${n} item(s) declared`, 'warning')
  load()
}

async function sharePass() {
  const activePass = pass.value
  if (!activePass) return

  try {
    showToast('Generating invite card...', 'warning')
    let cardDataUrl = await inviteCardRef.value?.generateImage()
    if (!cardDataUrl) {
      cardDataUrl = await generateInviteCardImage(activePass, { estateName: auth.resident?.estateName })
    }

    if (!cardDataUrl) {
      showToast('Could not generate invite card image. Try again.', 'error')
      return
    }

    const imageBlob = dataUrlToBlob(cardDataUrl)
    const imageName = `checkpass-${(activePass.visitorName || 'invite').toLowerCase().replace(/[^a-z0-9]/g, '-')}-${(activePass.id || 'pass').slice(-4).toLowerCase()}.png`
    const imageFile = new File([imageBlob], imageName, { type: 'image/png' })
    const nav = navigator as Navigator & {
      canShare?: (data?: ShareData) => boolean
      share?: (data?: ShareData) => Promise<void>
    }

    let shared = false
    if (nav.share) {
      try {
        if (!nav.canShare || nav.canShare({ files: [imageFile] })) {
          await nav.share({
            title: `${activePass.visitorName} Checkpass Invite`,
            text: `Here is your Checkpass visitor pass for ${activePass.visitorName} at ${activePass.hostUnit}.`,
            files: [imageFile],
          })
          shared = true
        } else if (nav.canShare({ text: 'text' })) {
          await nav.share({
            title: `${activePass.visitorName} Checkpass Invite`,
            text: `Here is your Checkpass visitor pass for ${activePass.visitorName} at ${activePass.hostUnit}.`,
          })
          shared = true
        }
      } catch (shareErr: unknown) {
        if (shareErr instanceof DOMException && shareErr.name === 'AbortError') {
          return
        }
        console.warn('Native share failed, falling back to download:', shareErr)
      }
    }

    if (!shared) {
      downloadImageBlob(imageBlob, imageName)
      showToast('Invite card downloaded successfully for sharing.', 'success')
    }
  } catch (error: unknown) {
    if (error instanceof DOMException && error.name === 'AbortError') return
    console.error('Share error:', error)
    showToast('Unable to share invite card right now.', 'error')
  }
}

function dataUrlToBlob(dataUrl: string): Blob {
  const [header, content] = dataUrl.split(',')
  const mimeMatch = header.match(/data:(.*?);base64/)
  const mimeType = mimeMatch?.[1] ?? 'image/png'
  const binary = atob(content)
  const bytes = new Uint8Array(binary.length)
  for (let index = 0; index < binary.length; index += 1) {
    bytes[index] = binary.charCodeAt(index)
  }
  return new Blob([bytes], { type: mimeType })
}

function downloadImageBlob(blob: Blob, fileName: string) {
  const url = URL.createObjectURL(blob)
  const link = document.createElement('a')
  link.href = url
  link.download = fileName
  link.rel = 'noopener'
  document.body.appendChild(link)
  link.click()
  setTimeout(() => {
    document.body.removeChild(link)
    URL.revokeObjectURL(url)
  }, 1200)
}

function fmtRelative(iso: string) {
  const diff = (Date.now() - new Date(iso).getTime()) / 1000
  if (diff < 3600) return `${Math.floor(diff / 60)}m ago`
  return `${Math.floor(diff / 3600)}h ago`
}

onMounted(load)
</script>

<style scoped>
.pass-detail-header {
  background: var(--w-bg);
  box-shadow: none;
}
.pass-detail-nav-bar {
  display: flex;
  align-items: center;
  justify-content: space-between;
  padding: calc(var(--ion-safe-area-top, 0px) + 14px) 16px 12px;
  background: var(--w-bg);
}
.nav-left {
  display: flex;
  align-items: center;
  gap: 8px; /* Exactly 8px spacing requested */
}
.nav-back-btn {
  background: none;
  border: none;
  padding: 4px;
  margin-left: -4px;
  display: flex;
  align-items: center;
  justify-content: center;
  color: var(--w-text);
  cursor: pointer;
  border-radius: 8px;
  transition: background 0.15s ease;
}
.nav-back-btn:active {
  background: rgba(0, 0, 0, 0.05);
}
.nav-title {
  margin: 0;
  font-size: 19px;
  font-weight: 700;
  color: var(--w-text);
  letter-spacing: -0.3px;
  line-height: 1.2;
}
.nav-right {
  display: flex;
  align-items: center;
}
.nav-share-btn {
  background: none;
  border: none;
  padding: 6px;
  margin-right: -4px;
  display: flex;
  align-items: center;
  justify-content: center;
  color: var(--w-text);
  cursor: pointer;
  border-radius: 8px;
  transition: all 0.15s ease;
}
.nav-share-btn:active {
  background: rgba(0, 0, 0, 0.05);
}

.detail-content { --background: var(--w-bg); }
.content-pad { padding: 12px 16px 100px; display: flex; flex-direction: column; gap: 16px; }
.center { align-items: center; justify-content: center; min-height: 60vh; }
.sk-card  { height: 130px; border-radius: var(--w-radius-lg); display: block; }
.sk-qr    { height: 260px; border-radius: var(--w-radius-lg); display: block; }
.sk-tiles { height: 100px; border-radius: var(--w-radius-lg); display: block; }

/* Actions */
.actions-section {
  display: flex;
  flex-direction: column;
  gap: 8px;
}
.actions-header {
  font-size: 11px;
  font-weight: 700;
  color: var(--w-muted);
  letter-spacing: 1px;
  text-transform: uppercase;
}
.guard-actions-row {
  display: flex;
  gap: 10px;
  margin-bottom: 4px;
}
.guard-btn {
  flex: 1;
  display: flex;
  align-items: center;
  justify-content: center;
  gap: 8px;
  height: 44px;
  border-radius: var(--w-radius-md);
  font-size: 14px;
  font-weight: 600;
  cursor: pointer;
  border: none;
  transition: transform 0.15s;
}
.guard-btn:active { transform: scale(0.97); }
.guard-btn-allow { background: rgba(0, 201, 122, 0.15); color: #065F46; }
.guard-btn-exit  { background: #EEF2FF; color: #4338CA; }

/* Resident actions layout: Share on its own, Call & Revoke below */
.action-row-share {
  width: 100%;
}
.btn-action {
  height: 48px;
  border-radius: var(--w-radius-md);
  display: flex;
  align-items: center;
  justify-content: center;
  font-family: var(--w-font-body);
  font-weight: 600;
  font-size: 14px;
  cursor: pointer;
  border: none;
  outline: none;
  transition: all 0.15s ease;
}
.btn-action:active {
  transform: scale(0.97);
}

.btn-share-full {
  width: 100%;
  background: #0A5C38;
  color: #ffffff;
  gap: 8px;
  box-shadow: 0 3px 10px rgba(10, 92, 56, 0.22);
}
.btn-share-full:hover {
  background: #084c2e;
}

.action-row-secondary {
  display: flex;
  gap: 10px;
  width: 100%;
}
.btn-call-half {
  flex: 1;
  background: var(--w-surface);
  color: var(--w-text);
  border: 1.5px solid var(--w-border);
  gap: 8px;
}
.btn-call-half:hover {
  background: rgba(10, 92, 56, 0.05);
}
.btn-call-disabled {
  opacity: 0.4;
  cursor: not-allowed;
  pointer-events: none;
}

.btn-revoke-half {
  flex: 1;
  background: #FFFFFF;
  color: #DC2626;
  border: 1.5px solid #FCA5A5;
  gap: 8px;
}
.btn-revoke-half svg {
  color: #DC2626;
}
.btn-revoke-half:hover {
  background: #FEF2F2;
}

/* Declared exit items */
.flagged-section { display: flex; flex-direction: column; gap: 8px; }
.declared-head {
  display: flex;
  align-items: center;
  justify-content: space-between;
  gap: 10px;
}
.declared-title {
  font-size: 11px;
  font-weight: 700;
  color: var(--w-muted);
  letter-spacing: 0.8px;
  text-transform: uppercase;
}
.declared-add {
  border: none;
  background: none;
  color: var(--w-warning);
  font-size: 12px;
  font-weight: 700;
  padding: 0;
  cursor: pointer;
  font-family: var(--w-font-body);
}
.declared-empty {
  background: var(--w-surface);
  border: 1px dashed var(--w-border);
  border-radius: var(--w-radius-md);
  padding: 14px;
  color: var(--w-muted);
  font-size: 13px;
}
.flagged-item {
  background: #fffaf3;
  border: 1.5px solid #f59e0b;
  border-radius: var(--w-radius-lg);
  padding: 10px 12px;
  display: flex;
  flex-direction: column;
  gap: 6px;
}
.flagged-main { display: flex; gap: 10px; align-items: center; }
.flagged-photo {
  width: 44px;
  height: 44px;
  border-radius: 50%;
  background: #f59e0b;
  overflow: hidden;
  display: flex;
  align-items: center;
  justify-content: center;
  flex-shrink: 0;
}
.flagged-photo--img {
  border-radius: var(--w-radius-sm);
  background: var(--w-border);
}
.flagged-photo img { width: 100%; height: 100%; object-fit: cover; }
.photo-placeholder { font-size: 16px; }
.flagged-info { flex: 1; min-width: 0; }
.flagged-desc {
  font-size: 14px;
  color: var(--w-text);
  font-weight: 600;
  line-height: 1.3;
  white-space: nowrap;
  overflow: hidden;
  text-overflow: ellipsis;
}
.flagged-meta { font-size: 12px; color: #b87400; margin-top: 2px; }
.flagged-status {
  font-size: 11px;
  font-weight: 700;
  color: #d97706;
  text-transform: uppercase;
}
.flagged-time { font-size: 12px; color: var(--w-muted); }
</style>
