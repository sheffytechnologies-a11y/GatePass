<template>
  <div class="app-layout">
    <!-- Sidebar -->
    <aside class="sidebar" :class="{ 'sidebar-open': sidebarOpen }">
      <div class="sidebar-logo">
        <span class="logo-icon">🛡️</span>
        <span class="logo-text">Wardn Admin</span>
      </div>

      <nav class="sidebar-nav">
        <router-link
          v-for="item in navItems"
          :key="item.path"
          :to="item.path"
          class="nav-item"
          active-class="nav-item--active"
          @click="sidebarOpen = false"
        >
          <span class="nav-icon">{{ item.icon }}</span>
          <span class="nav-label">{{ item.label }}</span>
        </router-link>
      </nav>

      <div class="sidebar-footer">
        <button class="nav-item logout-btn" @click="handleLogout">
          <span class="nav-icon">🚪</span>
          <span class="nav-label">Logout</span>
        </button>
      </div>
    </aside>

    <!-- Backdrop -->
    <div v-if="sidebarOpen" class="sidebar-backdrop" @click="sidebarOpen = false" />

    <!-- Main -->
    <div class="main-area">
      <!-- Topbar -->
      <header class="topbar">
        <button class="menu-btn" @click="sidebarOpen = !sidebarOpen">☰</button>
        <div class="topbar-title">{{ pageTitle }}</div>
        <div class="topbar-right">
          <span class="admin-badge">Admin</span>
          <div class="avatar">{{ userInitial }}</div>
        </div>
      </header>

      <!-- Page content -->
      <main class="content">
        <router-view />
      </main>
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
import { ref, computed } from 'vue'
import { useRoute, useRouter } from 'vue-router'
import { useAuthStore } from '@/stores/auth'
import { useToast } from '@/composables/useToast'

const route  = useRoute()
const router = useRouter()
const auth   = useAuthStore()
const { toasts } = useToast()

const sidebarOpen = ref(false)

const navItems = [
  { path: '/dashboard',     icon: '📊', label: 'Dashboard' },
  { path: '/users',         icon: '👥', label: 'Users' },
  { path: '/residents',     icon: '🏠', label: 'Residents' },
  { path: '/passes',        icon: '🎟️',  label: 'Passes' },
  { path: '/emergencies',   icon: '🚨', label: 'Emergencies' },
  { path: '/notifications', icon: '🔔', label: 'Notifications' },
]

const pageTitle = computed(() => {
  const match = navItems.find(n => route.path.startsWith(n.path))
  return match?.label ?? 'Dashboard'
})

const userInitial = computed(() => {
  const name = (auth.user?.name as string) ?? ''
  return name.charAt(0).toUpperCase() || 'A'
})

async function handleLogout() {
  await auth.logout()
  router.push('/login')
}
</script>

<style scoped>
.app-layout {
  display: flex;
  min-height: 100vh;
}

/* ── Sidebar ─────────────────────── */
.sidebar {
  width: var(--sidebar-width);
  background: var(--c-primary);
  color: #fff;
  display: flex;
  flex-direction: column;
  flex-shrink: 0;
  position: fixed;
  top: 0; left: 0; bottom: 0;
  z-index: 200;
  transition: transform 0.25s ease;
}

.sidebar-logo {
  display: flex;
  align-items: center;
  gap: 10px;
  padding: 20px 20px 16px;
  border-bottom: 1px solid rgba(255,255,255,0.12);
  font-size: 17px;
  font-weight: 800;
  letter-spacing: -0.3px;
}
.logo-icon { font-size: 22px; }

.sidebar-nav {
  flex: 1;
  padding: 12px 10px;
  display: flex;
  flex-direction: column;
  gap: 2px;
  overflow-y: auto;
}

.nav-item {
  display: flex;
  align-items: center;
  gap: 10px;
  padding: 10px 12px;
  border-radius: 8px;
  font-size: 14px;
  font-weight: 500;
  color: rgba(255,255,255,0.75);
  border: none;
  background: none;
  width: 100%;
  text-align: left;
  transition: background 0.15s, color 0.15s;
}
.nav-item:hover { background: rgba(255,255,255,0.1); color: #fff; }
.nav-item--active { background: rgba(255,255,255,0.15); color: #fff; font-weight: 700; }
.nav-icon  { font-size: 17px; width: 22px; text-align: center; flex-shrink: 0; }
.nav-label { white-space: nowrap; }

.sidebar-footer { padding: 12px 10px; border-top: 1px solid rgba(255,255,255,0.12); }
.logout-btn { color: rgba(255,255,255,0.6); }
.logout-btn:hover { background: rgba(255,0,0,0.15); color: #fca5a5; }

.sidebar-backdrop {
  display: none;
  position: fixed; inset: 0;
  background: rgba(0,0,0,0.5);
  z-index: 199;
}

/* ── Main area ───────────────────── */
.main-area {
  flex: 1;
  margin-left: var(--sidebar-width);
  min-width: 0;
  display: flex;
  flex-direction: column;
}

/* ── Topbar ──────────────────────── */
.topbar {
  height: var(--topbar-height);
  background: var(--c-surface);
  border-bottom: 1px solid var(--c-border);
  display: flex;
  align-items: center;
  padding: 0 24px;
  gap: 16px;
  position: sticky;
  top: 0;
  z-index: 100;
  box-shadow: var(--shadow);
}
.menu-btn {
  display: none;
  background: none; border: none;
  font-size: 20px; color: var(--c-text);
  padding: 4px;
}
.topbar-title {
  flex: 1;
  font-size: 17px;
  font-weight: 700;
}
.topbar-right { display: flex; align-items: center; gap: 12px; }
.admin-badge {
  font-size: 12px; font-weight: 600;
  background: var(--c-primary-light); color: var(--c-primary);
  padding: 3px 10px; border-radius: 20px;
}
.avatar {
  width: 34px; height: 34px; border-radius: 50%;
  background: var(--c-primary); color: #fff;
  display: flex; align-items: center; justify-content: center;
  font-size: 14px; font-weight: 700;
}

/* ── Content ─────────────────────── */
.content {
  flex: 1;
  padding: 24px;
  overflow-y: auto;
}

/* ── Responsive ──────────────────── */
@media (max-width: 768px) {
  .sidebar {
    transform: translateX(-100%);
  }
  .sidebar.sidebar-open {
    transform: translateX(0);
  }
  .sidebar-backdrop {
    display: block;
  }
  .main-area {
    margin-left: 0;
  }
  .menu-btn {
    display: block;
  }
  .content {
    padding: 16px;
  }
}
</style>
