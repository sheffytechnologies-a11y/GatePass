import { defineStore } from 'pinia'
import { ref, computed } from 'vue'
import { authApi } from '@/api'

type AppRole = 'super_admin' | 'estate_admin'

export const useAuthStore = defineStore('auth', () => {
  const token = ref<string | null>(localStorage.getItem('admin_token'))
  const refreshToken = ref<string | null>(localStorage.getItem('admin_refresh_token'))
  const role = ref<AppRole>((localStorage.getItem('admin_role') as AppRole) ?? 'estate_admin')
  const user  = ref<Record<string, unknown> | null>(
    JSON.parse(localStorage.getItem('admin_user') ?? 'null')
  )

  const isAuthenticated = computed(() => !!token.value)
  const isSuperAdmin = computed(() => role.value === 'super_admin')
  const isEstateAdmin = computed(() => role.value === 'estate_admin')
  const isAdmin = computed(() => true)
  const displayName = computed(() => String(user.value?.name ?? ''))

  function persistAdminSession(data: { token: string; refreshToken?: string | null; admin?: Record<string, unknown> | null }) {
    const resolvedRole = (data.admin?.role as AppRole) ?? 'estate_admin'
    token.value = data.token
    refreshToken.value = data.refreshToken ?? null
    role.value = resolvedRole
    user.value = data.admin ?? null
    localStorage.setItem('admin_token', data.token)
    localStorage.setItem('admin_role', resolvedRole)
    if (data.refreshToken) localStorage.setItem('admin_refresh_token', data.refreshToken)
    localStorage.setItem('admin_user', JSON.stringify(user.value))
  }

  async function loginAdmin(email: string, password: string) {
    const res = await authApi.loginAdmin(email, password)
    persistAdminSession(res.data)
  }

  async function registerAdmin(name: string, email: string, password: string, phone?: string) {
    const res = await authApi.registerAdmin(name, email, password, phone)
    persistAdminSession(res.data)
  }

  async function logout() {
    try {
      await authApi.logoutAdmin(refreshToken.value)
    } catch {}
    token.value = null
    refreshToken.value = null
    user.value  = null
    role.value = 'estate_admin'
    localStorage.removeItem('admin_token')
    localStorage.removeItem('admin_refresh_token')
    localStorage.removeItem('admin_user')
    localStorage.removeItem('admin_role')
  }

  return {
    token, refreshToken, role, user,
    isAuthenticated, isAdmin, isSuperAdmin, isEstateAdmin, displayName,
    loginAdmin, registerAdmin, logout,
  }
})
