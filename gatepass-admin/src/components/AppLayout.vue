<template>
  <div class="app-shell">
    <div class="phone-shell">
      <header class="mobile-topbar">
        <div>
          <div class="topbar-kicker">{{ auth.isSecurity ? 'Security app' : 'Admin app' }}</div>
          <div class="topbar-title">{{ pageTitle }}</div>
        </div>
        <button class="avatar-btn" @click="handleLogout">
          <span class="role-chip">{{ auth.isSecurity ? 'Gate' : 'Admin' }}</span>
          <div class="avatar">{{ userInitial }}</div>
        </button>
      </header>

      <main class="mobile-content">
        <router-view />
      </main>

      <nav class="bottom-nav">
        <template v-if="auth.isSecurity">
          <router-link to="/access" class="bottom-item" active-class="bottom-item--active">
            <svg class="nav-svg" viewBox="0 0 24 24" fill="none"><rect x="3" y="11" width="18" height="11" rx="2" stroke="currentColor" stroke-width="1.8"/><path d="M7 11V7a5 5 0 0 1 10 0v4" stroke="currentColor" stroke-width="1.8" stroke-linecap="round"/></svg>
            <span class="bottom-label">Gate</span>
          </router-link>
        </template>
        <template v-else>
          <router-link to="/dashboard" class="bottom-item" active-class="bottom-item--active">
            <svg class="nav-svg" viewBox="0 0 24 24" fill="none"><path d="M3 9.5L12 3l9 6.5V20a1 1 0 0 1-1 1H4a1 1 0 0 1-1-1V9.5z" stroke="currentColor" stroke-width="1.8" stroke-linejoin="round"/><path d="M9 21V12h6v9" stroke="currentColor" stroke-width="1.8" stroke-linecap="round" stroke-linejoin="round"/></svg>
            <span class="bottom-label">Home</span>
          </router-link>
          <router-link to="/fees" class="bottom-item" active-class="bottom-item--active">
            <svg class="nav-svg" viewBox="0 0 24 24" fill="none"><rect x="2" y="5" width="20" height="14" rx="2" stroke="currentColor" stroke-width="1.8"/><path d="M2 10h20" stroke="currentColor" stroke-width="1.8"/><path d="M7 15h4M15 15h2" stroke="currentColor" stroke-width="1.8" stroke-linecap="round"/></svg>
            <span class="bottom-label">Finance</span>
          </router-link>
          <router-link to="/access" class="bottom-item bottom-item--center" active-class="bottom-item--center-active">
            <div class="nav-center-btn">
              <svg viewBox="0 0 24 24" fill="none" width="26" height="26"><path d="M12 5v14M5 12h14" stroke="white" stroke-width="2.2" stroke-linecap="round"/></svg>
            </div>
            <span class="bottom-label">Create Pass</span>
          </router-link>
          <router-link to="/users" class="bottom-item" active-class="bottom-item--active">
            <svg class="nav-svg" viewBox="0 0 24 24" fill="none"><path d="M17 21v-2a4 4 0 0 0-4-4H5a4 4 0 0 0-4 4v2" stroke="currentColor" stroke-width="1.8" stroke-linecap="round"/><circle cx="9" cy="7" r="4" stroke="currentColor" stroke-width="1.8"/><path d="M23 21v-2a4 4 0 0 0-3-3.87M16 3.13a4 4 0 0 1 0 7.75" stroke="currentColor" stroke-width="1.8" stroke-linecap="round"/></svg>
            <span class="bottom-label">Users</span>
          </router-link>
          <router-link to="/dashboard" class="bottom-item" active-class="bottom-item--active" @click.prevent="handleLogout">
            <svg class="nav-svg" viewBox="0 0 24 24" fill="none"><path d="M20 21v-2a4 4 0 0 0-4-4H8a4 4 0 0 0-4 4v2" stroke="currentColor" stroke-width="1.8" stroke-linecap="round"/><circle cx="12" cy="7" r="4" stroke="currentColor" stroke-width="1.8"/></svg>
            <span class="bottom-label">Profile</span>
          </router-link>
        </template>
      </nav>
    </div>
  </div>

  <!-- Toast container -->
  <div class="toast-container">
    <div
      v-for="t in toasts"
      :key="t.id"
      class="toast"
      :class="`toast-${t.type}`"
    >
      <span>{{ t.type === 'success' ? '✅' : t.type === 'error' ? '❌' : '⚠️' }}</span>
      {{ t.message }}
    </div>
  </div>
