// src/utils/inviteCardRenderer.ts
import QRCode from 'qrcode'
import type { Pass } from '@/types'

export interface RenderPassOptions {
  estateName?: string
}

/**
 * Formats expiry date in a clean readable form: e.g. "Today · 6:00 PM" or "27 Oct · 6:00 PM"
 */
export function formatPassExpiry(expiresAt: string, type?: string, recurringDays?: string[] | null): string {
  if (type === 'Recurring' && recurringDays && recurringDays.length > 0) {
    return recurringDays.join(', ')
  }
  if (!expiresAt) return 'Valid upon arrival'
  try {
    const d = new Date(expiresAt)
    const now = new Date()
    const isToday = d.toDateString() === now.toDateString()
    const timeStr = d.toLocaleTimeString('en-NG', { hour: 'numeric', minute: '2-digit', hour12: true })
    if (isToday) {
      return `Today · ${timeStr}`
    }
    const dateStr = d.toLocaleDateString('en-NG', { day: 'numeric', month: 'short' })
    return `${dateStr} · ${timeStr}`
  } catch {
    return expiresAt
  }
}

/**
 * Draws rounded rectangle helper for Canvas 2D
 */
function drawRoundedRect(
  ctx: CanvasRenderingContext2D,
  x: number,
  y: number,
  width: number,
  height: number,
  radius: number | number[]
) {
  ctx.beginPath()
  if (typeof ctx.roundRect === 'function') {
    ctx.roundRect(x, y, width, height, radius)
  } else {
    const r = typeof radius === 'number' ? radius : radius[0]
    ctx.moveTo(x + r, y)
    ctx.arcTo(x + width, y, x + width, y + height, r)
    ctx.arcTo(x + width, y + height, x, y + height, r)
    ctx.arcTo(x, y + height, x, y, r)
    ctx.arcTo(x, y, x + width, y, r)
    ctx.closePath()
  }
}

/**
 * Renders a high-resolution, branded 750x1160px PNG image of the Checkpass Invite Card
 */
