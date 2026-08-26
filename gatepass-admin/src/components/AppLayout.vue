<template>
  <div class="dashboard-shell">
    <div v-if="sidebarOpen" class="sidebar-scrim" @click="sidebarOpen = false" />

    <aside class="sidebar" :class="{ 'sidebar--open': sidebarOpen }">
      <div class="brand">
        <span class="brand-mark">🛡️</span>
        <span class="brand-name">Gatepass</span>
      </div>

      <nav class="side-nav">
        <router-link
          v-for="item in navItems"
          :key="item.path"
          :to="item.path"
          class="side-link"
          active-class="side-link--active"
          @click="sidebarOpen = false"
        >
          <span class="side-icon" v-html="item.icon" />
          <span>{{ item.label }}</span>
        </router-link>
      </nav>

      <div class="side-footer">
        <div class="user-card">
          <div class="avatar">{{ userInitial }}</div>
          <div class="user-meta">
            <div class="user-name">{{ auth.displayName || 'Account' }}</div>
            <span class="role-chip">{{ roleLabel }}</span>
          </div>
        </div>
        <button class="logout-btn" @click="handleLogout">
          <svg width="16" height="16" viewBox="0 0 24 24" fill="none"><path d="M9 21H5a2 2 0 0 1-2-2V5a2 2 0 0 1 2-2h4" stroke="currentColor" stroke-width="1.8" stroke-linecap="round" stroke-linejoin="round"/><path d="M16 17l5-5-5-5M21 12H9" stroke="currentColor" stroke-width="1.8" stroke-linecap="round" stroke-linejoin="round"/></svg>
          Log out
        </button>
      </div>
    </aside>

    <div class="main-column">
      <header class="topbar">
        <button class="menu-btn" @click="sidebarOpen = true">
          <svg width="22" height="22" viewBox="0 0 24 24" fill="none"><path d="M3 6h18M3 12h18M3 18h18" stroke="currentColor" stroke-width="1.8" stroke-linecap="round"/></svg>
        </button>
        <div class="topbar-title">{{ pageTitle }}</div>
        <span class="topbar-role-chip">{{ roleLabel }}</span>
      </header>

      <main class="content">
        <router-view />
      </main>
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
  </div>
</template>

<script setup lang="ts">
import { computed, ref } from 'vue'
import { useRoute, useRouter } from 'vue-router'
import { useAuthStore } from '@/stores/auth'
import { useToast } from '@/composables/useToast'

const route  = useRoute()
const router = useRouter()
const auth   = useAuthStore()
const { toasts } = useToast()

const sidebarOpen = ref(false)

