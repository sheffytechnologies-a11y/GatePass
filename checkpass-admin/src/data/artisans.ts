export interface ArtisanReview {
  id: string
  residentName: string
  residentUnit: string
  rating: number
  comment: string
  date: string
}

export interface GateAccessLog {
  id: string
  date: string
  unit: string
  resident: string
  gate: string
}

export interface ArtisanItem {
  id: string
  name: string
  trade: string
  category: string
  phone: string
  whatsapp: string
  rating: number
  avatarBg: string
  initials: string
  status?: 'Active' | 'Under Review' | 'Inactive'
  address?: string
  verifiedSince?: string
  experienceYears?: number
  gatePassCleared?: boolean
  policeCleared?: boolean
  idVerified?: boolean
  responseTime?: string
  jobsCompleted?: number
  skills?: string[]
  reviews: ArtisanReview[]
  recentGateLogs?: GateAccessLog[]
}

export const STORAGE_KEY = 'cp_estate_artisans'

export const defaultArtisans: ArtisanItem[] = [
  {
    id: 'art-1',
    name: 'Chinedu Okafor',
    trade: 'Senior Electrician & Inverter Specialist',
    category: 'Electrician',
    phone: '+2348031234567',
    whatsapp: '2348031234567',
    rating: 4.9,
    avatarBg: '#0A5C38',
    initials: 'CO',
    status: 'Active',
    address: 'Commercial Plaza, Ground Floor Shop 2',
    verifiedSince: 'Nov 2024',
    experienceYears: 9,
    gatePassCleared: true,
    policeCleared: true,
    idVerified: true,
    responseTime: '< 20 mins',
    jobsCompleted: 68,
    skills: [
      'Inverter Diagnostics',
      'Solar Battery Balancing',
      'Distribution Board Wiring',
      'Emergency Short Circuit Repair',
      'Water Heater Element Replacement',
      'Smart Meter Calibration'
    ],
    recentGateLogs: [
      { id: 'log-101', date: 'Yesterday, 14:15', unit: 'House 14 • Palm Court', resident: 'Engr. Dapo Alabi', gate: 'Main Security Gate' },
      { id: 'log-102', date: '11 Nov 2026, 09:30', unit: 'Flat 3B • Block 1', resident: 'Mrs. Fatima Bello', gate: 'Main Security Gate' },
      { id: 'log-103', date: '04 Nov 2026, 16:20', unit: 'Villa 5 • Olive Drive', resident: 'Barr. Segun Odegbami', gate: 'West Gate' }
    ],
    reviews: [
      {
        id: 'rev-1',
        residentName: 'Engr. Dapo Alabi',
        residentUnit: 'House 14 • Palm Court',
        rating: 5,
        comment: 'Chinedu arrived in under 20 minutes when my inverter tripped. Very knowledgeable, neat cabling, and fair billing.',
        date: '2 days ago',
      },
      {
        id: 'rev-2',
        residentName: 'Mrs. Fatima Bello',
        residentUnit: 'Flat 3B • Block 1',
        rating: 5,
        comment: 'Replaced my water heater circuit breaker cleanly. No mess left behind, fully tested and verified.',
        date: 'Last week',
      },
    ],
  },
  {
    id: 'art-2',
    name: 'Musa Abdullahi',
    trade: 'Master Plumber & Water Pressure Engineer',
    category: 'Plumber',
    phone: '+2348023456789',
    whatsapp: '2348023456789',
    rating: 4.8,
    avatarBg: '#1D4ED8',
    initials: 'MA',
    status: 'Active',
    address: 'Estate Maintenance Yard Workshop',
    verifiedSince: 'Jan 2025',
    experienceYears: 12,
    gatePassCleared: true,
    policeCleared: true,
    idVerified: true,
    responseTime: '< 30 mins',
    jobsCompleted: 92,
    skills: [
      'Overhead Tank Automatic Float Valves',
      'Booster Pump Troubleshooting',
      'PPR Pipe Thermal Welding',
      'Underground Leak Detection',
      'Drainage Unclogging & De-scaling'
    ],
    recentGateLogs: [
      { id: 'log-201', date: '15 Nov 2026, 11:10', unit: 'Unit 9A • Hibiscus Lane', resident: 'Chief Emeka Eze', gate: 'Main Security Gate' },
      { id: 'log-202', date: '08 Nov 2026, 14:00', unit: 'House 3 • Cedar Ave', resident: 'Alhaji Bashir', gate: 'East Gate' }
    ],
    reviews: [
      {
        id: 'rev-3',
        residentName: 'Chief Emeka Eze',
        residentUnit: 'Unit 9A • Hibiscus Lane',
        rating: 5,
        comment: 'Resolved an annoying overhead tank overflow that two other plumbers failed to fix. Very polite gentleman.',
        date: '3 days ago',
      },
    ],
  },
  {
    id: 'art-3',
    name: 'Emmanuel Bassey',
    trade: 'HVAC & Inverter AC Specialist',
    category: 'AC Repair',
    phone: '+2348056789012',
    whatsapp: '2348056789012',
    rating: 4.9,
    avatarBg: '#B45309',
    initials: 'EB',
    status: 'Active',
    address: 'External On-Call Provider (Accredited)',
    verifiedSince: 'Feb 2025',
    experienceYears: 7,
    gatePassCleared: true,
    policeCleared: true,
    idVerified: true,
    responseTime: '< 45 mins',
    jobsCompleted: 54,
    skills: [
      'Inverter Compressor Diagnostics',
      'R410A / R32 Gas Charging',
      'Indoor Coil Chemical Wash',
      'PCB Board Soldering & Repair'
    ],
    recentGateLogs: [
      { id: 'log-301', date: '14 Nov 2026, 15:45', unit: 'House 4B • Emerald St', resident: 'Dr. Chinedu Okafor', gate: 'Main Security Gate' }
    ],
    reviews: [
      {
        id: 'rev-4',
        residentName: 'Dr. Chinedu Okafor',
        residentUnit: 'House 4B • Emerald St',
        rating: 5,
        comment: 'Cleaned and recharged two inverter units. Excellent cooling restored and very neat drop cloths used indoors.',
        date: '4 days ago',
      },
    ],
  },
  {
    id: 'art-4',
    name: 'SparklePro Cleaners (Mrs. Joy)',
    trade: 'Deep Home & Move-In Cleaning',
    category: 'Cleaning',
    phone: '+2348089012345',
    whatsapp: '2348089012345',
    rating: 4.7,
    avatarBg: '#7C3AED',
    initials: 'SP',
    status: 'Active',
    address: 'Estate Commercial Annex Suite 1B',
    verifiedSince: 'Mar 2025',
    experienceYears: 6,
    gatePassCleared: true,
    policeCleared: true,
    idVerified: true,
    responseTime: '< 1 hour',
    jobsCompleted: 110,
    skills: [
      'Post-Construction Deep Cleaning',
      'Upholstery & Rug Steam Extraction',
      'Kitchen Grease Extraction',
      'Glass Window Polishing'
    ],
    recentGateLogs: [
      { id: 'log-401', date: '13 Nov 2026, 08:30', unit: 'Flat 12 • Block 5', resident: 'Tolu Oladipo', gate: 'West Gate' }
    ],
    reviews: [
      {
        id: 'rev-5',
        residentName: 'Tolu Oladipo',
        residentUnit: 'Flat 12 • Block 5',
        rating: 5,
        comment: 'SparklePro did my move-in cleaning. Kitchen cabinets looked brand new.',
        date: '5 days ago',
      },
    ],
  },
  {
    id: 'art-5',
    name: 'Sunday Adeyemi',
    trade: 'Furniture Maker & Locksmith',
    category: 'Carpentry',
    phone: '+2348078901234',
    whatsapp: '2348078901234',
    rating: 4.8,
    avatarBg: '#059669',
    initials: 'SA',
    status: 'Active',
    address: 'Estate Perimeter Workshop',
    verifiedSince: 'Apr 2025',
    experienceYears: 15,
    gatePassCleared: true,
    policeCleared: true,
    idVerified: true,
    responseTime: '< 30 mins',
    jobsCompleted: 76,
    skills: [
      'Smart & Mortise Lock Installation',
      'Sliding Wardrobe Track Realignment',
      'Kitchen Cabinet Hinge Replacement',
      'Custom Wooden Doors & Framing'
    ],
    recentGateLogs: [
      { id: 'log-501', date: '11 Nov 2026, 12:00', unit: 'House 22 • Palm Court', resident: 'Pastor Segun Martins', gate: 'Main Security Gate' }
    ],
    reviews: [
      {
        id: 'rev-6',
        residentName: 'Pastor Segun Martins',
        residentUnit: 'House 22 • Palm Court',
        rating: 5,
        comment: 'Fixed our master bedroom sliding wardrobe door that was off track for months. Sturdy craftsmanship.',
        date: '1 week ago',
      },
    ],
  },
]

