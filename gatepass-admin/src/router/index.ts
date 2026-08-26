import { createRouter, createWebHistory } from 'vue-router'
import { useAuthStore } from '@/stores/auth'

type AppRole = 'super_admin' | 'estate_admin'

const ADMIN_ROLES: AppRole[] = ['super_admin', 'estate_admin']

const router = createRouter({
  history: createWebHistory(),
  routes: [
    { path: '/login', component: () => import('@/views/LoginView.vue'), meta: { public: true } },
    { path: '/register', component: () => import('@/views/RegisterView.vue'), meta: { public: true } },
    {
      path: '/register/estate',
      component: () => import('@/views/RegisterEstateView.vue'),
      meta: { requiresAuth: true, roles: ['estate_admin'] },
    },
    {
      path: '/',
      component: () => import('@/components/AppLayout.vue'),
      meta: { requiresAuth: true },
      children: [
        { path: '', redirect: '/dashboard' },
        { path: 'dashboard', component: () => import('@/views/DashboardView.vue'), meta: { roles: ADMIN_ROLES } },
        { path: 'access', component: () => import('@/views/AccessView.vue'), meta: { roles: ADMIN_ROLES } },
        { path: 'users', component: () => import('@/views/UsersView.vue'), meta: { roles: ADMIN_ROLES } },
        { path: 'users/new', component: () => import('@/views/UsersCreateView.vue'), meta: { roles: ADMIN_ROLES } },
        { path: 'fees', component: () => import('@/views/FeesView.vue'), meta: { roles: ADMIN_ROLES } },
        { path: 'passes', component: () => import('@/views/PassesView.vue'), meta: { roles: ADMIN_ROLES } },
        { path: 'passes/:id', component: () => import('@/views/PassDetailView.vue'), meta: { roles: ADMIN_ROLES } },
        { path: 'emergencies', component: () => import('@/views/EmergenciesView.vue'), meta: { roles: ADMIN_ROLES } },
        { path: 'residents', component: () => import('@/views/ResidentsView.vue'), meta: { roles: ADMIN_ROLES } },
        { path: 'notifications', component: () => import('@/views/NotificationsView.vue'), meta: { roles: ADMIN_ROLES } },
        { path: 'billing', component: () => import('@/views/BillingView.vue'), meta: { roles: ADMIN_ROLES } },
      ],
    },
    { path: '/:pathMatch(.*)*', redirect: '/' },
  ],
})

router.beforeEach((to) => {
  const auth = useAuthStore()
  if (!to.meta.public && !auth.isAuthenticated) return '/login'
  if ((to.path === '/login' || to.path === '/register') && auth.isAuthenticated) {
    return '/dashboard'
  }

  const roles = to.meta.roles as AppRole[] | undefined
  if (roles && !roles.includes(auth.role)) {
    return '/dashboard'
  }
})

export default router
