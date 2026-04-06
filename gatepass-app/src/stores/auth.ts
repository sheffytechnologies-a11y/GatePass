// src/stores/auth.ts
import { defineStore } from 'pinia'
import { ref, computed } from 'vue'
import type { Resident } from '@/types'

export const useAuthStore = defineStore('auth', () => {
  const token = ref<string | null>(localStorage.getItem('w_token'))
  const refreshToken = ref<string | null>(localStorage.getItem('w_refresh_token'))
  const resident = ref<Resident | null>(JSON.parse(localStorage.getItem('w_resident') ?? 'null'))
  const userType = ref<'resident' | 'security'>(
    (localStorage.getItem('w_user_type') as 'resident' | 'security') ?? 'resident'
  )
  const onboardingComplete = ref(localStorage.getItem('w_onboarded') === 'true')

  const isAuthenticated = computed(() => !!token.value)
  const isSecurityUser = computed(() => userType.value === 'security')

  function setSession(data: { token: string; refreshToken: string; resident: Resident | null; userType?: string; user?: object }) {
    token.value = data.token
    refreshToken.value = data.refreshToken
    resident.value = data.resident ?? null
    userType.value = (data.userType ?? 'resident') as 'resident' | 'security'
    localStorage.setItem('w_token', data.token)
    localStorage.setItem('w_refresh_token', data.refreshToken)
    localStorage.setItem('w_user_type', userType.value)
    if (data.resident) localStorage.setItem('w_resident', JSON.stringify(data.resident))
    if (data.user) localStorage.setItem('w_user', JSON.stringify(data.user))
  }

  function clearSession() {
    token.value = null
    refreshToken.value = null
    resident.value = null
    localStorage.removeItem('w_token')
    localStorage.removeItem('w_refresh_token')
    localStorage.removeItem('w_resident')
    localStorage.removeItem('w_user')
    localStorage.removeItem('w_user_type')
  }

  function completeOnboarding() {
    onboardingComplete.value = true
    localStorage.setItem('w_onboarded', 'true')
  }

  function updateResident(data: Resident) {
    resident.value = data
    localStorage.setItem('w_resident', JSON.stringify(data))
  }

  return { token, refreshToken, resident, userType, onboardingComplete, isAuthenticated, isSecurityUser, setSession, clearSession, completeOnboarding, updateResident }
})
