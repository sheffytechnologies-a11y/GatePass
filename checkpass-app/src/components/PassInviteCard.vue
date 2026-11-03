<!-- src/components/PassInviteCard.vue -->
<template>
  <div class="pass-invite-container">
    <div class="pass-ticket-1 seamless-mode">
      <!-- ══ HEADER ═════════════════════════════════════════════════ -->
      <div class="t1-header">
        <!-- Background Logo Zoom Watermark -->
        <div class="t1-bg-logo-watermark">
          <svg viewBox="0 0 119 119" fill="#FFFFFF">
            <path d="M21.6807 96.6416H55.5674V118.174H42.3701C30.6745 118.174 21.1566 108.849 20.8457 97.2285C9.34033 96.8386 0.108192 87.4159 0.0576172 75.8047L0 62.6084L21.5322 62.5146L21.6807 96.6416ZM118.079 62.6084L118.021 75.8047C117.971 87.4163 108.738 96.8391 97.2324 97.2285C96.9215 108.849 87.4046 118.174 75.709 118.174H62.5127V96.6416H96.3984L96.5469 62.5146L118.079 62.6084ZM55.5674 21.5322H21.6807L21.5322 55.6592L0 55.5654L0.0576172 42.3691C0.108192 30.7579 9.34032 21.3343 20.8457 20.9443C21.1571 9.32425 30.6748 0 42.3701 0H55.5674V21.5322ZM75.71 0C87.405 0.000315153 96.923 9.32446 97.2344 20.9443C108.739 21.3347 117.971 30.7582 118.021 42.3691L118.079 55.5654L96.5479 55.6592L96.3994 21.5322H62.5127V0H75.71Z" />
          </svg>
        </div>

        <div class="t1-top-row">
          <img src="/logo-dark.png" alt="Checkpass" class="t1-logo" />
          <div class="t1-badge">{{ badgeText }}</div>
        </div>

        <!-- Visitor Identity Frosted Pod (Visitor Name & Host Only - No Phone Number) -->
        <div class="t1-identity-card">
          <div class="t1-avatar-wrap" :style="{ background: avatarGradient }">
            <span class="t1-avatar-fallback">{{ initials }}</span>
          </div>
          <div class="t1-info">
            <span class="t1-visitor-kicker">VERIFIED VISITOR</span>
            <div class="t1-visitor-name">{{ pass.visitorName }}</div>
            <div class="t1-visitor-sub">Host: {{ pass.hostName }} · {{ pass.hostUnit }}</div>
          </div>
        </div>
      </div>

      <!-- ══ BODY ═══════════════════════════════════════════════════ -->
      <div class="t1-body">
        <!-- Subtle Center Logo Watermark in Body -->
        <div class="t1-body-watermark">
          <svg viewBox="0 0 119 119" fill="#0A5C38">
            <path d="M21.6807 96.6416H55.5674V118.174H42.3701C30.6745 118.174 21.1566 108.849 20.8457 97.2285C9.34033 96.8386 0.108192 87.4159 0.0576172 75.8047L0 62.6084L21.5322 62.5146L21.6807 96.6416ZM118.079 62.6084L118.021 75.8047C117.971 87.4163 108.738 96.8391 97.2324 97.2285C96.9215 108.849 87.4046 118.174 75.709 118.174H62.5127V96.6416H96.3984L96.5469 62.5146L118.079 62.6084ZM55.5674 21.5322H21.6807L21.5322 55.6592L0 55.5654L0.0576172 42.3691C0.108192 30.7579 9.34032 21.3343 20.8457 20.9443C21.1571 9.32425 30.6748 0 42.3701 0H55.5674V21.5322ZM75.71 0C87.405 0.000315153 96.923 9.32446 97.2344 20.9443C108.739 21.3347 117.971 30.7582 118.021 42.3691L118.079 55.5654L96.5479 55.6592L96.3994 21.5322H62.5127V0H75.71Z" />
          </svg>
        </div>

        <!-- Timestamps & Destination Details Grid -->
        <div class="t1-details-grid">
          <div class="t1-detail-item">
            <span class="t1-lbl">📍 DESTINATION</span>
            <span class="t1-val">{{ pass.hostUnit }}</span>
            <span class="t1-sub">{{ estateLabel }}</span>
          </div>
          <div class="t1-detail-item">
            <span class="t1-lbl">🏠 HOST</span>
            <span class="t1-val">{{ pass.hostName }}</span>
            <span class="t1-sub">Authorized Resident</span>
          </div>
          <div class="t1-detail-item">
            <span class="t1-lbl">🎯 PURPOSE</span>
            <span class="t1-val">{{ pass.purpose }}</span>
            <span class="t1-sub">{{ pass.type }}</span>
          </div>
          <div class="t1-detail-item">
            <span class="t1-lbl">⏰ EXPIRES</span>
            <span class="t1-val" style="color: #0A5C38;">{{ expiryDisplay }}</span>
            <span class="t1-sub">{{ pass.type === 'Recurring' ? 'Recurring Access' : 'Single Entry' }}</span>
          </div>
          <div v-if="pass.vehiclePlate" class="t1-detail-item full">
            <span class="t1-lbl">🚗 VEHICLE PLATE</span>
            <span class="t1-val">{{ pass.vehiclePlate }}</span>
          </div>
        </div>

        <!-- Standalone Clean QR Pod (No gate barrier clearance text) -->
        <div v-if="showQR" class="t1-qr-center-box">
          <div class="t1-qr-wrapper">
            <img v-if="qrDataUrl" :src="qrDataUrl" alt="Pass QR Code" class="t1-qr-img" />
          </div>
          <div class="t1-pass-ref">Pass #CP-{{ passShortId }}</div>
        </div>

        <!-- Clean Attribution Footer -->
        <div class="t1-footer">
          <span>Powered by</span>
          <img src="/sheffy-logo.svg" alt="Sheffy Technologies" class="sheffy-svg-inline" />
          <span>· checkpass.ng</span>
        </div>
      </div>
    </div>
  </div>
