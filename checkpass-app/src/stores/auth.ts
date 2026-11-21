// src/stores/auth.ts
import { defineStore } from 'pinia'
import { ref, computed } from 'vue'
import type { Resident } from '@/types'

export interface UserRoleItem {
  role: 'admin' | 'homeowner' | 'resident' | 'security' | string
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
  const activeRole = ref<'admin' | 'homeowner' | 'resident' | 'security'>(
    (localStorage.getItem('checkpass_active_role') as any) ||
    (localStorage.getItem('w_user_type') as any) ||
    'resident'
  )
  const userRoles = ref<UserRoleItem[]>(
    JSON.parse(localStorage.getItem('checkpass_user_roles') || '[]')
  )
  const onboardingComplete = ref(localStorage.getItem('w_onboarded') === 'true')

  // Sanitize stored session on startup: an occupant / tenant must never have homeowner access
  const isOwner = computed(() => (resident.value as any)?.role === 'owner')
  if (resident.value && !isOwner.value) {
    if (activeRole.value === 'homeowner') {
      activeRole.value = 'resident'
      localStorage.setItem('checkpass_active_role', 'resident')
    }
    userRoles.value = userRoles.value.filter(r => r.role !== 'homeowner')
    localStorage.setItem('checkpass_user_roles', JSON.stringify(userRoles.value))
  }

  const isAuthenticated = computed(() => !!token.value)
  const isAdminUser = computed(() => activeRole.value === 'admin')
  const isSecurityUser = computed(() => activeRole.value === 'security')
  const isHomeownerUser = computed(() => isOwner.value && activeRole.value === 'homeowner')
  const isResidentUser = computed(() => activeRole.value === 'resident')

  function setActiveRole(role: 'admin' | 'homeowner' | 'resident' | 'security') {
    if (role === 'homeowner' && !isOwner.value) {
      role = 'resident'
    }
    activeRole.value = role
    userType.value = role === 'security' ? 'security' : 'resident'
    localStorage.setItem('checkpass_active_role', role)
    localStorage.setItem('w_user_type', userType.value)
  }

  function setUserRoles(roles: UserRoleItem[]) {
    const sanitized = isOwner.value ? roles : roles.filter(r => r.role !== 'homeowner')
    userRoles.value = sanitized
    localStorage.setItem('checkpass_user_roles', JSON.stringify(sanitized))
  }

  function setSession(data: {
    token: string
    refreshToken: string
    resident: Resident | null
    userType?: string
    user?: object
    roles?: UserRoleItem[]
    activeRole?: 'admin' | 'homeowner' | 'resident' | 'security'
  }) {
    token.value = data.token
    refreshToken.value = data.refreshToken
    resident.value = data.resident ?? null

    const userIsOwner = (data.resident as any)?.role === 'owner'

    // Determine active role (strictly prevent homeowner for non-owners)
    let chosenRole = data.activeRole || (data.userType as any) || 'resident'
    if (chosenRole === 'homeowner' && !userIsOwner) {
      chosenRole = 'resident'
    }
    activeRole.value = chosenRole
    userType.value = chosenRole === 'security' ? 'security' : 'resident'

    // Determine user roles array
    let rolesList: UserRoleItem[] = (data.roles && data.roles.length > 0)
      ? data.roles
      : []

    if (!rolesList || rolesList.length === 0) {
      if (chosenRole === 'security') {
        rolesList = [{ role: 'security', label: 'Gate Security', description: 'Main Gate Duty Officer' }]
      } else if (userIsOwner) {
        rolesList = [
          { role: 'homeowner', label: 'Property Owner', description: 'Estate Deed Holder' },
          { role: 'resident', label: 'Resident Occupant', description: data.resident?.flatAddress || 'Resident View' },
        ]
      } else {
        rolesList = [
          { role: 'resident', label: 'Resident Occupant', description: data.resident?.flatAddress || 'Household Member' },
        ]
      }
    }

    if (!userIsOwner) {
      rolesList = rolesList.filter(r => r.role !== 'homeowner')
      if (rolesList.length === 0) {
        rolesList = [{ role: 'resident', label: 'Resident Occupant', description: data.resident?.flatAddress || 'Resident' }]
      }
    }
    userRoles.value = rolesList

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
    if ((data as any).role && (data as any).role !== 'owner') {
      if (activeRole.value === 'homeowner') {
        activeRole.value = 'resident'
        localStorage.setItem('checkpass_active_role', 'resident')
      }
      userRoles.value = userRoles.value.filter(r => r.role !== 'homeowner')
      localStorage.setItem('checkpass_user_roles', JSON.stringify(userRoles.value))
    }
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
    isAdminUser,
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