export async function generateInviteCardImage(pass: Pass, options: RenderPassOptions = {}): Promise<string> {
  // Generate crisp QR code
  const qrDataUrl = await QRCode.toDataURL(pass.qrData || `CHECKPASS:${pass.id}`, {
    width: 380,
    margin: 1,
    color: {
      dark: '#0A5C38',
      light: '#FFFFFF',
    },
  })

  // Load QR image element
  const qrImg = await new Promise<HTMLImageElement>((resolve, reject) => {
    const img = new Image()
    img.crossOrigin = 'anonymous'
    img.onload = () => resolve(img)
    img.onerror = reject
    img.src = qrDataUrl
  })

  // Setup 2x retina canvas (750x1180px)
  const width = 750
  const height = 1180
  const canvas = document.createElement('canvas')
  canvas.width = width
  canvas.height = height
  const ctx = canvas.getContext('2d')
  if (!ctx) throw new Error('Could not get canvas context')

  // Background canvas fill (soft sage light tone)
  ctx.fillStyle = '#EEF1EE'
  ctx.fillRect(0, 0, width, height)

  // Card geometry
  const cardX = 36
  const cardY = 36
  const cardW = width - cardX * 2 // 678px
  const cardH = height - cardY * 2 // 1108px
  const cardRadius = 32

  // Outer Card Shadow
  ctx.save()
  ctx.shadowColor = 'rgba(10, 92, 56, 0.16)'
  ctx.shadowBlur = 36
  ctx.shadowOffsetY = 14
  ctx.fillStyle = '#FFFFFF'
  drawRoundedRect(ctx, cardX, cardY, cardW, cardH, cardRadius)
  ctx.fill()
  ctx.restore()

  // Clip to rounded card
  ctx.save()
  drawRoundedRect(ctx, cardX, cardY, cardW, cardH, cardRadius)
  ctx.clip()

  // ══ 1. HEADER (Deep Emerald Gradient) ══════════════════════════════════════
  const headerHeight = 330
  const grad = ctx.createLinearGradient(cardX, cardY, cardX + cardW, cardY + headerHeight)
  grad.addColorStop(0, '#0A5C38')
  grad.addColorStop(1, '#004422')
  ctx.fillStyle = grad
  ctx.fillRect(cardX, cardY, cardW, headerHeight)

  // Subtle Header Watermark Emblem in top right
  ctx.save()
  ctx.translate(cardX + cardW - 60, cardY + 50)
  ctx.scale(1.3, 1.3)
  ctx.fillStyle = 'rgba(255, 255, 255, 0.08)'
  drawCheckpassEmblem(ctx)
  ctx.restore()

  // Top Bar: Checkpass Brand + Visitor Pass Badge
  // Checkpass Logo Symbol + Text
  ctx.save()
  const logoX = cardX + 32
  const logoY = cardY + 44

  // Small logo icon
  ctx.save()
  ctx.translate(logoX, logoY)
  ctx.scale(0.24, 0.24)
  ctx.fillStyle = '#FFFFFF'
  drawCheckpassEmblem(ctx)
  ctx.restore()

  ctx.fillStyle = '#FFFFFF'
  ctx.font = 'bold 26px "Bricolage Grotesque", -apple-system, sans-serif'
  ctx.fillText('Checkpass', logoX + 38, logoY + 22)

  // Visitor Pass Pill Badge
  const badgeText = 'VISITOR PASS'
  ctx.font = 'bold 13px "DM Sans", -apple-system, sans-serif'
  const badgeW = ctx.measureText(badgeText).width + 24
  const badgeH = 30
  const badgeX = cardX + cardW - 32 - badgeW
  const badgeY = cardY + 36

  ctx.fillStyle = 'rgba(255, 255, 255, 0.18)'
  drawRoundedRect(ctx, badgeX, badgeY, badgeW, badgeH, 15)
  ctx.fill()
  ctx.strokeStyle = 'rgba(255, 255, 255, 0.25)'
  ctx.lineWidth = 1.5
  ctx.stroke()

  ctx.fillStyle = '#E2FDEE'
  ctx.fillText(badgeText, badgeX + 12, badgeY + 20)
  ctx.restore()

  // ══ 2. VISITOR IDENTITY FROSTED POD ════════════════════════════════════════
  const idBoxX = cardX + 28
  const idBoxY = cardY + 98
  const idBoxW = cardW - 56
  const idBoxH = 194

  ctx.save()
  ctx.fillStyle = 'rgba(0, 0, 0, 0.26)'
  drawRoundedRect(ctx, idBoxX, idBoxY, idBoxW, idBoxH, 20)
  ctx.fill()
  ctx.strokeStyle = 'rgba(255, 255, 255, 0.16)'
  ctx.lineWidth = 1.5
  ctx.stroke()

  // Avatar Circle
  const avX = idBoxX + 24
  const avY = idBoxY + (idBoxH - 96) / 2
  const avSize = 96
  const avGrad = ctx.createLinearGradient(avX, avY, avX + avSize, avY + avSize)
  avGrad.addColorStop(0, '#00C97A')
  avGrad.addColorStop(1, '#0A5C38')
  ctx.fillStyle = avGrad
  drawRoundedRect(ctx, avX, avY, avSize, avSize, 24)
  ctx.fill()
  ctx.strokeStyle = '#00C97A'
  ctx.lineWidth = 3
  ctx.stroke()

  // Initials
  const initials = (pass.visitorName || 'Guest')
    .split(' ')
    .filter(Boolean)
    .slice(0, 2)
    .map(w => w[0].toUpperCase())
    .join('')
  ctx.fillStyle = '#FFFFFF'
  ctx.font = 'bold 36px "Bricolage Grotesque", -apple-system, sans-serif'
  ctx.textAlign = 'center'
  ctx.textBaseline = 'middle'
  ctx.fillText(initials, avX + avSize / 2, avY + avSize / 2 + 2)
  ctx.textAlign = 'left'
  ctx.textBaseline = 'alphabetic'

  // Text info
  const textX = avX + avSize + 22
  let curY = idBoxY + 50

  // Kicker removed per user request

  // Visitor Name
  curY += 40
  ctx.fillStyle = '#FFFFFF'
  ctx.font = 'bold 32px "Bricolage Grotesque", -apple-system, sans-serif'
  const vName = pass.visitorName.length > 20 ? pass.visitorName.slice(0, 19) + '…' : pass.visitorName
  ctx.fillText(vName, textX, curY)

  // Host line
  curY += 30
  ctx.fillStyle = 'rgba(255, 255, 255, 0.88)'
  ctx.font = '500 17px "DM Sans", -apple-system, sans-serif'
  const hostLabel = `Host: ${pass.hostName || 'Resident'} · ${pass.hostUnit}`
  const trimmedHost = hostLabel.length > 34 ? hostLabel.slice(0, 33) + '…' : hostLabel
  ctx.fillText(trimmedHost, textX, curY)

  // Pass ref removed per user request
  ctx.restore()

  // ══ 3. BODY (Seamless White Background) ════════════════════════════════════
  const bodyY = cardY + headerHeight
  const bodyH = cardH - headerHeight

  ctx.fillStyle = '#FFFFFF'
  ctx.fillRect(cardX, bodyY, cardW, bodyH)

  // Subtle Center Emblem Watermark in Body
  ctx.save()
  ctx.translate(cardX + cardW / 2 - 120, bodyY + 120)
  ctx.scale(2.2, 2.2)
  ctx.fillStyle = 'rgba(10, 92, 56, 0.04)'
  drawCheckpassEmblem(ctx)
  ctx.restore()

  // ══ 4. STRUCTURED DETAILS GRID ═════════════════════════════════════════════
  const gridX = cardX + 32
  const gridY = bodyY + 36
  const gridW = cardW - 64
  const gridH = pass.vehiclePlate ? 220 : 160

  ctx.fillStyle = '#F8FAF9'
  drawRoundedRect(ctx, gridX, gridY, gridW, gridH, 20)
  ctx.fill()
  ctx.strokeStyle = '#E8EFEA'
  ctx.lineWidth = 1.5
  ctx.stroke()

  // 2-column info layout
  const col1X = gridX + 24
  const col2X = gridX + gridW / 2 + 12

  // Row 1: Destination & Host
  renderCell(ctx, '📍 DESTINATION', pass.hostUnit, col1X, gridY + 34, options.estateName || 'Estate Residence')
  renderCell(ctx, '🏠 HOST', pass.hostName, col2X, gridY + 34, 'Authorized Resident')

  // Divider line
  ctx.beginPath()
  ctx.moveTo(gridX + 16, gridY + 84)
  ctx.lineTo(gridX + gridW - 16, gridY + 84)
  ctx.strokeStyle = '#EBF1ED'
  ctx.lineWidth = 1
  ctx.stroke()

  // Row 2: Purpose & Expiry
  const expiryFormatted = formatPassExpiry(pass.expiresAt, pass.type, pass.recurringDays)
  renderCell(ctx, '🎯 PURPOSE', pass.purpose || 'Personal Visit', col1X, gridY + 118, pass.type)
  renderCell(ctx, '⏰ EXPIRES', expiryFormatted, col2X, gridY + 118, pass.type === 'One-time' ? 'Single Entry' : 'Multi Entry')

  // Row 3: Vehicle plate (if provided)
  if (pass.vehiclePlate) {
    ctx.beginPath()
    ctx.moveTo(gridX + 16, gridY + 158)
    ctx.lineTo(gridX + gridW - 16, gridY + 158)
    ctx.strokeStyle = '#EBF1ED'
    ctx.lineWidth = 1
    ctx.stroke()

    renderCell(ctx, '🚗 VEHICLE PLATE', pass.vehiclePlate.toUpperCase(), col1X, gridY + 192, 'Clearance Registered')
  }

  // ══ 5. CENTERED CLEAN QR POD (No gate barrier text) ════════════════════════
  const qrPodY = gridY + gridH + 34
  const qrPodSize = 250
  const qrPodX = cardX + (cardW - qrPodSize) / 2

  // QR Container Box
  ctx.save()
  ctx.shadowColor = 'rgba(0, 0, 0, 0.05)'
  ctx.shadowBlur = 16
  ctx.shadowOffsetY = 6
  ctx.fillStyle = '#FFFFFF'
  drawRoundedRect(ctx, qrPodX, qrPodY, qrPodSize, qrPodSize, 22)
  ctx.fill()
  ctx.strokeStyle = '#DDE7E1'
  ctx.lineWidth = 1.5
  ctx.stroke()
  ctx.restore()

  // Draw QR Image inside
  const qrInnerPad = 18
  ctx.drawImage(
    qrImg,
    qrPodX + qrInnerPad,
    qrPodY + qrInnerPad,
    qrPodSize - qrInnerPad * 2,
    qrPodSize - qrInnerPad * 2
  )

  // ══ 6. CLEAN ATTRIBUTION FOOTER ════════════════════════════════════════════
  const footerY = cardY + cardH - 42
  ctx.save()
  ctx.beginPath()
  ctx.moveTo(cardX + 32, footerY - 24)
  ctx.lineTo(cardX + cardW - 32, footerY - 24)
  ctx.strokeStyle = '#EBF1ED'
  ctx.lineWidth = 1
  ctx.stroke()

  ctx.fillStyle = '#66756C'
  ctx.font = '500 14px "DM Sans", -apple-system, sans-serif'
  ctx.textAlign = 'center'
  ctx.fillText('Powered by Sheffy Technologies · checkpass.ng', cardX + cardW / 2, footerY)
  ctx.restore()

  // Restore clip
  ctx.restore()

  return canvas.toDataURL('image/png')
}

