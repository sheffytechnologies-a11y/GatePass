// src/api/auth.ts
import client from './client'

export const authApi = {
  login: (phone: string, password: string) =>
    client.post('/auth/login', { phone, password }),

  refresh: (refreshToken: string) =>
    client.post('/auth/refresh', { refreshToken }),

  logout: (refreshToken: string) =>
    client.post('/auth/logout', { refreshToken }),

  changePassword: (currentPassword: string, newPassword: string) =>
    client.post('/auth/change-password', { currentPassword, newPassword }),
}
