import { defineStore } from 'pinia'
import { ref, computed } from 'vue'
import { authApi } from '@/api'

type AppRole = 'admin' | 'security'

export const useAuthStore = defineStore('auth', () => {
  const token = ref<string | null>(localStorage.getItem('admin_token'))
  const refreshToken = ref<string | null>(localStorage.getItem('admin_refresh_token'))
  const role = ref<AppRole>((localStorage.getItem('admin_role') as AppRole) ?? 'admin')
  const user  = ref<Record<string, unknown> | null>(
    JSON.parse(localStorage.getItem('admin_user') ?? 'null')
  )

  const isAuthenticated = computed(() => !!token.value)
  const isAdmin = computed(() => role.value === 'admin')
  const isSecurity = computed(() => role.value === 'security')
  const displayName = computed(() => String(user.value?.name ?? ''))

  async function loginAdmin(email: string, password: string) {
    const res = await authApi.loginAdmin(email, password)
    const data = res.data
    token.value = data.token
    refreshToken.value = data.refreshToken ?? null
    role.value = 'admin'
    user.value  = data.admin ?? null
    localStorage.setItem('admin_token', data.token)
    localStorage.setItem('admin_role', 'admin')
    if (data.refreshToken) localStorage.setItem('admin_refresh_token', data.refreshToken)
    localStorage.setItem('admin_user', JSON.stringify(user.value))
  }

  async function loginSecurity(phone: string, password: string) {
    const res = await authApi.loginSecurity(phone, password)
    const data = res.data
    token.value = data.token
    refreshToken.value = data.refreshToken ?? null
    role.value = 'security'
    user.value  = data.data ?? data.user ?? null
    localStorage.setItem('admin_token', data.token)
    localStorage.setItem('admin_role', 'security')
    if (data.refreshToken) localStorage.setItem('admin_refresh_token', data.refreshToken)
    localStorage.setItem('admin_user', JSON.stringify(user.value))
  }

  async function logout() {
    try {
      if (role.value === 'admin') await authApi.logoutAdmin(refreshToken.value)
      else await authApi.logoutSecurity(refreshToken.value)
    } catch {}
    token.value = null
    refreshToken.value = null
    user.value  = null
    role.value = 'admin'
    localStorage.removeItem('admin_token')
    localStorage.removeItem('admin_refresh_token')
    localStorage.removeItem('admin_user')
    localStorage.removeItem('admin_role')
  }

  return { token, refreshToken, role, user, isAuthenticated, isAdmin, isSecurity, displayName, loginAdmin, loginSecurity, logout }
})
