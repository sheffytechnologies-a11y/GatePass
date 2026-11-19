// src/stores/auth.ts
import { defineStore } from 'pinia'
import { ref, computed } from 'vue'
import type { Resident } from '@/types'

export interface UserRoleItem {
  role: 'resident' | 'security' | 'homeowner'
  label: string
  description?: string
  propertiesCount?: number
  unit?: string
}

export const useAuthStore = defineStore('auth', () => {
  const token = ref<string | null>(localStorage.getItem('w_token'))
  const refreshToken = ref<string | null>(localStorage.getItem('w_refresh_token'))
  const resident = ref<Resident | null>(JSON.parse(localStorage.getItem('w_resident') ?? 'null'))
  const userType = ref<'resident' | 'security'>(
    (localStorage.getItem('w_user_type') as 'resident' | 'security') ?? 'resident'
  )
  const activeRole = ref<'resident' | 'security' | 'homeowner'>(
    (localStorage.getItem('checkpass_active_role') as any) ||
    (localStorage.getItem('w_user_type') as any) ||
    'resident'
  )
  const userRoles = ref<UserRoleItem[]>(
    JSON.parse(localStorage.getItem('checkpass_user_roles') || '[]')
  )
  const onboardingComplete = ref(localStorage.getItem('w_onboarded') === 'true')

  const isAuthenticated = computed(() => !!token.value)
  const isSecurityUser = computed(() => activeRole.value === 'security')
  const isHomeownerUser = computed(() => activeRole.value === 'homeowner')
  const isResidentUser = computed(() => activeRole.value === 'resident')

  function setActiveRole(role: 'resident' | 'security' | 'homeowner') {
    activeRole.value = role
    userType.value = role === 'security' ? 'security' : 'resident'
    localStorage.setItem('checkpass_active_role', role)
    localStorage.setItem('w_user_type', userType.value)
  }

  function setUserRoles(roles: UserRoleItem[]) {
    userRoles.value = roles
    localStorage.setItem('checkpass_user_roles', JSON.stringify(roles))
  }

  function setSession(data: {
    token: string
    refreshToken: string
    resident: Resident | null
    userType?: string
    user?: object
    roles?: UserRoleItem[]
    activeRole?: 'resident' | 'security' | 'homeowner'
  }) {
    token.value = data.token
    refreshToken.value = data.refreshToken
    resident.value = data.resident ?? null

    // Determine active role
    const chosenRole = data.activeRole || (data.userType as any) || 'resident'
    activeRole.value = chosenRole
    userType.value = chosenRole === 'security' ? 'security' : 'resident'

    // Determine user roles array
    if (data.roles && data.roles.length > 0) {
      userRoles.value = data.roles
    } else {
      const derivedRoles: UserRoleItem[] = []
      if (chosenRole === 'security') {
        derivedRoles.push({ role: 'security', label: 'Gate Security', description: 'Main Gate Duty Officer' })
      } else if (chosenRole === 'homeowner' || (data.resident as any)?.role === 'owner') {
        derivedRoles.push({ role: 'homeowner', label: 'Property Owner', description: 'Estate Deed Holder' })
        derivedRoles.push({ role: 'resident', label: 'Resident Occupant', description: data.resident?.flatAddress || 'Resident View' })
      } else {
        derivedRoles.push({ role: 'resident', label: 'Resident Occupant', description: data.resident?.flatAddress || 'Household Member' })
      }
      userRoles.value = derivedRoles
    }

    onboardingComplete.value = true
    localStorage.setItem('w_token', data.token)
    localStorage.setItem('w_refresh_token', data.refreshToken)
    localStorage.setItem('w_user_type', userType.value)
    localStorage.setItem('checkpass_active_role', activeRole.value)
    localStorage.setItem('checkpass_user_roles', JSON.stringify(userRoles.value))
    localStorage.setItem('w_onboarded', 'true')
    if (data.resident) localStorage.setItem('w_resident', JSON.stringify(data.resident))
    if (data.user) localStorage.setItem('w_user', JSON.stringify(data.user))
  }

  function clearSession() {
    token.value = null
    refreshToken.value = null
    resident.value = null
    userRoles.value = []
    activeRole.value = 'resident'
    localStorage.removeItem('w_token')
    localStorage.removeItem('w_refresh_token')
    localStorage.removeItem('w_resident')
    localStorage.removeItem('w_user')
    localStorage.removeItem('w_user_type')
    localStorage.removeItem('checkpass_active_role')
    localStorage.removeItem('checkpass_user_roles')
  }

  function completeOnboarding() {
    onboardingComplete.value = true
    localStorage.setItem('w_onboarded', 'true')
  }

  function updateResident(data: Resident) {
    resident.value = data
    localStorage.setItem('w_resident', JSON.stringify(data))
  }

  return {
    token,
    refreshToken,
    resident,
    userType,
    activeRole,
    userRoles,
    onboardingComplete,
    isAuthenticated,
    isSecurityUser,
    isHomeownerUser,
    isResidentUser,
    setActiveRole,
    setUserRoles,
    setSession,
    clearSession,
    completeOnboarding,
    updateResident,
  }
})
