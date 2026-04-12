<template>
  <ion-page>
    <ion-header class="ion-no-border">
      <ion-toolbar>
        <ion-title slot="start" class="home-title">{{ greeting }}, {{ firstName }} 👋</ion-title>
        <div slot="end">
          <NotifBell :count="notifStore.unreadCount" @click="router.push('/notifications')" />
        </div>
      </ion-toolbar>
    </ion-header>

    <ion-content :fullscreen="true" class="home-content">
      <ion-refresher slot="fixed" @ionRefresh="onRefresh">
        <ion-refresher-content />
      </ion-refresher>

      <div class="content-pad" v-if="!summaryLoading && !summaryError">

        <!-- ── SECURITY VIEW ─────────────────────────────── -->
        <template v-if="auth.isSecurityUser">
          <!-- Scan QR button -->
          <button class="scan-qr-btn" @click="scannerOpen = true">
            <span class="scan-icon">📷</span>
            <div>
              <div class="scan-btn-title">Scan QR Pass</div>
              <div class="scan-btn-sub">Verify visitor at the gate</div>
            </div>
          </button>

          <div class="security-stats">
            <div class="stat-card">
              <div class="stat-value">{{ summary.stats.onPremises }}</div>
              <div class="stat-label">On-site</div>
            </div>
            <div class="stat-card">
              <div class="stat-value">{{ summary.stats.activePasses }}</div>
              <div class="stat-label">Active</div>
            </div>
            <div class="stat-card">
              <div class="stat-value">{{ summary.stats.newToday }}</div>
              <div class="stat-label">Today</div>
            </div>
          </div>

          <section class="section">
            <div class="section-header">
              <div class="section-title">Today's Passes</div>
            </div>
            <div v-if="summary.recentPasses.length === 0">
              <EmptyState icon="🎟️" heading="No passes today" body="No visitor passes have been created today." />
            </div>
            <div v-else class="pass-list">
              <PassCard
                v-for="pass in summary.recentPasses"
                :key="pass.id"
                :pass="pass"
                @click="router.push(`/pass/${pass.id}`)"
              />
            </div>
          </section>
        </template>

        <!-- ── RESIDENT VIEW ─────────────────────────────── -->
        <template v-else>
          <EstateHero
            :flat-address="auth.resident?.flatAddress || ''"
            :estate-name="auth.resident?.estateName || ''"
            :gate-open="true"
            :stats="summary.stats"
          />

          <section class="section">
            <div class="passes-shell">
              <div class="passes-tabs">
                <button
                  class="passes-tab"
                  :class="{ active: activeHomeTab === 'passes' }"
                  @click="activeHomeTab = 'passes'"
                >
                  Active Passes
                </button>
                <button
                  class="passes-tab"
                  :class="{ active: activeHomeTab === 'news' }"
                  @click="activeHomeTab = 'news'"
                >
                  News &amp; Update
                </button>
              </div>

              <template v-if="activeHomeTab === 'passes'">
                <div v-if="activePasses.length === 0">
                  <EmptyState
                    icon="🎟️"
                    heading="No active passes"
                    body="Create a pass for your next visitor and it will appear here."
                    cta-label="Create a pass"
                    @cta="router.push('/tabs/create')"
                  />
                </div>
                <div v-else class="pass-mini-list">
                  <button
                    v-for="pass in activePasses"
                    :key="pass.id"
                    class="pass-mini-row"
                    @click="router.push(`/pass/${pass.id}`)"
                  >
                    <div class="pass-mini-avatar" :style="{ background: avatarColor(pass.visitorName) }">
                      {{ initials(pass.visitorName) }}
                    </div>
                    <div class="pass-mini-body">
                      <div class="pass-mini-name">{{ pass.visitorName }}</div>
                      <div class="pass-mini-purpose">{{ pass.purpose || 'Personal Visit' }}</div>
                    </div>
                    <span class="pass-mini-status" :class="statusClass(pass.status)">{{ pass.status }}</span>
                  </button>

                  <button class="view-all-btn" @click="router.push('/tabs/passes')">View all passes</button>
                </div>
              </template>

              <template v-else>
                <div v-if="newsLoading" class="news-mini-empty">Loading updates...</div>
                <div v-else-if="newsItems.length === 0" class="news-mini-empty">No news updates yet.</div>
                <div v-else class="news-mini-list">
                  <button
                    v-for="item in newsItems"
                    :key="item.id"
                    class="news-mini-row"
                  >
                    <div class="news-mini-top">
                      <div class="news-mini-title">{{ item.title }}</div>
                      <div class="news-mini-date">{{ fmtNewsDate(item.createdAt) }}</div>
                    </div>
                    <div class="news-mini-content">{{ item.content }}</div>
                  </button>
                </div>
              </template>
            </div>
          </section>

          <!-- <section class="section">
            <div class="section-header">
              <div class="section-title">Recent Activity</div>
            </div>
            <div class="notif-list">
              <div
                v-for="n in recentNotifs"
                :key="n.id"
                class="notif-row"
                :class="{ unread: !n.read }"
                @click="goToNotif(n)"
              >
                <span class="notif-icon">{{ notifIcon(n.type) }}</span>
                <div class="notif-body">
                  <div class="notif-msg">{{ n.message }}</div>
                  <div class="notif-time">{{ fmtRelative(n.createdAt) }}</div>
                </div>
                <span v-if="!n.read" class="unread-dot" />
              </div>
              <div v-if="recentNotifs.length === 0" class="notif-empty">No recent activity</div>
            </div>
          </section> -->

        </template>

      </div>

      <!-- Loading -->
      <div v-else-if="summaryLoading" class="content-pad">
        <ion-skeleton-text class="sk-hero" animated />
        <div class="section">
          <SkeletonList :rows="2" />
        </div>
      </div>

      <!-- Error -->
      <div v-else class="content-pad">
        <ErrorState message="Couldn't load your dashboard." @retry="loadSummary" />
      </div>

    </ion-content>

    <!-- QR Scanner -->
    <QRScannerModal
      v-if="scannerOpen"
      :is-open="true"
      @close="scannerOpen = false"
      @scanned="onQRScanned"
    />
  </ion-page>
