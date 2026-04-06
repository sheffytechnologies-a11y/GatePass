import client from './client'

export const authApi = {
  login: (phone: string, password: string) =>
    client.post('/login', { phone, password }),
  logout: () =>
    client.post('/v1/auth/logout'),
  me: () =>
    client.get('/v1/auth/me'),
}

export const dashboardApi = {
  getSummary: () => client.get('/v1/admin/dashboard'),
}

export const usersApi = {
  getAll: (params?: Record<string, unknown>) => client.get('/v1/admin/users', { params }),
  getOne: (id: number) => client.get(`/v1/admin/users/${id}`),
  create: (data: Record<string, unknown>) => client.post('/v1/admin/users', data),
  update: (id: number, data: Record<string, unknown>) => client.patch(`/v1/admin/users/${id}`, data),
  delete: (id: number) => client.delete(`/v1/admin/users/${id}`),
}

export const passesApi = {
  getAll: (params?: Record<string, unknown>) => client.get('/v1/admin/passes', { params }),
  getOne: (ulid: string) => client.get(`/v1/admin/passes/${ulid}`),
  create: (data: Record<string, unknown>) => client.post('/v1/admin/passes', data),
  revoke: (ulid: string) => client.patch(`/v1/admin/passes/${ulid}/revoke`),
  delete: (ulid: string) => client.delete(`/v1/admin/passes/${ulid}`),
}

export const emergenciesApi = {
  getAll: (params?: Record<string, unknown>) => client.get('/v1/admin/emergencies', { params }),
  acknowledge: (id: number) => client.patch(`/v1/admin/emergencies/${id}/acknowledge`),
  resolve: (id: number) => client.patch(`/v1/admin/emergencies/${id}/resolve`),
  delete: (id: number) => client.delete(`/v1/admin/emergencies/${id}`),
}

export const residentsApi = {
  getAll: (params?: Record<string, unknown>) => client.get('/v1/admin/residents', { params }),
  getOne: (id: number) => client.get(`/v1/admin/residents/${id}`),
  create: (data: Record<string, unknown>) => client.post('/v1/admin/residents', data),
  update: (id: number, data: Record<string, unknown>) => client.patch(`/v1/admin/residents/${id}`, data),
  delete: (id: number) => client.delete(`/v1/admin/residents/${id}`),
}

export const estatesApi = {
  getAll: () => client.get('/v1/admin/estates'),
  getUnits: (estateId: number) => client.get(`/v1/admin/estates/${estateId}/units`),
}

export const notificationsApi = {
  getAll: (params?: Record<string, unknown>) => client.get('/v1/admin/notifications', { params }),
  delete: (id: number) => client.delete(`/v1/admin/notifications/${id}`),
}
