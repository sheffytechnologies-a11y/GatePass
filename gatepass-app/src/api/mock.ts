// src/api/mock.ts
// ─────────────────────────────────────────────────────────────
// Replace all usages of these with real API calls once the
// backend developer provides a staging URL.
// ─────────────────────────────────────────────────────────────
import type { Pass, Notification, Resident, HomeSummary } from '@/types'

export const MOCK_RESIDENT: Resident = {
  id: 'res-001',
  unitId: 'L3-H7-FA',
  name: 'Sheriff',
  phone: '+2348012345678',
  flatAddress: 'L3, H7, FA',
  estateName: 'PHDL Estate',
  lane: 'L3', house: 'H7', flat: 'FA',
  role: 'primary',
  pushEnabled: true,
  isActive: true,
}

export const MOCK_PASSES: Pass[] = [
  {
    id: 'GP-3382', visitorName: 'Chidi Okeke', visitorPhone: '+2348099887766',
    purpose: 'Personal Visit', type: 'One-time', status: 'On-site', itemsFlagged: false,
    hostUnit: 'L3, H7, FA', hostName: 'Sheriff',
    qrData: 'WARDN:GP-3382:res-001',
    vehiclePlate: null,
    expiresAt: new Date(Date.now() + 3 * 60 * 60 * 1000).toISOString(),
    createdAt: new Date(Date.now() - 2 * 60 * 60 * 1000).toISOString(),
    arrivedAt: new Date(Date.now() - 90 * 60 * 1000).toISOString(),
    exitedAt: null, recurringDays: null, flaggedItems: [],
  },
  {
    id: 'GP-3381', visitorName: 'Grace Nwosu', visitorPhone: '+2348055443322',
    purpose: 'Service', type: 'Recurring', status: 'Pending', itemsFlagged: false,
    hostUnit: 'L3, H7, FA', hostName: 'Sheriff',
    qrData: 'WARDN:GP-3381:res-001',
    vehiclePlate: null,
    expiresAt: new Date(Date.now() + 5 * 60 * 60 * 1000).toISOString(),
    createdAt: new Date(Date.now() - 7 * 24 * 60 * 60 * 1000).toISOString(),
    arrivedAt: null, exitedAt: null,
    recurringDays: ['Mon', 'Wed', 'Fri'], flaggedItems: [],
  },
  {
    id: 'GP-3380', visitorName: 'Dr. Bello', visitorPhone: null,
    purpose: 'Business', type: 'One-time', status: 'Pending', itemsFlagged: false,
    hostUnit: 'L3, H7, FA', hostName: 'Sheriff',
    qrData: 'WARDN:GP-3380:res-001',
    vehiclePlate: 'ABJ-421-KY',
    expiresAt: new Date(Date.now() + 24 * 60 * 60 * 1000).toISOString(),
    createdAt: new Date(Date.now() - 60 * 60 * 1000).toISOString(),
    arrivedAt: null, exitedAt: null, recurringDays: null, flaggedItems: [],
  },
  {
    id: 'GP-3378', visitorName: 'Tunde Fashola', visitorPhone: '+2348011223344',
    purpose: 'Delivery', type: 'One-time', status: 'Exited', itemsFlagged: false,
    hostUnit: 'L3, H7, FA', hostName: 'Sheriff',
    qrData: 'WARDN:GP-3378:res-001',
    vehiclePlate: null,
    expiresAt: new Date(Date.now() - 60 * 60 * 1000).toISOString(),
    createdAt: new Date(Date.now() - 6 * 60 * 60 * 1000).toISOString(),
    arrivedAt: new Date(Date.now() - 5 * 60 * 60 * 1000).toISOString(),
    exitedAt: new Date(Date.now() - 4 * 60 * 60 * 1000).toISOString(),
    recurringDays: null, flaggedItems: [],
  },
  {
    id: 'GP-3376', visitorName: 'Kemi Adeyemi', visitorPhone: '+2348077665544',
    purpose: 'Business', type: 'One-time', status: 'On-site', itemsFlagged: true,
    hostUnit: 'L3, H7, FA', hostName: 'Sheriff',
    qrData: 'WARDN:GP-3376:res-001',
    vehiclePlate: 'LAG-882-AA',
    expiresAt: new Date(Date.now() + 2 * 60 * 60 * 1000).toISOString(),
    createdAt: new Date(Date.now() - 4 * 60 * 60 * 1000).toISOString(),
    arrivedAt: new Date(Date.now() - 3 * 60 * 60 * 1000).toISOString(),
    exitedAt: null, recurringDays: null,
    flaggedItems: [{
      id: 'fi-001', photoUrl: '', description: 'MacBook Pro, space grey',
      flaggedAt: new Date(Date.now() - 60 * 60 * 1000).toISOString(),
    }],
  },
]

export const MOCK_NOTIFICATIONS: Notification[] = [
  { id: 'n-001', type: 'arrival', message: 'Chidi Okeke has arrived and checked in at Gate 1.', passId: 'GP-3382', read: false, createdAt: new Date(Date.now() - 90 * 60 * 1000).toISOString() },
  { id: 'n-002', type: 'item_flagged', message: "Security has been notified about an item declared on Kemi Adeyemi's pass.", passId: 'GP-3376', read: false, createdAt: new Date(Date.now() - 60 * 60 * 1000).toISOString() },
  { id: 'n-003', type: 'expired', message: "Pass for Tunde Fashola has expired and been archived.", passId: 'GP-3378', read: true, createdAt: new Date(Date.now() - 5 * 60 * 60 * 1000).toISOString() },
]

export const MOCK_HOME_SUMMARY: HomeSummary = {
  resident: MOCK_RESIDENT,
  stats: { onPremises: 2, activePasses: 3, newToday: 2 },
  recentPasses: MOCK_PASSES.slice(0, 3),
  notifications: MOCK_NOTIFICATIONS.slice(0, 3),
}

// Simulates a network delay
export const delay = (ms = 600) => new Promise(r => setTimeout(r, ms))
