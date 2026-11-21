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
  status?: 'Active' | 'Under Review' | 'Inactive' | 'Disabled'
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

export const defaultArtisans: ArtisanItem[] = []

export function loadArtisans(): ArtisanItem[] {
  try {
    const raw = localStorage.getItem(STORAGE_KEY)
    if (!raw) {
      return []
    }
    const parsed = JSON.parse(raw)
    if (!Array.isArray(parsed) || parsed.length === 0) {
      return []
    }
    // Purge any legacy mock artisans (art-1 through art-5) and legacy mock logs/reviews
    const valid = parsed
      .filter((item: any) => {
        const id = String(item.id || '')
        return !['art-1', 'art-2', 'art-3', 'art-4', 'art-5'].includes(id)
      })
      .map((item: any) => {
        const reviews = (Array.isArray(item.reviews) ? item.reviews : []).filter((r: any) => {
          const id = String(r.id || '')
          return !['rev-1', 'rev-2', 'rev-3', 'rev-4', 'rev-5', 'rev-6'].includes(id)
        })
        const recentGateLogs = (Array.isArray(item.recentGateLogs) ? item.recentGateLogs : []).filter((l: any) => {
          const id = String(l.id || '')
          return !['log-101', 'log-102', 'log-103', 'log-201', 'log-202', 'log-301', 'log-401', 'log-501'].includes(id)
        })
        return {
          ...item,
          reviews,
          skills: Array.isArray(item.skills) ? item.skills : [],
          recentGateLogs,
        }
      })
    if (valid.length !== parsed.length) {
      saveArtisans(valid)
    }
    return valid
  } catch {
    return []
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
  status?: 'Active' | 'Under Review' | 'Inactive' | 'Disabled'
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
