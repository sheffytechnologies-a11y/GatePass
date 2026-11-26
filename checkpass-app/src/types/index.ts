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
  ulid?: string
  isOfflineCached?: boolean
  visitorName: string
  visitorPhone: string | null
  purpose: string // 'Personal Visit' | 'Delivery' | 'Service' | 'Business' | 'Other'
  type: string   // 'One-time' | 'Recurring'
  status: PassStatus
  itemsFlagged: boolean // overlay flag — true when items declared while On-site
  hostUnit: string     // 'L3, H7, FA'
  hostName: string
  qrData: string
  passCode?: string | null
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
  email?: string | null
  avatar?: string | null
  flatAddress: string  // 'L3, H7, FA'
  estateName: string   // 'PHDL Estate'
  lane: string
  house: string
  flat: string
  role: 'primary' | 'member' | 'owner' | 'tenant' | string
  is_resident?: boolean
  isResident?: boolean
  pushEnabled: boolean
  isActive: boolean
}

export interface HomeSummary {
  userType?: string
  isHomeowner?: boolean
  canAccessOccupantRole?: boolean
  homeownerProperties?: any[]
  user?: {
    id?: number | string
    name?: string
    phone?: string
    email?: string
    avatar?: string | null
    estateName?: string
    assignedStation?: string
    dutyStatus?: string
  }
  resident?: Resident | null
  stats: {
    onPremises: number
    activePasses: number
    newToday: number
    grantedToday?: number
    declinedToday?: number
    totalGranted?: number
  }
  recentPasses: Pass[]
  notifications: Notification[]
  emergencies?: any[]
  announcements?: any[]
}

export interface AccessPhrase {
  phrase: string           // 'AMBER LION 27'
  validFor: string         // 'YYYY-MM-DD' (estate-local day)
  expiresAt: string        // ISO, local midnight
  flatAddress: string
  locked: boolean          // too many wrong attempts at the gate
  attemptsRemaining: number
}

export interface ApiError {
  error: true
  code: string
  message: string
}
