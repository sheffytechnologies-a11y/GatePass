// src/stores/auth.ts
import { defineStore } from 'pinia'
import { ref, computed } from 'vue'
import type { Resident } from '@/types'

export const useAuthStore = defineStore('auth', () => {
  const token = ref<string | null>(localStorage.getItem('w_token'))
  const refreshToken = ref<string | null>(localStorage.getItem('w_refresh_token'))
  const resident = ref<Resident | null>(null)
  const onboardingComplete = ref(localStorage.getItem('w_onboarded') === 'true')

  const isAuthenticated = computed(() => !!token.value)

  function setSession(data: { token: string; refreshToken: string; resident: Resident }) {
    token.value = data.token
    refreshToken.value = data.refreshToken
    resident.value = data.resident
    localStorage.setItem('w_token', data.token)
    localStorage.setItem('w_refresh_token', data.refreshToken)
  }

  function clearSession() {
    token.value = null
    refreshToken.value = null
    resident.value = null
    localStorage.removeItem('w_token')
    localStorage.removeItem('w_refresh_token')
  }

  function completeOnboarding() {
    onboardingComplete.value = true
    localStorage.setItem('w_onboarded', 'true')
  }

  return { token, refreshToken, resident, onboardingComplete, isAuthenticated, setSession, clearSession, completeOnboarding }
})