const ICONS = {
  dashboard: '<svg viewBox="0 0 24 24" fill="none"><rect x="3" y="3" width="7" height="9" rx="1.5" stroke="currentColor" stroke-width="1.8"/><rect x="14" y="3" width="7" height="5" rx="1.5" stroke="currentColor" stroke-width="1.8"/><rect x="14" y="12" width="7" height="9" rx="1.5" stroke="currentColor" stroke-width="1.8"/><rect x="3" y="16" width="7" height="5" rx="1.5" stroke="currentColor" stroke-width="1.8"/></svg>',
  access: '<svg viewBox="0 0 24 24" fill="none"><rect x="3" y="11" width="18" height="11" rx="2" stroke="currentColor" stroke-width="1.8"/><path d="M7 11V7a5 5 0 0 1 10 0v4" stroke="currentColor" stroke-width="1.8" stroke-linecap="round"/></svg>',
  passes: '<svg viewBox="0 0 24 24" fill="none"><rect x="2" y="5" width="20" height="14" rx="2" stroke="currentColor" stroke-width="1.8"/><path d="M2 10h20" stroke="currentColor" stroke-width="1.8"/><path d="M7 15h4M15 15h2" stroke="currentColor" stroke-width="1.8" stroke-linecap="round"/></svg>',
  residents: '<svg viewBox="0 0 24 24" fill="none"><path d="M3 9.5L12 3l9 6.5V20a1 1 0 0 1-1 1H4a1 1 0 0 1-1-1V9.5z" stroke="currentColor" stroke-width="1.8" stroke-linejoin="round"/><path d="M9 21V12h6v9" stroke="currentColor" stroke-width="1.8" stroke-linecap="round" stroke-linejoin="round"/></svg>',
  users: '<svg viewBox="0 0 24 24" fill="none"><path d="M17 21v-2a4 4 0 0 0-4-4H5a4 4 0 0 0-4 4v2" stroke="currentColor" stroke-width="1.8" stroke-linecap="round"/><circle cx="9" cy="7" r="4" stroke="currentColor" stroke-width="1.8"/><path d="M23 21v-2a4 4 0 0 0-3-3.87M16 3.13a4 4 0 0 1 0 7.75" stroke="currentColor" stroke-width="1.8" stroke-linecap="round"/></svg>',
  finance: '<svg viewBox="0 0 24 24" fill="none"><rect x="2" y="6" width="20" height="14" rx="2" stroke="currentColor" stroke-width="1.8"/><path d="M2 10h20" stroke="currentColor" stroke-width="1.8"/></svg>',
  billing: '<svg viewBox="0 0 24 24" fill="none"><path d="M6 2h12a1 1 0 0 1 1 1v18l-3-2-3 2-3-2-3 2-3-2V3a1 1 0 0 1 1-1z" stroke="currentColor" stroke-width="1.8" stroke-linejoin="round"/><path d="M8 8h8M8 12h8M8 16h4" stroke="currentColor" stroke-width="1.8" stroke-linecap="round"/></svg>',
  emergencies: '<svg viewBox="0 0 24 24" fill="none"><path d="M12 3l10 18H2L12 3z" stroke="currentColor" stroke-width="1.8" stroke-linejoin="round"/><path d="M12 9.5v4.5M12 17h.01" stroke="currentColor" stroke-width="1.8" stroke-linecap="round"/></svg>',
  notifications: '<svg viewBox="0 0 24 24" fill="none"><path d="M6 8a6 6 0 0 1 12 0c0 6.5 2.5 8.5 2.5 8.5h-17S6 14.5 6 8z" stroke="currentColor" stroke-width="1.8" stroke-linejoin="round"/><path d="M10 20.5a2 2 0 0 0 4 0" stroke="currentColor" stroke-width="1.8" stroke-linecap="round"/></svg>',
}

const navItems = computed(() => [
  { path: '/dashboard', label: 'Dashboard', icon: ICONS.dashboard },
  { path: '/access', label: 'Access', icon: ICONS.access },
  { path: '/passes', label: 'Passes', icon: ICONS.passes },
  { path: '/residents', label: 'Residents', icon: ICONS.residents },
  { path: '/users', label: 'Users', icon: ICONS.users },
  { path: '/fees', label: 'Finance', icon: ICONS.finance },
  { path: '/billing', label: 'Billing', icon: ICONS.billing },
  { path: '/emergencies', label: 'Emergencies', icon: ICONS.emergencies },
  { path: '/notifications', label: 'Notifications', icon: ICONS.notifications },
])

const pageTitle = computed(() => {
  const match = navItems.value.find(n => route.path.startsWith(n.path))
  return match?.label ?? 'Dashboard'
})

const roleLabel = computed(() => auth.isSuperAdmin ? 'Super Admin' : 'Estate Admin')

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
.dashboard-shell {
  min-height: 100dvh;
  display: flex;
}

/* ── Sidebar ─────────────────────────────── */
.sidebar {
  width: 252px;
  flex-shrink: 0;
  background: var(--c-surface);
  border-right: 1px solid var(--c-border);
  display: flex;
  flex-direction: column;
  padding: 20px 14px;
  position: sticky;
  top: 0;
  height: 100dvh;
}
.brand { display: flex; align-items: center; gap: 10px; padding: 6px 10px 22px; }
.brand-mark { font-size: 22px; }
.brand-name { font-size: 18px; font-weight: 800; font-family: var(--font-display); }