export function loadArtisans(): ArtisanItem[] {
  try {
    const raw = localStorage.getItem(STORAGE_KEY)
    if (!raw) {
      saveArtisans(defaultArtisans)
      return [...defaultArtisans]
    }
    const parsed = JSON.parse(raw)
    if (!Array.isArray(parsed) || parsed.length === 0) {
      saveArtisans(defaultArtisans)
      return [...defaultArtisans]
    }
    // merge any defaults if missing
    return parsed.map((item: any) => {
      const match = defaultArtisans.find(d => d.id === item.id)
      return {
        ...match,
        ...item,
        reviews: Array.isArray(item.reviews) ? item.reviews : (match?.reviews || []),
        skills: Array.isArray(item.skills) ? item.skills : (match?.skills || []),
        recentGateLogs: Array.isArray(item.recentGateLogs) ? item.recentGateLogs : (match?.recentGateLogs || [])
      }
    })
  } catch {
    return [...defaultArtisans]
  }
}

export function saveArtisans(list: ArtisanItem[]): void {
  try {
    localStorage.setItem(STORAGE_KEY, JSON.stringify(list))
  } catch (err) {
    console.error('Failed to save artisans:', err)
  }
}

export function getArtisanById(id: string): ArtisanItem | undefined {
  const list = loadArtisans()
  return list.find(a => String(a.id) === String(id))
}

