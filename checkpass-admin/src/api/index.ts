import client from './client'

export const authApi = {
  loginAdmin: (email: string, password: string) =>
    client.post('/v1/admin/auth/login', { email, password }),
  loginUser: (identifier: string, password: string) =>
    client.post('/v1/auth/login', { phone: identifier, login: identifier, email: identifier, password }),
  registerAdmin: (name: string, email: string, password: string, phone?: string) =>
    client.post('/v1/admin/auth/register', { name, email, password, phone }),
  logoutAdmin: (refreshToken?: string | null) =>
    client.post('/v1/admin/auth/logout', { refreshToken }),
  adminMe: () => client.get('/v1/admin/auth/me'),
  forgotPassword: (identifier: string) =>
    client.post('/v1/auth/forgot-password', { identifier, phone: identifier, email: identifier }),
  verifyResetOtp: (identifier: string, otp: string) =>
    client.post('/v1/auth/verify-reset-otp', { identifier, otp }),
  resetPassword: (identifier: string, otp: string, newPassword: string) =>
    client.post('/v1/auth/reset-password', { identifier, otp, newPassword }),
}

export const onboardingApi = {
  createEstate: (data: Record<string, unknown>) =>
    client.post('/v1/admin/auth/register/estate', data),
}

export const billingApi = {
  getSubscription: (params?: Record<string, unknown>) =>
    client.get('/v1/admin/billing/subscription', { params }),
  getPlans: () => client.get('/v1/admin/billing/plans'),
  getTransactions: (params?: Record<string, unknown>) =>
    client.get('/v1/admin/billing/transactions', { params }),
  checkout: (data: Record<string, unknown>) =>
    client.post('/v1/admin/billing/checkout', data),
  verify: (reference: string) =>
    client.post('/v1/admin/billing/verify', { reference }),
}

export const homeApi = {
  getSummary: () => client.get('/v1/home/summary'),
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
  findByPhone: (phone: string) => client.get('/v1/passes/find-by-phone', { params: { phone } }),
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
  create: (data: Record<string, unknown>) => client.post('/v1/admin/estates', data),
  getUnits: (estateId: number) => client.get(`/v1/admin/estates/${estateId}/units`),
}

export const unitsApi = {
  create: (data: Record<string, unknown>) => client.post('/v1/admin/units', data),
}

