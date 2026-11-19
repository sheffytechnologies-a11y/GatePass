<!-- src/components/EstateHero.vue -->
<template>
  <div class="hero-card">
    <div class="hero-top">
      <div>
        <div class="estate-name">{{ displayEstateName }}</div>
        <div class="address">{{ displayFlatAddress }}</div>
      </div>
      <span class="gate-badge" :class="gateOpen ? 'gate-open' : 'gate-closed'">
        <span class="gate-dot" />
        {{ gateOpen ? 'Gate Open' : 'Gate Closed' }}
      </span>
    </div>

    <div class="stats-row">
      <div class="stat">
        <div class="stat-value">{{ safeStats.onPremises }}</div>
        <div class="stat-label">On Premises</div>
      </div>
      <div class="divider" />
      <div class="stat">
        <div class="stat-value">{{ safeStats.activePasses }}</div>
        <div class="stat-label">Active Passes</div>
      </div>
      <div class="divider" />
      <div class="stat">
        <div class="stat-value">{{ safeStats.newToday }}</div>
        <div class="stat-label">New Today</div>
      </div>
    </div>
  </div>
</template>

<script setup lang="ts">
import { computed } from 'vue'

const props = withDefaults(defineProps<{
  flatAddress?: string
  estateName?: string
  gateOpen?: boolean
  stats?: { onPremises?: number; activePasses?: number; newToday?: number } | null
}>(), {
  flatAddress: '',
  estateName: '',
  gateOpen: true,
  stats: () => ({ onPremises: 0, activePasses: 0, newToday: 0 }),
})

const displayEstateName = computed(() => props.estateName || 'Sheffy Estate')
const displayFlatAddress = computed(() => props.flatAddress || 'VIP 2, VVIP 2, 1% 2')
const safeStats = computed(() => ({
  onPremises: typeof props.stats?.onPremises === 'number' ? props.stats.onPremises : 0,
  activePasses: typeof props.stats?.activePasses === 'number' ? props.stats.activePasses : 0,
  newToday: typeof props.stats?.newToday === 'number' ? props.stats.newToday : 0,
}))
</script>

<style scoped>
.hero-card {
  background: var(--w-primary, #0A5C38);
  border-radius: var(--w-radius-xl, 16px);
  padding: 14px 16px;
  color: white;
  margin-bottom: 4px;
}
.hero-top { display: flex; justify-content: space-between; align-items: flex-start; margin-bottom: 12px; }
.estate-name { font-size: 11.5px; opacity: 0.85; margin-bottom: 2px; font-weight: 500; }
.address { font-family: var(--w-font-display, inherit); font-size: 14px; font-weight: 700; letter-spacing: -0.1px; line-height: 1.25; }
.gate-badge {
  display: flex; align-items: center; gap: 4px;
  padding: 3px 8px; border-radius: 20px; font-size: 11px; font-weight: 600;
}
.gate-open  { background: rgba(0,201,122,0.2); color: #00C97A; }
.gate-closed{ background: rgba(255,255,255,0.15); color: rgba(255,255,255,0.8); }
.gate-dot   { width: 5px; height: 5px; border-radius: 50%; background: currentColor; }
.stats-row  { display: flex; align-items: center; }
.stat       { flex: 1; text-align: center; }
.stat-value { font-family: var(--w-font-display, inherit); font-size: 16px; font-weight: 700; }
.stat-label { font-size: 10px; opacity: 0.75; margin-top: 1px; }
.divider    { width: 1px; height: 24px; background: rgba(255,255,255,0.2); }
</style>
