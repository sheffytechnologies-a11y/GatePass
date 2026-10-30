// src/stores/notifications.ts
import { defineStore } from 'pinia'
import { ref, computed } from 'vue'
import type { Notification } from '@/types'
import { notificationsApi } from '@/api/passes'
import { MOCK_NOTIFICATIONS } from '@/api/mock'

const STORAGE_KEY = 'cp_notifications'

export const useNotificationsStore = defineStore('notifications', () => {
  const notifications = ref<Notification[]>([])
  const loading = ref(false)

  const unreadCount = computed(() => notifications.value.filter(n => !n.read).length)

  function loadLocal(): Notification[] {
    try {
      const saved = localStorage.getItem(STORAGE_KEY)
      if (saved) return JSON.parse(saved)
    } catch {}
    return [...MOCK_NOTIFICATIONS]
  }

  function saveLocal() {
    try {
      localStorage.setItem(STORAGE_KEY, JSON.stringify(notifications.value))
    } catch {}
  }

  async function fetchAll() {
    loading.value = true
    try {
      const res = await notificationsApi.getAll()
      if (res?.data?.notifications && Array.isArray(res.data.notifications) && res.data.notifications.length > 0) {
        notifications.value = res.data.notifications
        saveLocal()
      } else {
        notifications.value = loadLocal()
      }
    } catch {
      notifications.value = loadLocal()
    } finally {
      loading.value = false
    }
  }

  async function markAllRead() {
    try {
      await notificationsApi.markAllRead()
    } catch {}
    notifications.value.forEach(n => (n.read = true))
    saveLocal()
  }

  async function markOneRead(id: string) {
    try {
      await notificationsApi.markOneRead(id)
    } catch {}
    const n = notifications.value.find(n => n.id === id)
    if (n) {
      n.read = true
      saveLocal()
    }
  }

  // Pre-initialize so badge and list are immediately populated
  notifications.value = loadLocal()

  return { notifications, loading, unreadCount, fetchAll, markAllRead, markOneRead }
})