function renderCell(
  ctx: CanvasRenderingContext2D,
  label: string,
  value: string,
  x: number,
  y: number,
  sub?: string
) {
  ctx.fillStyle = '#66756C'
  ctx.font = 'bold 11px "DM Sans", -apple-system, sans-serif'
  ctx.fillText(label, x, y)

  ctx.fillStyle = '#0E291C'
  ctx.font = 'bold 17px "Bricolage Grotesque", -apple-system, sans-serif'
  const displayVal = value.length > 20 ? value.slice(0, 19) + '…' : value
  ctx.fillText(displayVal, x, y + 22)

  if (sub) {
    ctx.fillStyle = '#8FA096'
    ctx.font = '500 12px "DM Sans", -apple-system, sans-serif'
    ctx.fillText(sub, x, y + 40)
  }
}

/**
 * Draws the official 4-quadrant Checkpass emblem geometry in local 100x100 space
 */
function drawCheckpassEmblem(ctx: CanvasRenderingContext2D) {
  // Path corresponding to SVG: viewBox="0 0 119 119"
  const p = new Path2D(
    'M21.6807 96.6416H55.5674V118.174H42.3701C30.6745 118.174 21.1566 108.849 20.8457 97.2285C9.34033 96.8386 0.108192 87.4159 0.0576172 75.8047L0 62.6084L21.5322 62.5146L21.6807 96.6416ZM118.079 62.6084L118.021 75.8047C117.971 87.4163 108.738 96.8391 97.2324 97.2285C96.9215 108.849 87.4046 118.174 75.709 118.174H62.5127V96.6416H96.3984L96.5469 62.5146L118.079 62.6084ZM55.5674 21.5322H21.6807L21.5322 55.6592L0 55.5654L0.0576172 42.3691C0.108192 30.7579 9.34032 21.3343 20.8457 20.9443C21.1571 9.32425 30.6748 0 42.3701 0H55.5674V21.5322ZM75.71 0C87.405 0.000315153 96.923 9.32446 97.2344 20.9443C108.739 21.3347 117.971 30.7582 118.021 42.3691L118.079 55.5654L96.5479 55.6592L96.3994 21.5322H62.5127V0H75.71Z'
  )
  ctx.fill(p)
}

