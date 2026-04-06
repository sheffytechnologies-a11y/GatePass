// src/api/passes.ts
import client from './client'

export const passesApi = {
  getAll: (params?: { status?: string; search?: string; sort?: string }) =>
    client.get('/passes', { params }),

  getById: (id: string) =>
    client.get(`/passes/${id}`),

  create: (data: {
    visitorName: string
    visitorPhone?: string
    purpose: string
    type: string
    expiresAt: string
    recurringDays?: string[]
    vehiclePlate?: string
  }) => client.post('/passes', data),

  revoke: (id: string) =>
    client.patch(`/passes/${id}/revoke`),

  extend: (id: string, newExpiresAt: string) =>
    client.patch(`/passes/${id}/extend`, { newExpiresAt }),

  createItem: (passId: string, formData: FormData) =>
    client.post(`/v1/passes/${passId}/items`, formData, { headers: { 'Content-Type': 'multipart/form-data' } }),
}

// src/api/notifications.ts
export const notificationsApi = {
  getAll: () => client.get('/notifications'),
  markAllRead: () => client.patch('/notifications/read-all'),
  markOneRead: (id: string) => client.patch(`/notifications/${id}/read`),
}
