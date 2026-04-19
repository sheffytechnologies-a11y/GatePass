import { createRouter, createWebHistory } from 'vue-router'
import { useAuthStore } from '@/stores/auth'

type AppRole = 'admin' | 'security'

const router = createRouter({
  history: createWebHistory(),
  routes: [
    { path: '/login', component: () => import('@/views/LoginView.vue'), meta: { public: true } },
    {
      path: '/',
      component: () => import('@/components/AppLayout.vue'),
      meta: { requiresAuth: true },
      children: [
        { path: '', redirect: '/dashboard' },
        { path: 'dashboard', component: () => import('@/views/DashboardView.vue'), meta: { roles: ['admin'] } },
        { path: 'access', component: () => import('@/views/AccessView.vue'), meta: { roles: ['admin', 'security'] } },
        { path: 'users', component: () => import('@/views/UsersView.vue'), meta: { roles: ['admin'] } },
        { path: 'passes', component: () => import('@/views/PassesView.vue'), meta: { roles: ['admin'] } },
        { path: 'passes/:id', component: () => import('@/views/PassDetailView.vue'), meta: { roles: ['admin', 'security'] } },
        { path: 'emergencies', component: () => import('@/views/EmergenciesView.vue'), meta: { roles: ['admin'] } },
        { path: 'residents', component: () => import('@/views/ResidentsView.vue'), meta: { roles: ['admin'] } },
        { path: 'notifications', component: () => import('@/views/NotificationsView.vue'), meta: { roles: ['admin'] } },
      ],
    },
    { path: '/:pathMatch(.*)*', redirect: '/' },
  ],
})

router.beforeEach((to) => {
  const auth = useAuthStore()
  if (!to.meta.public && !auth.isAuthenticated) return '/login'
  if (to.path === '/login' && auth.isAuthenticated) return auth.isSecurity ? '/access' : '/dashboard'

  const roles = to.meta.roles as AppRole[] | undefined
  if (roles && !roles.includes(auth.role)) {
    return auth.isSecurity ? '/access' : '/dashboard'
  }
})

export default router