</template>

<script setup lang="ts">
import { ref, computed, onMounted } from 'vue'
import { useRouter } from 'vue-router'
import {
  IonPage, IonHeader, IonToolbar, IonTitle, IonContent,
  IonRefresher, IonRefresherContent, IonSkeletonText,
} from '@ionic/vue'
import type { Notification } from '@/types'
import { useAuthStore } from '@/stores/auth'
import { useNotificationsStore } from '@/stores/notifications'
import EstateHero from '@/components/EstateHero.vue'
import PassCard from '@/components/PassCard.vue'
import NotifBell from '@/components/NotifBell.vue'
import EmptyState from '@/components/EmptyState.vue'
import SkeletonList from '@/components/SkeletonList.vue'
import ErrorState from '@/components/ErrorState.vue'
import QRScannerModal from '@/components/QRScannerModal.vue'
import client from '@/api/client'
import { useToast } from '@/composables/useToast'
import type { HomeSummary } from '@/types'

type NewsFeedItem = {
  id: number
  title: string
  content: string
  image: string | null
  createdAt: string | null
}

const router     = useRouter()
const auth       = useAuthStore()
const notifStore = useNotificationsStore()
const { showToast } = useToast()

const scannerOpen = ref(false)

async function onQRScanned(value: string) {
  try {
    // value is the qr_data from the pass (ULID)
    const res = await client.get(`/v1/passes/${value}`)
    const passId = res.data.pass?.ulid || res.data.pass?.id || value
    router.push(`/pass/${passId}`)
  } catch {
    showToast('Pass not found or invalid QR code', 'error')
  }
}

const summaryLoading = ref(true)
const summaryError   = ref(false)
const summary        = ref<HomeSummary>({ resident: auth.resident!, stats: { onPremises: 0, activePasses: 0, newToday: 0 }, recentPasses: [], notifications: [] })
const activeHomeTab  = ref<'passes' | 'news'>('passes')
const newsLoading    = ref(false)
const newsItems      = ref<NewsFeedItem[]>([])

const firstName = computed(() => {
  const name = auth.resident?.name || JSON.parse(localStorage.getItem('w_user') ?? 'null')?.name || ''
  return name.split(' ')[0]
})
const greeting  = computed(() => {
  const h = new Date().getHours()
  if (h < 12) return 'Good morning'
  if (h < 17) return 'Good afternoon'
  return 'Good evening'
})

