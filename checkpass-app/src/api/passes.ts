// src/api/passes.ts
import client from './client'

export const passesApi = {
  getAll: (params?: { status?: string; search?: string; sort?: string }) =>
    client.get('/v1/passes', { params }),

  getById: (id: string) =>
    client.get(`/v1/passes/${id}`),

  create: (data: {
    visitorName: string
    visitorPhone?: string
    purpose: string
    type: string
    expiresAt: string
    recurringDays?: string[]
    vehiclePlate?: string
  }) => client.post('/v1/passes', data),

  revoke: (id: string) =>
    client.patch(`/v1/passes/${id}/revoke`),

  extend: (id: string, newExpiresAt: string) =>
    client.patch(`/v1/passes/${id}/extend`, { newExpiresAt }),

  allowEntry: (id: string) =>
    client.patch(`/v1/passes/${id}/allow-entry`),

  markExited: (id: string) =>
    client.patch(`/v1/passes/${id}/mark-exited`),

  createItem: (passId: string, formData: FormData) =>
    client.post(`/v1/passes/${passId}/items`, formData, { headers: { 'Content-Type': 'multipart/form-data' } }),
}

// src/api/notifications.ts
export const notificationsApi = {
  getAll: () => client.get('/v1/notifications'),
  markAllRead: () => client.patch('/v1/notifications/read-all'),
  markOneRead: (id: string) => client.patch(`/v1/notifications/${id}/read`),
}
