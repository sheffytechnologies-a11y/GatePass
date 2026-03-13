<template>
  <span class="status-badge" :class="`status-${cssClass}`">
    <span v-if="showDot" class="dot" />
    {{ label }}
  </span>
</template>

<script setup lang="ts">
import { computed } from 'vue'
import type { PassStatus } from '@/types'

const props = defineProps<{ status: PassStatus }>()

const label = computed(() => props.status)

const cssClass = computed(() => ({
  'On-site':      'onsite',
  'Pending':      'pending',
  'Item Flagged': 'flagged',
  'Exited':       'exited',
  'Revoked':      'revoked',
  'Expired':      'revoked',
}[props.status] ?? 'pending'))

const showDot = computed(() => props.status === 'On-site' || props.status === 'Item Flagged')
</script>

<style scoped>
.status-badge {
  display: inline-flex;
  align-items: center;
  gap: 5px;
  padding: 3px 10px;
  border-radius: 20px;
  font-size: 12px;
  font-weight: 600;
  font-family: var(--w-font-body);
  white-space: nowrap;
}
.dot {
  width: 6px;
  height: 6px;
  border-radius: 50%;
  display: inline-block;
}

/* On-site */
.status-onsite  { background: var(--w-primary-light); color: var(--w-primary); }
.status-onsite .dot { background: var(--w-primary); animation: pulse-dot 2s ease-in-out infinite; }

/* Pending */
.status-pending { background: var(--w-info-light); color: var(--w-info); }

/* Item Flagged */
.status-flagged { background: var(--w-warning-light); color: var(--w-warning); }
.status-flagged .dot { background: var(--w-warning); animation: pulse-dot 1.5s ease-in-out infinite; }

/* Exited */
.status-exited  { background: #F5F5F5; color: var(--w-muted); }

/* Revoked / Expired */
.status-revoked { background: var(--w-danger-light); color: var(--w-danger); }
</style>
