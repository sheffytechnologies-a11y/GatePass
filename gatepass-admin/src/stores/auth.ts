import { defineStore } from 'pinia'
import { ref, computed } from 'vue'
import { authApi } from '@/api'

export const useAuthStore = defineStore('auth', () => {
  const token = ref<string | null>(localStorage.getItem('admin_token'))
  const user  = ref<Record<string, unknown> | null>(
    JSON.parse(localStorage.getItem('admin_user') ?? 'null')
  )

  const isAuthenticated = computed(() => !!token.value)

  async function login(phone: string, password: string) {
    const res = await authApi.login(phone, password)
    const data = res.data
    token.value = data.token
    user.value  = data.data ?? data.user ?? null
    localStorage.setItem('admin_token', data.token)
    localStorage.setItem('admin_user', JSON.stringify(user.value))
  }

  async function logout() {
    try { await authApi.logout() } catch {}
    token.value = null
    user.value  = null
    localStorage.removeItem('admin_token')
    localStorage.removeItem('admin_user')
  }

  return { token, user, isAuthenticated, login, logout }
})
