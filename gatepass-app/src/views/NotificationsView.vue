<template>
  <ion-page>
    <ion-header class="ion-no-border">
      <ion-toolbar>
        <ion-buttons slot="start">
          <ion-back-button default-href="/tabs/home" text="" />
        </ion-buttons>
        <ion-title>Notifications</ion-title>
        <ion-buttons v-if="store.unreadCount > 0" slot="end">
          <ion-button color="primary" @click="store.markAllRead()">Mark all read</ion-button>
        </ion-buttons>
      </ion-toolbar>
    </ion-header>

    <ion-content :fullscreen="true" class="notif-content">
      <ion-refresher slot="fixed" @ionRefresh="onRefresh">
        <ion-refresher-content />
      </ion-refresher>

      <div class="content-pad">

        <SkeletonList v-if="store.loading" :rows="5" />

        <EmptyState
          v-else-if="store.notifications.length === 0"
          icon="🔔"
          heading="No notifications yet"
          body="You'll be notified when a visitor arrives, a pass expires, or any gate activity happens."
        />

        <template v-else>
          <template v-for="(group, label) in grouped" :key="label">
            <div class="date-label">{{ label }}</div>
            <div class="notif-group">
              <div
                v-for="n in group"
                :key="n.id"
                class="notif-row"
                :class="{ unread: !n.read }"
                @click="handleTap(n)"
              >
                <span v-if="!n.read" class="unread-indicator" />
                <span class="notif-icon">{{ ICONS[n.type] }}</span>
                <div class="notif-body">
                  <div class="notif-msg">{{ n.message }}</div>
                  <div class="notif-time">{{ fmtRelative(n.createdAt) }}</div>
                </div>
              </div>
            </div>
          </template>
        </template>

      </div>
    </ion-content>
  </ion-page>
</template>

<script setup lang="ts">
import { computed, onMounted } from 'vue'
import { useRouter } from 'vue-router'
import { IonPage, IonHeader, IonToolbar, IonTitle, IonButtons, IonButton, IonBackButton, IonContent, IonRefresher, IonRefresherContent } from '@ionic/vue'
import type { Notification } from '@/types'
import { useNotificationsStore } from '@/stores/notifications'
import SkeletonList from '@/components/SkeletonList.vue'
import EmptyState from '@/components/EmptyState.vue'

const router = useRouter()
const store  = useNotificationsStore()

const ICONS: Record<string, string> = { arrival: '👤', denied: '🚫', expired: '⏱️', item_flagged: '📦' }

const grouped = computed(() => {
  const today     = new Date(); today.setHours(0,0,0,0)
  const yesterday = new Date(today); yesterday.setDate(yesterday.getDate()-1)

  const groups: Record<string, Notification[]> = {}
  for (const n of store.notifications) {
    const d = new Date(n.createdAt); d.setHours(0,0,0,0)
    const label = d >= today ? 'Today' : d >= yesterday ? 'Yesterday' : 'Earlier'
    if (!groups[label]) groups[label] = []
    groups[label].push(n)
  }
  return groups
})

async function handleTap(n: Notification) {
  await store.markOneRead(n.id)
  if (n.passId) router.push(`/pass/${n.passId}`)
}

async function onRefresh(e: CustomEvent) {
  await store.fetchAll()
  ;(e.target as HTMLIonRefresherElement).complete()
}

function fmtRelative(iso: string) {
  const diff = (Date.now() - new Date(iso).getTime()) / 1000
  if (diff < 60) return 'Just now'
  if (diff < 3600) return `${Math.floor(diff/60)}m ago`
  if (diff < 86400) return `${Math.floor(diff/3600)}h ago`
  return new Date(iso).toLocaleTimeString('en-NG',{hour:'2-digit',minute:'2-digit',hour12:true})
}

onMounted(() => store.fetchAll())
</script>

<style scoped>
.notif-content { --background: var(--w-bg); }
.content-pad   { padding: 16px 16px 80px; display: flex; flex-direction: column; gap: 12px; }
.date-label    { font-size: 12px; font-weight: 700; color: var(--w-muted); text-transform: uppercase; letter-spacing: 0.5px; padding: 4px 0; }
.notif-group   { background: var(--w-surface); border-radius: var(--w-radius-md); overflow: hidden; }
.notif-row { display: flex; align-items: center; gap: 12px; padding: 14px 16px; border-bottom: 1px solid var(--w-border); cursor: pointer; position: relative; transition: background 0.15s; }
.notif-row:last-child { border-bottom: none; }
.notif-row.unread { background: var(--w-primary-light); }
.unread-indicator { position: absolute; left: 0; top: 0; bottom: 0; width: 3px; background: var(--w-primary); border-radius: 0 2px 2px 0; }
.notif-icon { font-size: 22px; flex-shrink: 0; }
.notif-body { flex: 1; }
.notif-msg  { font-size: 14px; color: var(--w-text); line-height: 1.4; }
.notif-time { font-size: 12px; color: var(--w-muted); margin-top: 3px; }
</style>