const activePasses  = computed(() => summary.value.recentPasses.filter(p => p.status !== 'Exited' && p.status !== 'Revoked').slice(0, 4))
const recentNotifs  = computed(() => summary.value.notifications.slice(0, 3))

async function loadSummary() {
  summaryLoading.value = true
  summaryError.value   = false
  try {
    const res = await client.get('/v1/home/summary')
    summary.value = res.data
    notifStore.notifications = res.data.notifications
    if (res.data.resident) auth.updateResident(res.data.resident)
  } catch {
    summaryError.value = true
  } finally {
    summaryLoading.value = false
  }

  await loadNews()
}

async function loadNews() {
  newsLoading.value = true
  try {
    const res = await client.get('/v1/news')
    newsItems.value = (res.data.news || []).map((item: NewsFeedItem & { image?: string | null; createdAt?: string | null }) => ({
      id: item.id,
      title: item.title,
      content: item.content,
      image: item.image ?? null,
      createdAt: item.createdAt ?? null,
    }))
  } catch {
    newsItems.value = []
  } finally {
    newsLoading.value = false
  }
}

async function onRefresh(event: CustomEvent) {
  await loadSummary()
  ;(event.target as HTMLIonRefresherElement).complete()
}

function goToNotif(n: Notification) {
  notifStore.markOneRead(n.id)
  if (n.passId) router.push(`/pass/${n.passId}`)
}

const NOTIF_ICONS: Record<string, string> = { arrival: '👤', denied: '🚫', expired: '⏱️', item_flagged: '📦' }
function notifIcon(type: string) { return NOTIF_ICONS[type] || '🔔' }

function fmtRelative(iso: string) {
  const diff = (Date.now() - new Date(iso).getTime()) / 1000
  if (diff < 60)   return 'Just now'
  if (diff < 3600) return `${Math.floor(diff / 60)}m ago`
  if (diff < 86400)return `${Math.floor(diff / 3600)}h ago`
  return `${Math.floor(diff / 86400)}d ago`
}

function fmtNewsDate(iso: string | null) {
  if (!iso) return ''
  return new Date(iso).toLocaleDateString('en-NG', {
    day: '2-digit',
    month: 'short',
    year: 'numeric',
  })
}

function initials(name: string) {
  return name
    .split(' ')
    .filter(Boolean)
    .map((word) => word[0])
    .slice(0, 2)
    .join('')
    .toUpperCase()
}

function avatarColor(name: string) {
  const colors = ['#F08A00', '#0E9F24', '#6D17E6', '#E5007E', '#1A3F8F']
  const idx = (name.charCodeAt(0) || 0) % colors.length
  return colors[idx]
}

function statusClass(status: string) {
  if (status === 'On-site') return 'status-onsite'
  if (status === 'Pending') return 'status-pending'
  if (status === 'Expired') return 'status-expired'
  return 'status-exited'
}

onMounted(loadSummary)
</script>

