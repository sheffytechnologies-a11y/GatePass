// src/stores/passes.ts
import { defineStore } from 'pinia'
import { ref } from 'vue'
import type { Pass, PassStatus } from '@/types'
import client from '@/api/client'

export const usePassesStore = defineStore('passes', () => {
  const passes = ref<Pass[]>([])
  const activePass = ref<Pass | null>(null)
  const loading = ref(false)
  const error = ref<string | null>(null)
  const filterStatus = ref('all')
  const searchQuery = ref('')
  const sortOrder = ref<'newest' | 'oldest' | 'name'>('newest')

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
    } catch (e: any) {
      error.value = e.response?.data?.code || 'SERVER_ERROR'
    } finally {
      loading.value = false
    }
  }

  async function revokePass(id: string) {
    await client.patch(`/v1/passes/${id}/revoke`)
    const pass = passes.value.find(p => p.id === id)
    if (pass) pass.status = 'Revoked'
    if (activePass.value?.id === id) activePass.value.status = 'Revoked'
  }

  async function extendPass(id: string, newExpiresAt: string) {
    const res = await client.patch(`/v1/passes/${id}/extend`, { expiresAt: newExpiresAt })
    const updated = res.data.pass
    const pass = passes.value.find(p => p.id === id)
    if (pass) pass.expiresAt = updated.expiresAt
    if (activePass.value?.id === id) activePass.value.expiresAt = updated.expiresAt
  }

  return { passes, activePass, loading, error, filterStatus, searchQuery, sortOrder, displayStatus, fetchAll, fetchById, revokePass, extendPass }
})
