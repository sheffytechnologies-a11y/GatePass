<!-- src/components/EstateHero.vue -->
<template>
  <div class="hero-card">
    <div class="hero-top">
      <div>
        <div class="estate-name">{{ estateName }}</div>
        <div class="address">{{ flatAddress }}</div>
      </div>
      <span class="gate-badge" :class="gateOpen ? 'gate-open' : 'gate-closed'">
        <span class="gate-dot" />
        {{ gateOpen ? 'Gate Open' : 'Gate Closed' }}
      </span>
    </div>

    <div class="stats-row">
      <div class="stat">
        <div class="stat-value">{{ stats.onPremises }}</div>
        <div class="stat-label">On Premises</div>
      </div>
      <div class="divider" />
      <div class="stat">
        <div class="stat-value">{{ stats.activePasses }}</div>
        <div class="stat-label">Active Passes</div>
      </div>
      <div class="divider" />
      <div class="stat">
        <div class="stat-value">{{ stats.newToday }}</div>
        <div class="stat-label">New Today</div>
      </div>
    </div>
  </div>
</template>

<script setup lang="ts">
defineProps<{
  flatAddress: string
  estateName: string
  gateOpen?: boolean
  stats: { onPremises: number; activePasses: number; newToday: number }
}>()
</script>

<style scoped>
.hero-card {
  background: var(--w-primary);
  border-radius: var(--w-radius-xl);
  padding: 20px;
  color: white;
}
.hero-top { display: flex; justify-content: space-between; align-items: flex-start; margin-bottom: 20px; }
.estate-name { font-size: 13px; opacity: 0.7; margin-bottom: 4px; }
.address { font-family: var(--w-font-display); font-size: 22px; font-weight: 700; }
.gate-badge {
  display: flex; align-items: center; gap: 5px;
  padding: 5px 10px; border-radius: 20px; font-size: 12px; font-weight: 600;
}
.gate-open  { background: rgba(0,201,122,0.2); color: #00C97A; }
.gate-closed{ background: rgba(255,255,255,0.15); color: rgba(255,255,255,0.8); }
.gate-dot   { width: 6px; height: 6px; border-radius: 50%; background: currentColor; }
.stats-row  { display: flex; align-items: center; }
.stat       { flex: 1; text-align: center; }
.stat-value { font-family: var(--w-font-display); font-size: 28px; font-weight: 700; }
.stat-label { font-size: 11px; opacity: 0.65; margin-top: 2px; }
.divider    { width: 1px; height: 40px; background: rgba(255,255,255,0.2); }
</style>
