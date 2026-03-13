// src/stores/passes.ts
import { defineStore } from 'pinia'
import { ref } from 'vue'
import type { Pass, PassStatus } from '@/types'
import { MOCK_PASSES, delay } from '@/api/mock'

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
      // ── Replace with real API call ──────────────────────────
      // const res = await passesApi.getAll({ status: filterStatus.value, search: searchQuery.value, sort: sortOrder.value })
      // passes.value = res.data.passes
      await delay()
      passes.value = MOCK_PASSES
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
      // ── Replace with real API call ──────────────────────────
      // const res = await passesApi.getById(id)
      // activePass.value = res.data.pass
      await delay()
      activePass.value = MOCK_PASSES.find(p => p.id === id) || null
      if (!activePass.value) error.value = 'PASS_NOT_FOUND'
    } catch (e: any) {
      error.value = e.response?.data?.code || 'SERVER_ERROR'
    } finally {
      loading.value = false
    }
  }

  async function revokePass(id: string) {
    // ── Replace with real API call ──────────────────────────
    // await passesApi.revoke(id)
    await delay(400)
    const pass = passes.value.find(p => p.id === id)
    if (pass) pass.status = 'Revoked'
    if (activePass.value?.id === id) activePass.value.status = 'Revoked'
  }

  async function extendPass(id: string, newExpiresAt: string) {
    // ── Replace with real API call ──────────────────────────
    // await passesApi.extend(id, newExpiresAt)
    await delay(400)
    const pass = passes.value.find(p => p.id === id)
    if (pass) pass.expiresAt = newExpiresAt
    if (activePass.value?.id === id) activePass.value.expiresAt = newExpiresAt
  }

  return { passes, activePass, loading, error, filterStatus, searchQuery, sortOrder, displayStatus, fetchAll, fetchById, revokePass, extendPass }
})