</template>

<script setup lang="ts">
import { computed } from 'vue'
import { useRoute, useRouter } from 'vue-router'
import { useAuthStore } from '@/stores/auth'
import { useToast } from '@/composables/useToast'

const route  = useRoute()
const router = useRouter()
const auth   = useAuthStore()
const { toasts } = useToast()

const navItems = computed(() => auth.isSecurity
  ? [{ path: '/access', icon: '📷', label: 'Gate' }]
  : [
      { path: '/dashboard', icon: '🏡', label: 'Home' },
      { path: '/fees', icon: '💳', label: 'Finance' },
      { path: '/users', icon: '👥', label: 'Users' },
      { path: '/residents', icon: '🏠', label: 'Residents' },
      { path: '/access', icon: '🛡️', label: 'Access' },
      { path: '/emergencies', icon: '🚨', label: 'Alerts' },
    ])

const pageTitle = computed(() => {
  const match = navItems.value.find(n => route.path.startsWith(n.path))
  return match?.label ?? (auth.isSecurity ? 'Gate' : 'Home')
})

const userInitial = computed(() => {
  const name = auth.displayName ?? ''
  return name.charAt(0).toUpperCase() || 'A'
})

async function handleLogout() {
  await auth.logout()
  router.push('/login')
}
</script>

<style scoped>
.app-shell {
  height: 100dvh;
  display: flex;
  justify-content: center;
  padding: 18px;
  overflow: hidden;
}
.phone-shell {
  position: relative;
  width: min(var(--shell-width), 100%);
  height: calc(100dvh - 36px);
  background: rgba(255,255,255,0.62);
  backdrop-filter: blur(20px);
  border: 1px solid rgba(255,255,255,0.6);
  border-radius: 34px;
  box-shadow: var(--shadow-md);
  display: flex;
  flex-direction: column;
  overflow: hidden;
}
.mobile-topbar {
  display: flex;
  justify-content: space-between;
  align-items: center;
  gap: 12px;
  padding: 18px 18px 12px;
}
.topbar-kicker { font-size: 11px; text-transform: uppercase; letter-spacing: 0.15em; color: var(--c-muted); }
.topbar-title { margin-top: 4px; font-size: 22px; font-weight: 700; font-family: var(--font-display); }
.avatar-btn {
  border: none;
  background: transparent;
  display: flex;
  align-items: center;
  gap: 10px;
}
.role-chip {
  padding: 6px 10px;
  border-radius: 999px;
  background: var(--c-primary-light);
  color: var(--c-primary);
  font-size: 12px;
  font-weight: 800;
}
.avatar {
  width: 38px;
  height: 38px;
  border-radius: 50%;
  background: var(--c-primary);
  color: white;
  display: flex;
  align-items: center;
  justify-content: center;
  font-weight: 800;
}
.mobile-content {
  flex: 1;
  min-height: 0;
  overflow-y: auto;
  padding: 8px 16px 104px;
}
.bottom-nav {
  position: absolute;
  left: 0;
  right: 0;
  bottom: 0;
  z-index: 5;
  display: grid;
  grid-template-columns: 1fr 1fr 1fr 1fr 1fr;
  padding: 12px 14px 16px;
  border-top: 1px solid rgba(10, 92, 56, 0.08);
  background: rgba(255,255,255,0.96);
  backdrop-filter: blur(16px);
}
.bottom-item {
  display: flex;
  flex-direction: column;
  align-items: center;
  justify-content: center;
  gap: 4px;
  padding: 10px 8px;
  border-radius: 18px;
  color: #6d726f;
}
.bottom-item--active {
  background: var(--c-primary-light);
  color: var(--c-primary);
  font-weight: 700;
}
.bottom-item--center { color: #6d726f; }
.bottom-item--center-active .nav-center-btn { background: #0a4f30; }
.nav-svg { width: 22px; height: 22px; }
.bottom-label { font-size: 11px; }
.nav-center-btn {
  width: 48px; height: 48px; border-radius: 50%;
  background: var(--c-primary);
  display: flex; align-items: center; justify-content: center;
  margin-bottom: -4px;
  box-shadow: 0 4px 12px rgba(10,92,56,0.35);
}
@media (max-width: 520px) {
  .app-shell { padding: 0; }
  .phone-shell {
    width: 100%;
    height: 100dvh;
    border-radius: 0;
  }
}
</style>