export function updateArtisan(id: string, updates: Partial<ArtisanItem>): ArtisanItem | null {
  const list = loadArtisans()
  const idx = list.findIndex(a => String(a.id) === String(id))
  if (idx === -1) return null
  const updated = { ...list[idx], ...updates }
  list[idx] = updated
  saveArtisans(list)
  return updated
}

export function addArtisanReview(
  artisanId: string,
  review: { residentName: string; residentUnit: string; rating: number; comment: string; date?: string }
): ArtisanReview | null {
  const list = loadArtisans()
  const idx = list.findIndex(a => String(a.id) === String(artisanId))
  if (idx === -1) return null

  const newRev: ArtisanReview = {
    id: 'rev-' + Date.now(),
    residentName: review.residentName.trim(),
    residentUnit: review.residentUnit.trim(),
    rating: Math.max(1, Math.min(5, Number(review.rating) || 5)),
    comment: review.comment.trim(),
    date: review.date || 'Just now',
  }

  const existingReviews = list[idx].reviews || []
  const newReviews = [newRev, ...existingReviews]
  const avg = Number((newReviews.reduce((sum, r) => sum + r.rating, 0) / newReviews.length).toFixed(1))

  list[idx].reviews = newReviews
  list[idx].rating = avg

  saveArtisans(list)
  return newRev
}

export function createArtisan(data: {
  name: string
  trade: string
  category: string
  phone: string
  whatsapp: string
  status?: 'Active' | 'Under Review' | 'Inactive'
  skills?: string[]
}): ArtisanItem {
  const list = loadArtisans()
  const initials = data.name.split(' ').filter(Boolean).map(w => w[0].toUpperCase()).slice(0, 2).join('') || 'AR'
  const colors = ['#0A5C38', '#1D4ED8', '#B45309', '#7C3AED', '#059669', '#DC2626', '#0891B2']
  const avatarBg = colors[Math.floor(Math.random() * colors.length)]

  const newArt: ArtisanItem = {
    id: 'art-' + Date.now(),
    name: data.name,
    trade: data.trade,
    category: data.category,
    phone: data.phone,
    whatsapp: data.whatsapp.replace(/\D/g, ''),
    rating: 5.0,
    avatarBg,
    initials,
    status: data.status || 'Active',
    address: 'Estate Commercial Annex / On-Call Dispatch',
    verifiedSince: new Date().toLocaleDateString('en-GB', { month: 'short', year: 'numeric' }),
    experienceYears: 5,
    gatePassCleared: true,
    policeCleared: true,
    idVerified: true,
    responseTime: '< 30 mins',
    jobsCompleted: 0,
    skills: data.skills || [data.trade],
    reviews: [],
    recentGateLogs: []
  }

  list.unshift(newArt)
  saveArtisans(list)
  return newArt
}