</template>

<script setup lang="ts">
import { ref, computed, watch } from 'vue'
import QRCode from 'qrcode'
import type { Pass } from '@/types'
import { generateInviteCardImage, formatPassExpiry } from '@/utils/inviteCardRenderer'

const props = withDefaults(
  defineProps<{
    pass: Pass
    showQR?: boolean
    estateName?: string
  }>(),
  {
    showQR: true,
  }
)

const qrDataUrl = ref('')

const initials = computed(() => {
  return (props.pass.visitorName || 'Guest')
    .split(' ')
    .filter(Boolean)
    .slice(0, 2)
    .map(w => w[0].toUpperCase())
    .join('')
})

const GRADIENTS = [
  'linear-gradient(135deg, #0A5C38 0%, #00C97A 100%)',
  'linear-gradient(135deg, #0C3B4A 0%, #17A2B8 100%)',
  'linear-gradient(135deg, #1A3F8F 0%, #4A90D9 100%)',
  'linear-gradient(135deg, #0A5C38 0%, #004422 100%)',
]

const avatarGradient = computed(() => {
  const code = props.pass.visitorName?.charCodeAt(0) || 0
  return GRADIENTS[code % GRADIENTS.length]
})

const passShortId = computed(() => (props.pass.id || '').slice(-6).toUpperCase())

const badgeText = computed(() => {
  if (props.pass.status === 'On-site') return '● On-site'
  if (props.pass.type === 'Recurring') return 'Recurring Pass'
  return 'Visitor Pass'
})

const estateLabel = computed(() => props.estateName || 'Estate Residence')

const expiryDisplay = computed(() => {
  return formatPassExpiry(props.pass.expiresAt, props.pass.type, props.pass.recurringDays)
})

watch(
  () => props.pass.qrData || props.pass.id,
  async (val) => {
    if (!val) {
      qrDataUrl.value = ''
      return
    }
    qrDataUrl.value = await QRCode.toDataURL(val, {
      width: 320,
      margin: 1,
      color: {
        dark: '#0A5C38',
        light: '#FFFFFF',
      },
    })
  },
  { immediate: true }
)

/**
 * Generates the full high-res PNG image of this invite card for sharing
 */
async function generateImage(): Promise<string> {
  return generateInviteCardImage(props.pass, { estateName: props.estateName })
}

defineExpose({
  generateImage,
  qrDataUrl,
})
</script>

<style scoped>
.pass-invite-container {
  width: 100%;
  max-width: 440px;
  margin: 0 auto;
}

.pass-ticket-1 {
  background: #FFFFFF;
  border-radius: 24px;
  overflow: hidden;
  box-shadow: 0 16px 36px -8px rgba(10, 92, 56, 0.16), 0 4px 12px rgba(0, 0, 0, 0.05);
  border: 1px solid #D5E2DA;
  position: relative;
  transition: transform 0.3s cubic-bezier(0.16, 1, 0.3, 1);
}

