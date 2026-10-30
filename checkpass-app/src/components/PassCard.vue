<template>
  <div class="pass-card" @click="$emit('click')">
    <!-- Avatar -->
    <div class="avatar" :style="{ background: avatarGradient }">
      {{ initials }}
    </div>

    <!-- Info -->
    <div class="info">
      <div class="name">{{ pass.visitorName }}</div>
      <div class="meta">{{ pass.purpose }}
        <span v-if="pass.type === 'Recurring'" class="recurring-chip">Recurring</span>
      </div>
      <div class="time">{{ timeLabel }}</div>
    </div>

    <!-- Status + chevron -->
    <div class="right">
      <StatusBadge :status="displayStatus" />
      <ion-icon :icon="chevronForwardOutline" class="chevron" />
    </div>
  </div>
</template>

<script setup lang="ts">
import { computed } from 'vue'
import { IonIcon } from '@ionic/vue'
import { chevronForwardOutline } from 'ionicons/icons'
import StatusBadge from './StatusBadge.vue'
import type { Pass } from '@/types'

const props = defineProps<{ pass: Pass }>()
defineEmits(['click'])

const displayStatus = computed(() =>
  props.pass.status === 'On-site' && props.pass.itemsFlagged ? 'Item Flagged' : props.pass.status
)

const initials = computed(() =>
  props.pass.visitorName.split(' ').map(w => w[0]).slice(0, 2).join('').toUpperCase()
)

const GRADIENTS = [
  'linear-gradient(135deg,#0A5C38,#00C97A)',
  'linear-gradient(135deg,#1A3F8F,#4A90D9)',
  'linear-gradient(135deg,#7C3AED,#C084FC)',
  'linear-gradient(135deg,#D97706,#FBBF24)',
  'linear-gradient(135deg,#DC2626,#F87171)',
]
const avatarGradient = computed(() => {
  const idx = props.pass.visitorName.charCodeAt(0) % GRADIENTS.length
  return GRADIENTS[idx]
})

const timeLabel = computed(() => {
  const s = props.pass.status
  if (s === 'On-site') {
    return `Arrived ${fmtTime(props.pass.arrivedAt)}`
  }
  if (s === 'Exited') return `Exited ${fmtTime(props.pass.exitedAt)}`
  if (props.pass.type === 'Recurring' && props.pass.recurringDays) {
    return props.pass.recurringDays.join(', ')
  }
  return `Expires ${fmtTime(props.pass.expiresAt)}`
})

function fmtTime(iso: string | null) {
  if (!iso) return ''
  return new Date(iso).toLocaleTimeString('en-NG', { hour: '2-digit', minute: '2-digit', hour12: true })
}
</script>

<style scoped>
.pass-card {
  display: flex;
  align-items: center;
  gap: 12px;
  background: var(--w-surface);
  border-radius: var(--w-radius-md);
  padding: 14px 16px;
  box-shadow: var(--w-shadow-sm);
  cursor: pointer;
  transition: transform 0.15s ease;
}
.pass-card:active { transform: scale(0.98); }
.avatar {
  width: 44px; height: 44px;
  border-radius: 50%;
  display: flex; align-items: center; justify-content: center;
  color: white; font-weight: 700; font-size: 15px;
  flex-shrink: 0;
}
.info { flex: 1; min-width: 0; }
.name { font-weight: 600; font-size: 15px; color: var(--w-text); white-space: nowrap; overflow: hidden; text-overflow: ellipsis; }
.meta { font-size: 13px; color: var(--w-muted); margin-top: 2px; display: flex; align-items: center; gap: 6px; }
.time { font-size: 12px; color: var(--w-muted); margin-top: 3px; }
.right { display: flex; flex-direction: column; align-items: flex-end; gap: 6px; flex-shrink: 0; }
.chevron { font-size: 16px; color: var(--w-muted); }
.recurring-chip {
  background: var(--w-info-light);
  color: var(--w-info);
  font-size: 11px;
  font-weight: 600;
  padding: 2px 6px;
  border-radius: 10px;
}
</style>