.side-nav { display: flex; flex-direction: column; gap: 2px; flex: 1; overflow-y: auto; }
.side-link {
  display: flex; align-items: center; gap: 12px;
  padding: 10px 12px; border-radius: 12px;
  color: var(--c-muted); font-size: 14px; font-weight: 600;
  transition: background 0.15s, color 0.15s;
}
.side-link:hover { background: var(--c-bg); color: var(--c-text); }
.side-link--active { background: var(--c-primary-light); color: var(--c-primary); }
.side-icon { width: 20px; height: 20px; display: flex; flex-shrink: 0; }
.side-icon :deep(svg) { width: 100%; height: 100%; }

.side-footer { border-top: 1px solid var(--c-border); padding-top: 14px; display: flex; flex-direction: column; gap: 10px; }
.user-card { display: flex; align-items: center; gap: 10px; padding: 4px 6px; }
.avatar {
  width: 36px; height: 36px; border-radius: 50%;
  background: var(--c-primary); color: white;
  display: flex; align-items: center; justify-content: center;
  font-weight: 800; flex-shrink: 0;
}
.user-meta { min-width: 0; }
.user-name { font-size: 13px; font-weight: 700; color: var(--c-text); white-space: nowrap; overflow: hidden; text-overflow: ellipsis; }
.role-chip {
  display: inline-block; margin-top: 2px;
  padding: 2px 8px; border-radius: 999px;
  background: var(--c-primary-light); color: var(--c-primary);
  font-size: 11px; font-weight: 800;
}
.logout-btn {
  display: flex; align-items: center; gap: 8px;
  border: none; background: transparent; color: var(--c-muted);
  font-size: 13px; font-weight: 600; padding: 8px 6px; border-radius: 10px;
}
.logout-btn:hover { background: var(--c-danger-light); color: var(--c-danger); }

.sidebar-scrim { display: none; }

/* ── Main column ─────────────────────────── */
.main-column { flex: 1; min-width: 0; display: flex; flex-direction: column; }
.topbar {
  display: none;
  align-items: center; gap: 12px;
  padding: 14px 18px;
  border-bottom: 1px solid var(--c-border);
  background: var(--c-surface);
  position: sticky; top: 0; z-index: 5;
}
.menu-btn { border: none; background: transparent; color: var(--c-text); display: flex; }
.topbar-title { font-size: 17px; font-weight: 700; font-family: var(--font-display); flex: 1; }
.topbar-role-chip {
  padding: 4px 10px; border-radius: 999px;
  background: var(--c-primary-light); color: var(--c-primary);
  font-size: 11px; font-weight: 800;
}

.content { flex: 1; padding: 28px 32px 48px; max-width: 1240px; width: 100%; margin: 0 auto; }

/* ── Toast ───────────────────────────────── */
.toast-container { position: fixed; bottom: 24px; right: 24px; z-index: 9999; display: flex; flex-direction: column; gap: 8px; }
.toast {
  display: flex; align-items: center; gap: 10px;
  padding: 12px 18px; border-radius: var(--radius); box-shadow: var(--shadow-md);
  font-size: 14px; font-weight: 500; background: var(--c-surface); color: var(--c-text);
  animation: slideIn 0.2s ease;
}
@keyframes slideIn { from { transform: translateX(30px); opacity: 0; } to { transform: translateX(0); opacity: 1; } }
.toast-success { border-left: 4px solid var(--c-success); }
.toast-error   { border-left: 4px solid var(--c-danger); }
.toast-warning { border-left: 4px solid var(--c-warning); }

/* ── Responsive: collapse to drawer ──────── */
@media (max-width: 900px) {
  .sidebar {
    position: fixed; left: 0; top: 0; z-index: 40;
    transform: translateX(-100%);
    transition: transform 0.2s ease;
    box-shadow: var(--shadow-md);
  }
  .sidebar--open { transform: translateX(0); }
  .sidebar-scrim { display: block; position: fixed; inset: 0; background: rgba(10,18,14,0.45); z-index: 30; }
  .topbar { display: flex; }
  .content { padding: 20px 16px 40px; }
}

@media (max-width: 640px) {
  .toast-container { bottom: 16px; right: 16px; left: 16px; }
}
</style>
