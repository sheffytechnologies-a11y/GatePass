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

        <!-- Estate Hero -->
        <EstateHero
          :flat-address="auth.resident?.flatAddress || ''"
          :estate-name="auth.resident?.estateName || ''"
          :gate-open="true"
          :stats="summary.stats"
        />

        <!-- Active Passes -->
        <section class="section">
          <div class="section-header">
            <div class="section-title">Active Passes</div>
            <button class="see-all" @click="router.push('/tabs/passes')">See all</button>
          </div>

          <div v-if="activePasses.length === 0">
            <EmptyState
              icon="🎟️"
              heading="No active passes"
              body="Create a pass for your next visitor and it will appear here."
              cta-label="Create a pass"
              @cta="router.push('/tabs/create')"
            />
          </div>
          <div v-else class="pass-list">
            <PassCard
              v-for="pass in activePasses"
              :key="pass.id"
              :pass="pass"
              @click="router.push(`/pass/${pass.id}`)"
            />
          </div>
        </section>

        <!-- Recent Notifications -->
        <section class="section">
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
        </section>

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
import { MOCK_HOME_SUMMARY, delay } from '@/api/mock'

const router     = useRouter()
const auth       = useAuthStore()
const notifStore = useNotificationsStore()

const summaryLoading = ref(true)
const summaryError   = ref(false)
const summary        = ref(MOCK_HOME_SUMMARY)

const firstName = computed(() => auth.resident?.name.split(' ')[0] || '')
const greeting  = computed(() => {
  const h = new Date().getHours()
  if (h < 12) return 'Good morning'
  if (h < 17) return 'Good afternoon'
  return 'Good evening'
})

const activePasses  = computed(() => summary.value.recentPasses.filter(p => p.status !== 'Exited' && p.status !== 'Revoked').slice(0, 3))
const recentNotifs  = computed(() => summary.value.notifications.slice(0, 3))

async function loadSummary() {
  summaryLoading.value = true
  summaryError.value   = false
  try {
    // ── Replace with real API call ──────────────────────────
    // const res = await client.get('/resident/home-summary')
    // summary.value = res.data
    await delay()
    summary.value = MOCK_HOME_SUMMARY
    notifStore.notifications = MOCK_HOME_SUMMARY.notifications as any
  } catch {
    summaryError.value = true
  } finally {
    summaryLoading.value = false
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
.sk-hero { height: 140px; border-radius: var(--w-radius-xl); display: block; }
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