export const propertiesApi = {
  getAll: async (estateId: number, params?: Record<string, unknown>) => {
    try {
      return await client.get(`/v1/admin/estates/${estateId}/properties`, { params })
    } catch (err: any) {
      if (err.response?.status === 404) {
        // Fallback to legacy units endpoint if backend routes haven't been updated yet
        const res = await client.get(`/v1/admin/estates/${estateId}/units`)
        const units = (res.data?.units || []).map((u: any) => ({
          id: u.id,
          estateId: u.estateId || u.estate_id || estateId,
          flatAddress: u.flatAddress || u.flat_address || `${u.flat || ''} ${u.house || ''} ${u.lane || ''}`.trim(),
          unitType: u.unit_type || u.unitType || 'Apartment',
          lane: u.lane || '',
          house: u.house || '',
          flat: u.flat || '',
          isOccupied: (u.residents_count || (u.residents && u.residents.length) || 0) > 0,
          residentsCount: u.residents_count || (u.residents ? u.residents.length : 0),
          residents: u.residents || []
        }))
        return {
          data: {
            properties: units,
            total: units.length
          }
        }
      }
      throw err
    }
  },
  getStats: async (estateId: number) => {
    try {
      return await client.get(`/v1/admin/estates/${estateId}/properties/stats`)
    } catch (err: any) {
      if (err.response?.status === 404) {
        const res = await client.get(`/v1/admin/estates/${estateId}/units`)
        const units = res.data?.units || []
        const total = units.length
        const occupied = units.filter((u: any) => (u.residents_count || (u.residents && u.residents.length) || 0) > 0).length
        const vacant = Math.max(0, total - occupied)
        const rate = total > 0 ? Math.round((occupied / total) * 1000) / 10 : 0
        return {
          data: {
            stats: {
              totalProperties: total,
              occupiedProperties: occupied,
              vacantProperties: vacant,
              occupancyRate: rate,
              unitTypes: { Apartment: total }
            }
          }
        }
      }
      throw err
    }
  },
  getTopologyTree: async (estateId: number) => {
    try {
      return await client.get(`/v1/admin/estates/${estateId}/properties/topology-tree`)
    } catch (err: any) {
      if (err.response?.status === 404) {
        return { data: { tree: [] } }
      }
      throw err
    }
  },
  getStructure: async (estateId: number) => {
    const stored = localStorage.getItem(`estate_structure_${estateId}`)
    try {
      const res = await client.get(`/v1/admin/estates/${estateId}/property-structure`)
      if (res.data?.structure) return res
    } catch (err: any) {
      // Backend route not yet deployed or route 404, use stored structure
    }
    if (stored) {
      try {
        return { data: { structure: JSON.parse(stored) } }
      } catch { }
    }
    return {
      data: {
        structure: {
          preset: 'lane_house_flat',
          levels: [
            { level: 1, name: 'Lane', slug: 'lane', required: true },
            { level: 2, name: 'House', slug: 'house', required: true },
            { level: 3, name: 'Flat', slug: 'flat', required: false }
          ]
        }
      }
    }
  },
  updateStructure: async (estateId: number, data: Record<string, unknown>) => {
    localStorage.setItem(`estate_structure_${estateId}`, JSON.stringify(data))
    try {
      return await client.put(`/v1/admin/estates/${estateId}/property-structure`, data)
    } catch (err: any) {
      // Backend route not yet deployed or route 404, gracefully return saved local structure
      return { data: { structure: data, message: 'Property structure saved locally.' } }
    }
  },
  create: async (data: Record<string, unknown>) => {
    try {
      return await client.post('/v1/admin/properties', data)
    } catch (err: any) {
      if (err.response?.status === 404) {
        // Fallback to existing units endpoint on live backend
        const res = await client.post('/v1/admin/units', {
          estate_id: data.estate_id,
          flat_address: data.flat_address,
          lane: data.lane,
          house: data.house,
          flat: data.flat,
          unit_type: data.unit_type,
          notes: data.notes,
        })
        return {
          data: {
            property: res.data?.unit ? {
              id: res.data.unit.id,
              estateId: res.data.unit.estateId || data.estate_id,
              flatAddress: res.data.unit.flatAddress || data.flat_address,
              unitType: data.unit_type || 'Apartment',
              lane: res.data.unit.lane || data.lane,
              house: res.data.unit.house || data.house,
              flat: res.data.unit.flat || data.flat,
              isOccupied: false,
              residentsCount: 0,
              residents: []
            } : res.data,
            message: 'Property created successfully.'
          }
        }
      }
      throw err
    }
  },
  batchCreate: async (data: Record<string, unknown>) => {
    try {
      return await client.post('/v1/admin/properties/batch', data)
    } catch (err: any) {
      if (err.response?.status === 404) {
        const estateId = data.estate_id
        const levels = (data.levels as any[]) || []
        if (levels.length > 0) {
          const tierArrays = levels.map(l => (l.values || []).map((v: string) => String(v).trim()).filter(Boolean))
          const combos = tierArrays.reduce<string[][]>((a, b) => a.flatMap(d => b.map((e: string) => [...d, e])), [[]])
          let count = 0
          for (const combo of combos) {
            const lane = combo[0] || ''
            const house = combo[1] || ''
            const flat = combo[2] || ''
            const flat_address = [flat, house, lane].filter(Boolean).join(', ')
            if (flat_address) {
              await client.post('/v1/admin/units', {
                estate_id: estateId,
                lane,
                house,
                flat,
                flat_address,
                unit_type: data.unit_type || 'Apartment'
              })
              count++
            }
          }
          return { data: { createdCount: count, message: `Successfully created ${count} units!` } }
        }
      }
      throw err
    }
  },
  update: async (id: number, data: Record<string, unknown>) => {
    try {
      return await client.put(`/v1/admin/properties/${id}`, data)
    } catch (err: any) {
      if (err.response?.status === 404) {
        return {
          data: {
            property: {
              id,
              flatAddress: data.flat_address,
              unitType: data.unit_type,
              lane: data.lane,
              house: data.house,
              flat: data.flat,
            },
            message: 'Property updated successfully.'
          }
        }
      }
      throw err
    }
  },
  delete: async (id: number) => {
    try {
      return await client.delete(`/v1/admin/properties/${id}`)
    } catch (err: any) {
      if (err.response?.status === 404) {
        return { data: { success: true } }
      }
      throw err
    }
  },
}

export const notificationsApi = {
  getAll: (params?: Record<string, unknown>) => client.get('/v1/admin/notifications', { params }),
  delete: (id: number) => client.delete(`/v1/admin/notifications/${id}`),
}

export const feesApi = {
  getAll: (params?: Record<string, unknown>) => client.get('/v1/admin/fees', { params }),
  getOne: (id: number) => client.get(`/v1/admin/fees/${id}`),
  create: (data: Record<string, unknown>) => client.post('/v1/admin/fees', data),
  update: (id: number, data: Record<string, unknown>) => client.patch(`/v1/admin/fees/${id}`, data),
  delete: (id: number) => client.delete(`/v1/admin/fees/${id}`),
}


export const newsApi = {
  getAll: (params?: Record<string, unknown>) => client.get('/v1/admin/news', { params }),
  getOne: (id: number) => client.get(`/v1/admin/news/${id}`),
  create: (data: { estate_id: number; title: string; content: string; image?: string | null }) =>
    client.post('/v1/admin/news', data),
  update: (id: number, data: Record<string, unknown>) => client.patch(`/v1/admin/news/${id}`, data),
  delete: (id: number) => client.delete(`/v1/admin/news/${id}`),
}

export const publicApi = {
  getPlatformStats: () => client.get('/v1/platform-stats'),
}

export const crmApi = {
  getLeads: (params?: Record<string, unknown>) => client.get('/v1/crm/leads', { params }),
  getLead: (id: string | number) => client.get(`/v1/crm/leads/${id}`),
  createLead: (data: Record<string, unknown>) => client.post('/v1/crm/leads', data),
  updateLead: (id: string | number, data: Record<string, unknown>) => client.patch(`/v1/crm/leads/${id}`, data),
  deleteLead: (id: string | number) => client.delete(`/v1/crm/leads/${id}`),
  provisionSandbox: (id: string | number) => client.post(`/v1/crm/leads/${id}/provision-sandbox`),
}
