// src/router/index.ts
import { createRouter, createWebHistory } from '@ionic/vue-router'
import type { RouteRecordRaw } from 'vue-router'
import TabsPage from '@/views/TabsPage.vue'

const routes: RouteRecordRaw[] = [
  { path: '/', redirect: '/tabs/home' },
  {
    path: '/login',
    component: () => import('@/views/LoginView.vue'),
    meta: { public: true },
  },
  {
    path: '/onboarding',
    component: () => import('@/views/OnboardingView.vue'),
    meta: { public: true },
  },
  {
    path: '/pass/:id',
    component: () => import('@/views/PassDetailView.vue'),
  },
  {
    path: '/notifications',
    component: () => import('@/views/NotificationsView.vue'),
  },
  {
    path: '/fees/payment',
    component: () => import('@/views/FeePaymentView.vue'),
  },
  {
    path: '/fees/payment/details',
    component: () => import('@/views/PaymentDetailsView.vue'),
  },
  {
    path: '/fees/payment/success',
    component: () => import('@/views/PaymentSuccessView.vue'),
  },
  {
    path: '/tabs',
    component: TabsPage,
    children: [
      { path: '',       redirect: '/tabs/home' },
      { path: 'home',      component: () => import('@/views/HomeView.vue') },
      { path: 'create',    component: () => import('@/views/CreatePassView.vue') },
      { path: 'fees',      component: () => import('@/views/FeesView.vue') },
      { path: 'passes',    component: () => import('@/views/MyPassesView.vue') },
      { path: 'emergency', component: () => import('@/views/EmergencyView.vue') },
      { path: 'profile',   component: () => import('@/views/ProfileView.vue') },
    ],
  },
]

const router = createRouter({
  history: createWebHistory(import.meta.env.BASE_URL),
  routes,
})

// Auth guard
router.beforeEach((to) => {
  const token = localStorage.getItem('w_token')
  if (!to.meta.public && !token) {
    return '/login'
  }
  if (to.path === '/login' && token) {
    return '/tabs/home'
  }
})

export default router
