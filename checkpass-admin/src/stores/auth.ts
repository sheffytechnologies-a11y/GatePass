import { defineStore } from 'pinia'
import { ref, computed } from 'vue'
import { authApi } from '@/api'

type AppRole = 'super_admin' | 'estate_admin'

export interface EstateWorkspace {
  id: number | string
  name: string
  role?: string
  address?: string
}

export const useAuthStore = defineStore('auth', () => {
  const token = ref<string | null>(localStorage.getItem('admin_token'))
  const refreshToken = ref<string | null>(localStorage.getItem('admin_refresh_token'))
  const role = ref<AppRole>((localStorage.getItem('admin_role') as AppRole) ?? 'estate_admin')
  const user  = ref<Record<string, unknown> | null>(
    JSON.parse(localStorage.getItem('admin_user') ?? 'null')
  )

  const activeEstateId = ref<string | number | null>(localStorage.getItem('admin_active_estate_id'))
  const activeEstateName = ref<string>(localStorage.getItem('admin_active_estate_name') || 'Select Estate')
  const estates = ref<EstateWorkspace[]>(
    JSON.parse(localStorage.getItem('admin_estates') ?? '[]')
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

    // Set active estate if available in response
    if (data.admin && data.admin.estateId) {
      activeEstateId.value = data.admin.estateId as string | number
      activeEstateName.value = (data.admin.estateName as string) || 'Primary Estate'
      localStorage.setItem('admin_active_estate_id', String(data.admin.estateId))
      localStorage.setItem('admin_active_estate_name', activeEstateName.value)
    }
  }

  function setActiveEstate(estate: EstateWorkspace) {
    activeEstateId.value = estate.id
    activeEstateName.value = estate.name
    if (estate.role && (estate.role === 'super_admin' || estate.role === 'estate_admin')) {
      role.value = estate.role as AppRole
      localStorage.setItem('admin_role', estate.role)
    }
    localStorage.setItem('admin_active_estate_id', String(estate.id))
    localStorage.setItem('admin_active_estate_name', estate.name)
  }

  function setEstatesList(list: EstateWorkspace[]) {
    estates.value = list
    localStorage.setItem('admin_estates', JSON.stringify(list))
    if (!activeEstateId.value && list.length > 0) {
      setActiveEstate(list[0])
    }
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
    activeEstateId.value = null
    activeEstateName.value = 'Select Estate'
    estates.value = []
    localStorage.removeItem('admin_token')
    localStorage.removeItem('admin_refresh_token')
    localStorage.removeItem('admin_user')
    localStorage.removeItem('admin_role')
    localStorage.removeItem('admin_active_estate_id')
    localStorage.removeItem('admin_active_estate_name')
    localStorage.removeItem('admin_estates')
  }

  return {
    token, refreshToken, role, user,
    activeEstateId, activeEstateName, estates,
    isAuthenticated, isAdmin, isSuperAdmin, isEstateAdmin, displayName,
    setActiveEstate, setEstatesList,
    loginAdmin, registerAdmin, logout,
  }
})
