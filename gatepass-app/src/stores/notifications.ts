// src/stores/notifications.ts
import { defineStore } from 'pinia'
import { ref, computed } from 'vue'
import type { Notification } from '@/types'
import { MOCK_NOTIFICATIONS, delay } from '@/api/mock'

export const useNotificationsStore = defineStore('notifications', () => {
  const notifications = ref<Notification[]>([])
  const loading = ref(false)

  const unreadCount = computed(() => notifications.value.filter(n => !n.read).length)

  async function fetchAll() {
    loading.value = true
    try {
      // ── Replace with real API call ──────────────────────────
      // const res = await notificationsApi.getAll()
      // notifications.value = res.data.notifications
      await delay()
      notifications.value = MOCK_NOTIFICATIONS
    } finally {
      loading.value = false
    }
  }

  async function markAllRead() {
    // ── Replace with real API call ──────────────────────────
    // await notificationsApi.markAllRead()
    notifications.value.forEach(n => n.read = true)
  }

  async function markOneRead(id: string) {
    // ── Replace with real API call ──────────────────────────
    // await notificationsApi.markOneRead(id)
    const n = notifications.value.find(n => n.id === id)
    if (n) n.read = true
  }

  return { notifications, loading, unreadCount, fetchAll, markAllRead, markOneRead }
})
