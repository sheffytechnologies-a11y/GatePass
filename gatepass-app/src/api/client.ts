// src/api/client.ts
import axios from 'axios'

const BASE_URL = import.meta.env.VITE_API_BASE_URL || 'https://api.wardn.ng/v1'

export const client = axios.create({
  baseURL: BASE_URL,
  timeout: 10000,
  headers: { 'Content-Type': 'application/json' },
})

// ── Request interceptor: attach token ────────────────────────
client.interceptors.request.use((config) => {
  const token = localStorage.getItem('w_token')
  if (token) config.headers.Authorization = `Bearer ${token}`
  return config
})

// ── Response interceptor: handle 401 + silent refresh ────────
let isRefreshing = false
let failedQueue: Array<{ resolve: (v: string) => void; reject: (e: unknown) => void }> = []

const processQueue = (error: unknown, token: string | null) => {
  failedQueue.forEach(p => (token ? p.resolve(token) : p.reject(error)))
  failedQueue = []
}

client.interceptors.response.use(
  (res) => res,
  async (error) => {
    const original = error.config
    const code = error.response?.data?.code

    // If token expired and we haven't tried refreshing yet
    if (error.response?.status === 401 && code === 'AUTH_TOKEN_EXPIRED' && !original._retry) {
      if (isRefreshing) {
        // Queue until current refresh resolves
        return new Promise((resolve, reject) => {
          failedQueue.push({ resolve, reject })
        }).then((token) => {
          original.headers.Authorization = `Bearer ${token}`
          return client(original)
        })
      }

      original._retry = true
      isRefreshing = true

      try {
        const refreshToken = localStorage.getItem('w_refresh_token')
        const res = await axios.post(`${BASE_URL}/auth/refresh`, { refreshToken })
        const { token, refreshToken: newRefresh } = res.data
        localStorage.setItem('w_token', token)
        localStorage.setItem('w_refresh_token', newRefresh)
        client.defaults.headers.common.Authorization = `Bearer ${token}`
        processQueue(null, token)
        return client(original)
      } catch (refreshError) {
        processQueue(refreshError, null)
        // Refresh failed — clear session and redirect to login
        localStorage.removeItem('w_token')
        localStorage.removeItem('w_refresh_token')
        window.location.href = '/login?expired=1'
        return Promise.reject(refreshError)
      } finally {
        isRefreshing = false
      }
    }

    // Account suspended
    if (code === 'AUTH_ACCOUNT_DISABLED') {
      localStorage.removeItem('w_token')
      localStorage.removeItem('w_refresh_token')
      window.location.href = '/login?suspended=1'
    }

    return Promise.reject(error)
  }
)

export default client
