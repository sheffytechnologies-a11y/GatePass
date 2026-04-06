import { createRouter, createWebHistory } from 'vue-router'
import { useAuthStore } from '@/stores/auth'

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
        { path: 'dashboard', component: () => import('@/views/DashboardView.vue') },
        { path: 'users', component: () => import('@/views/UsersView.vue') },
        { path: 'passes', component: () => import('@/views/PassesView.vue') },
        { path: 'passes/:id', component: () => import('@/views/PassDetailView.vue') },
        { path: 'emergencies', component: () => import('@/views/EmergenciesView.vue') },
        { path: 'residents', component: () => import('@/views/ResidentsView.vue') },
        { path: 'notifications', component: () => import('@/views/NotificationsView.vue') },
      ],
    },
    { path: '/:pathMatch(.*)*', redirect: '/' },
  ],
})

router.beforeEach((to) => {
  const auth = useAuthStore()
  if (!to.meta.public && !auth.isAuthenticated) return '/login'
  if (to.path === '/login' && auth.isAuthenticated) return '/dashboard'
})

export default router