<style scoped>
.home-content { --background: var(--w-bg); }
.home-title { font-size: 16px !important; font-weight: 600; }
.content-pad { padding: 16px 16px 100px; display: flex; flex-direction: column; gap: 20px; }
.section { display: flex; flex-direction: column; gap: 10px; }
.section-header { display: flex; justify-content: space-between; align-items: center; }
.section-title  { font-size: 17px; font-weight: 700; color: var(--w-text); }
.see-all { background: none; border: none; color: var(--w-primary); font-size: 14px; font-weight: 600; cursor: pointer; padding: 4px 0; }
.pass-list { display: flex; flex-direction: column; gap: 10px; }
.passes-shell { display: flex; flex-direction: column; gap: 12px; }
.passes-tabs {
  display: flex;
  gap: 16px;
  border-bottom: 1px solid var(--w-border);
  margin-bottom: 2px;
}
.passes-tab {
  background: none;
  border: none;
  padding: 0 0 10px;
  color: #8f96a3;
  font-size: 17px;
  font-weight: 500;
  cursor: pointer;
}
.passes-tab.active {
  color: #2f7d5e;
  border-bottom: 2px solid var(--w-primary);
}
.pass-mini-list { display: flex; flex-direction: column; gap: 6px; }
.pass-mini-row {
  width: 100%;
  border: none;
  border-radius: 16px;
  background: #ececef;
  display: flex;
  align-items: center;
  gap: 12px;
  padding: 14px 14px;
  text-align: left;
  cursor: pointer;
}
.pass-mini-avatar {
  width: 42px;
  height: 42px;
  border-radius: 999px;
  color: #fff;
  font-size: 24px;
  font-weight: 700;
  display: flex;
  align-items: center;
  justify-content: center;
  flex-shrink: 0;
}
.pass-mini-body { flex: 1; min-width: 0; }
.pass-mini-name {
  font-size: 18px;
  font-weight: 700;
  color: #22252b;
  line-height: 1.2;
  white-space: nowrap;
  overflow: hidden;
  text-overflow: ellipsis;
}
.pass-mini-purpose { margin-top: 3px; color: #787f8b; font-size: 13px; }
.pass-mini-status {
  flex-shrink: 0;
  padding: 4px 10px;
  border-radius: 999px;
  font-size: 12px;
  font-weight: 600;
  line-height: 1.1;
}
.status-onsite { background: #d8f0d3; color: #2a8b35; }
.status-pending { background: #efe8d2; color: #c39200; }
.status-expired { background: #f3dedf; color: #f24141; }
.status-exited { background: #d8d8da; color: #2b2f38; }
.view-all-btn {
  margin-top: 2px;
  width: 100%;
  border: none;
  border-radius: 14px;
  background: #ececef;
  color: #5f6470;
  font-size: 18px;
  font-weight: 600;
  padding: 10px 12px;
  cursor: pointer;
}
.news-mini-empty {
  border-radius: 14px;
  background: #ececef;
  color: #6b7280;
  text-align: center;
  padding: 18px;
  font-size: 14px;
}
.news-mini-list { display: flex; flex-direction: column; gap: 8px; }
.news-mini-row {
  width: 100%;
  border: none;
  border-radius: 14px;
  background: #ececef;
  padding: 12px;
  text-align: left;
}
.news-mini-top {
  display: flex;
  align-items: flex-start;
  justify-content: space-between;
  gap: 10px;
}
.news-mini-title {
  color: #232833;
  font-size: 14px;
  font-weight: 700;
  line-height: 1.3;
}
.news-mini-date {
  color: #7b8290;
  font-size: 11px;
  flex-shrink: 0;
}
.news-mini-content {
  margin-top: 6px;
  color: #636b79;
  font-size: 12px;
  line-height: 1.45;
}
.sk-hero { height: 140px; border-radius: var(--w-radius-xl); display: block; }
.security-stats { display: grid; grid-template-columns: repeat(3, 1fr); gap: 10px; }
.stat-card { background: var(--w-surface); border-radius: var(--w-radius-md); padding: 16px 12px; text-align: center; }
.stat-value { font-size: 28px; font-weight: 800; color: var(--w-primary); }
.stat-label { font-size: 12px; color: var(--w-muted); font-weight: 600; margin-top: 4px; }
.scan-qr-btn {
  display: flex; align-items: center; gap: 14px;
  width: 100%; padding: 18px 20px;
  background: var(--w-primary); border: none; border-radius: var(--w-radius-lg);
  cursor: pointer; text-align: left; transition: opacity 0.15s;
}
.scan-qr-btn:active { opacity: 0.85; }
.scan-icon { font-size: 30px; flex-shrink: 0; }
.scan-btn-title { font-size: 16px; font-weight: 700; color: #fff; }
.scan-btn-sub   { font-size: 13px; color: rgba(255,255,255,0.7); margin-top: 2px; }
.notif-list { background: var(--w-surface); border-radius: var(--w-radius-md); overflow: hidden; }
.notif-row {
  display: flex; align-items: center; gap: 12px; padding: 14px 16px;
  cursor: pointer; border-bottom: 1px solid var(--w-border);
  transition: background 0.15s;
}
.notif-row:last-child { border-bottom: none; }
.notif-row.unread { background: var(--w-primary-light); }
.notif-icon { font-size: 22px; flex-shrink: 0; }
.notif-body { flex: 1; min-width: 0; }
.notif-msg  { font-size: 13px; color: var(--w-text); line-height: 1.4; }
.notif-time { font-size: 11px; color: var(--w-muted); margin-top: 3px; }
.unread-dot { width: 8px; height: 8px; border-radius: 50%; background: var(--w-primary); flex-shrink: 0; }
.notif-empty{ padding: 24px; text-align: center; font-size: 14px; color: var(--w-muted); }
</style>