/* Header */
.t1-header {
  background: linear-gradient(145deg, #0A5C38 0%, #004422 100%);
  color: #FFFFFF;
  padding: 22px 20px 24px;
  position: relative;
  overflow: hidden;
}

.t1-bg-logo-watermark {
  position: absolute;
  top: -24px;
  right: -24px;
  width: 150px;
  height: 150px;
  pointer-events: none;
  z-index: 1;
  opacity: 0.1;
}

.t1-bg-logo-watermark svg {
  width: 100%;
  height: 100%;
}

.t1-top-row {
  display: flex;
  justify-content: space-between;
  align-items: center;
  margin-bottom: 16px;
  position: relative;
  z-index: 2;
}

.t1-logo {
  height: 22px;
  filter: brightness(0) invert(1);
}

.t1-badge {
  background: rgba(255, 255, 255, 0.18);
  backdrop-filter: blur(4px);
  padding: 4px 12px;
  border-radius: 999px;
  font-size: 11px;
  font-weight: 700;
  letter-spacing: 0.05em;
  text-transform: uppercase;
  color: #E2FDEE;
  border: 1px solid rgba(255, 255, 255, 0.2);
}

/* Identity Pod */
.t1-identity-card {
  display: flex;
  align-items: center;
  gap: 14px;
  background: rgba(0, 0, 0, 0.25);
  backdrop-filter: blur(8px);
  border: 1px solid rgba(255, 255, 255, 0.16);
  border-radius: 16px;
  padding: 12px 14px;
  position: relative;
  z-index: 2;
}

.t1-avatar-wrap {
  width: 52px;
  height: 52px;
  border-radius: 14px;
  border: 2px solid #00C97A;
  box-shadow: 0 4px 12px rgba(0, 0, 0, 0.25);
  flex-shrink: 0;
  display: flex;
  align-items: center;
  justify-content: center;
}

.t1-avatar-fallback {
  font-family: var(--w-font-display, 'Bricolage Grotesque', sans-serif);
  font-size: 20px;
  font-weight: 700;
  color: #FFFFFF;
}

.t1-info {
  display: flex;
  flex-direction: column;
  gap: 2px;
  min-width: 0;
}

.t1-visitor-kicker {
  font-size: 10px;
  font-weight: 800;
  letter-spacing: 0.08em;
  text-transform: uppercase;
  color: #79F3BA;
}

.t1-visitor-name {
  font-family: var(--w-font-display, 'Bricolage Grotesque', sans-serif);
  font-size: 18px;
  font-weight: 700;
  color: #FFFFFF;
  line-height: 1.2;
  white-space: nowrap;
  overflow: hidden;
  text-overflow: ellipsis;
}

.t1-visitor-sub {
  font-size: 12px;
  color: rgba(255, 255, 255, 0.88);
  font-weight: 500;
  white-space: nowrap;
  overflow: hidden;
  text-overflow: ellipsis;
}

/* Body */
.t1-body {
  padding: 18px 20px 20px;
  position: relative;
  overflow: hidden;
  background: #FFFFFF;
}

.t1-body-watermark {
  position: absolute;
  bottom: 70px;
  left: 50%;
  transform: translate(-50%, 0);
  width: 240px;
  height: 240px;
  pointer-events: none;
  z-index: 0;
  opacity: 0.05;
}

.t1-body-watermark svg {
  width: 100%;
  height: 100%;
}

/* Details Grid */
.t1-details-grid {
  display: grid;
  grid-template-columns: 1fr 1fr;
  gap: 10px;
  background: #F8FAF9;
  border: 1px solid #E8EFEA;
  border-radius: 14px;
  padding: 12px 14px;
  margin-bottom: 18px;
  position: relative;
  z-index: 1;
}

.t1-detail-item {
  display: flex;
  flex-direction: column;
  gap: 2px;
  min-width: 0;
}

.t1-detail-item.full {
  grid-column: span 2;
  border-top: 1px solid #EBF1ED;
  padding-top: 8px;
  margin-top: 2px;
}

.t1-lbl {
  font-size: 10px;
  font-weight: 700;
  letter-spacing: 0.05em;
  text-transform: uppercase;
  color: #66756C;
}

.t1-val {
  font-size: 13.5px;
  font-weight: 700;
  color: #1A2E23;
  white-space: nowrap;
  overflow: hidden;
  text-overflow: ellipsis;
}

.t1-sub {
  font-size: 11px;
  color: #8FA096;
  font-weight: 500;
}

/* Standalone Clean QR Pod */
.t1-qr-center-box {
  text-align: center;
  margin-bottom: 16px;
  position: relative;
  z-index: 1;
}

.t1-qr-wrapper {
  width: 154px;
  height: 154px;
  margin: 0 auto 8px;
  background: #FFFFFF;
  padding: 8px;
  border-radius: 16px;
  border: 1px solid #E0E8E2;
  box-shadow: 0 4px 12px rgba(0, 0, 0, 0.04);
  display: flex;
  align-items: center;
  justify-content: center;
}

.t1-qr-img {
  width: 100%;
  height: 100%;
  display: block;
  border-radius: 8px;
}

.t1-pass-ref {
  font-size: 12px;
  font-weight: 600;
  color: #66756C;
  letter-spacing: 0.04em;
}

/* Footer */
.t1-footer {
  border-top: 1px solid #EBF1ED;
  padding-top: 12px;
  text-align: center;
  font-size: 11px;
  color: #66756C;
  position: relative;
  z-index: 1;
  display: flex;
  align-items: center;
  justify-content: center;
  gap: 4px;
}

.sheffy-svg-inline {
  height: 13px;
  display: inline-block;
}
</style>
