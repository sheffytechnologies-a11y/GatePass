// src/types/index.ts

export type PassStatus = 'On-site' | 'Pending' | 'Exited' | 'Item Flagged' | 'Revoked' | 'Expired'

export interface FlaggedItem {
  id: string
  photoUrl: string
  description: string
  flaggedAt: string // ISO
}

export interface Pass {
  id: string
  visitorName: string
  visitorPhone: string | null
  purpose: string // 'Personal Visit' | 'Delivery' | 'Service' | 'Business' | 'Other'
  type: string   // 'One-time' | 'Recurring'
  status: PassStatus
  itemsFlagged: boolean // overlay flag — true when items declared while On-site
  hostUnit: string     // 'L3, H7, FA'
  hostName: string
  qrData: string
  vehiclePlate: string | null
  expiresAt: string  // ISO
  createdAt: string  // ISO
  arrivedAt: string | null
  exitedAt: string | null
  recurringDays: string[] | null // ['Mon','Wed','Fri']
  flaggedItems: FlaggedItem[]
}

export interface Notification {
  id: string
  type: 'arrival' | 'denied' | 'expired' | 'item_flagged'
  message: string
  passId: string | null
  read: boolean
  createdAt: string // ISO
}

export interface Resident {
  id: string
  unitId: string
  name: string
  phone: string
  flatAddress: string  // 'L3, H7, FA'
  estateName: string   // 'PHDL Estate'
  lane: string
  house: string
  flat: string
  role: 'primary' | 'member'
  pushEnabled: boolean
  isActive: boolean
}

export interface HomeSummary {
  resident: Resident
  stats: {
    onPremises: number
    activePasses: number
    newToday: number
  }
  recentPasses: Pass[]
  notifications: Notification[]
}

export interface ApiError {
  error: true
  code: string
  message: string
}
