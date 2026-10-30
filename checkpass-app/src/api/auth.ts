// src/api/auth.ts
import client from './client'

export const authApi = {
  login: (login: string, password: string) =>
    client.post('/v1/auth/login', { phone: login, email: login, login, password }),

  refresh: (refreshToken: string) =>
    client.post('/v1/auth/refresh', { refreshToken }),

  logout: (refreshToken: string) =>
    client.post('/v1/auth/logout', { refreshToken }),

  changePassword: (currentPassword: string, newPassword: string) =>
    client.post('/v1/auth/change-password', { currentPassword, newPassword }),

  forgotPassword: (identifier: string) =>
    client.post('/v1/auth/forgot-password', { identifier }),

  resetPassword: (identifier: string, otp: string, newPassword: string) =>
    client.post('/v1/auth/reset-password', { identifier, otp, newPassword }),

  me: () =>
    client.get('/v1/auth/me'),
}
