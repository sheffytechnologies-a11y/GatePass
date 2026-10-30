// src/stores/passes.ts
import { defineStore } from 'pinia'
import { ref } from 'vue'
import type { Pass, PassStatus } from '@/types'
import client from '@/api/client'

export const usePassesStore = defineStore('passes', () => {
  const passes = ref<Pass[]>([])
  const activePass = ref<(Pass & { isOfflineCached?: boolean }) | null>(null)
  const loading = ref(false)
  const error = ref<string | null>(null)
  const filterStatus = ref('all')
  const searchQuery = ref('')
  const sortOrder = ref<'newest' | 'oldest' | 'name'>('newest')

  // Offline caching helpers
  function cacheOfflinePass(pass: Pass) {
    try {
      const cache = JSON.parse(localStorage.getItem('cp_offline_passes') || '{}')
      cache[pass.ulid || pass.id] = pass
      if (pass.ulid) cache[pass.ulid] = pass
      if (pass.id) cache[pass.id] = pass
      localStorage.setItem('cp_offline_passes', JSON.stringify(cache))
    } catch {}
  }

  function getOfflinePass(idOrUlid: string): Pass | null {
    try {
      const cache = JSON.parse(localStorage.getItem('cp_offline_passes') || '{}')
      return cache[idOrUlid] || null
    } catch {
      return null
    }
  }

  // Derived display status — 'Item Flagged' when on-site with items declared
  function displayStatus(pass: Pass): PassStatus {
    if (pass.status === 'On-site' && pass.itemsFlagged) return 'Item Flagged'
    return pass.status
  }

  async function fetchAll() {
    loading.value = true
    error.value = null
    try {
      const res = await client.get('/v1/passes', {
        params: { status: filterStatus.value, search: searchQuery.value || undefined, sort: sortOrder.value },
      })
      passes.value = res.data.passes
      // Batch cache loaded passes
      if (Array.isArray(passes.value)) {
        passes.value.forEach(p => cacheOfflinePass(p))
      }
    } catch (e: any) {
      error.value = "Couldn't load your passes."
    } finally {
      loading.value = false
    }
  }

  async function fetchById(id: string) {
    loading.value = true
    error.value = null
    activePass.value = null
    try {
      const res = await client.get(`/v1/passes/${id}`)
      activePass.value = res.data.pass
      if (res.data.pass) cacheOfflinePass(res.data.pass)
    } catch (e: any) {
      // Check offline resilience cache
      const cached = getOfflinePass(id)
      if (cached) {
        activePass.value = { ...cached, isOfflineCached: true }
        error.value = null
      } else {
        error.value = e.response?.data?.code || 'SERVER_ERROR'
      }
    } finally {
      loading.value = false
    }
  }

  async function revokePass(id: string) {
    await client.patch(`/v1/passes/${id}/revoke`)
    const pass = passes.value.find(p => p.id === id)
    if (pass) pass.status = 'Revoked'
    if (activePass.value?.id === id) activePass.value.status = 'Revoked'
    if (activePass.value) cacheOfflinePass(activePass.value)
  }

  async function extendPass(id: string, newExpiresAt: string) {
    const res = await client.patch(`/v1/passes/${id}/extend`, { expiresAt: newExpiresAt })
    const updated = res.data.pass
    const idx = passes.value.findIndex(p => p.id === id)
    if (idx !== -1) passes.value[idx] = updated
    if (activePass.value?.id === id) activePass.value = updated
    if (updated) cacheOfflinePass(updated)
  }

  return {
    passes, activePass, loading, error, filterStatus, searchQuery, sortOrder,
    displayStatus, fetchAll, fetchById, revokePass, extendPass,
  }
})
