<!-- src/components/AccessPhraseCard.vue -->
<!-- Resident's daily gate phrase for visitors without a smartphone -->
<template>
  <div class="phrase-card">
    <div class="phrase-label">Today's gate phrase</div>
    <div class="phrase-sub">For visitors without a smartphone · changes at midnight</div>

    <div v-if="loading" class="phrase-value phrase-muted">Loading…</div>
    <div v-else-if="error" class="phrase-error">
      Couldn't load your phrase.
      <button class="link-btn" @click="load">Retry</button>
    </div>
    <template v-else-if="accessPhrase">
      <div class="phrase-value" :class="{ locked: accessPhrase.locked }">{{ accessPhrase.phrase }}</div>
      <div v-if="accessPhrase.locked" class="phrase-warning">
        Locked after too many wrong attempts at the gate. Generate a new phrase.
      </div>

      <div class="phrase-actions">
        <a class="phrase-btn" :href="smsHref">💬 SMS</a>
        <button class="phrase-btn" @click="copy">📋 Copy</button>
        <button class="phrase-btn" :disabled="regenerating" @click="confirmRegenerate">
          <ion-spinner v-if="regenerating" name="crescent" style="width:16px;height:16px" />
          <template v-else>🔄 New</template>
        </button>
      </div>
    </template>
  </div>
</template>

<script setup lang="ts">
import { ref, computed, onMounted } from 'vue'
import { IonSpinner, alertController } from '@ionic/vue'
import client from '@/api/client'
import { useToast } from '@/composables/useToast'
import type { AccessPhrase } from '@/types'

const { showToast } = useToast()

const accessPhrase = ref<AccessPhrase | null>(null)
const loading      = ref(true)
const error        = ref(false)
const regenerating = ref(false)

const shareMessage = computed(() => {
  if (!accessPhrase.value) return ''
  return `Gate access to ${accessPhrase.value.flatAddress} today: tell security the phrase "${accessPhrase.value.phrase}". Valid until midnight.`
})

// `sms:?&body=` is accepted by both Android and iOS
const smsHref = computed(() => `sms:?&body=${encodeURIComponent(shareMessage.value)}`)

async function load() {
  loading.value = true
  error.value   = false
  try {
    const res = await client.get('/v1/access-phrase')
    accessPhrase.value = res.data.accessPhrase
  } catch {
    error.value = true
  } finally {
    loading.value = false
  }
}

async function copy() {
  try {
    await navigator.clipboard.writeText(shareMessage.value)
    showToast('Copied — paste it into a message')
  } catch {
    showToast('Could not copy. Use SMS instead.', 'warning')
  }
}

async function confirmRegenerate() {
  const alert = await alertController.create({
    header: 'Generate a new phrase?',
    message: 'The current phrase will stop working. Anyone you already gave it to will need the new one.',
    buttons: [
      { text: 'Cancel', role: 'cancel' },
      { text: 'Generate', handler: regenerate },
    ],
  })
  await alert.present()
}

async function regenerate() {
  regenerating.value = true
  try {
    const res = await client.post('/v1/access-phrase/regenerate')
    accessPhrase.value = res.data.accessPhrase
    showToast('New phrase generated')
  } catch {
    showToast('Failed to generate a new phrase. Try again.', 'error')
  } finally {
    regenerating.value = false
  }
}

onMounted(load)
</script>

<style scoped>
.phrase-card {
  background: var(--w-surface);
  border-radius: var(--w-radius-lg);
  padding: 16px;
  box-shadow: var(--w-shadow-sm);
}
.phrase-label { font-size: 15px; font-weight: 700; color: var(--w-text); }
.phrase-sub   { font-size: 12px; color: var(--w-muted); margin-top: 2px; }
.phrase-value {
  margin: 14px 0 12px;
  padding: 14px;
  border-radius: var(--w-radius-md);
  background: var(--w-primary-light);
  color: var(--w-primary);
  font-family: var(--w-font-display);
  font-size: 22px;
  font-weight: 800;
  letter-spacing: 1px;
  text-align: center;
  word-break: break-word;
}
.phrase-value.locked { background: var(--w-danger-light); color: var(--w-danger); text-decoration: line-through; }
.phrase-muted { background: var(--w-bg); color: var(--w-muted); font-size: 15px; }
.phrase-warning { font-size: 12px; color: var(--w-danger); margin: -4px 0 12px; }
.phrase-error { font-size: 13px; color: var(--w-muted); margin-top: 12px; }
.link-btn { background: none; border: none; color: var(--w-primary); font-weight: 600; cursor: pointer; padding: 0 4px; }
.phrase-actions { display: grid; grid-template-columns: repeat(3, 1fr); gap: 8px; }
.phrase-btn {
  display: flex; align-items: center; justify-content: center; gap: 4px;
  padding: 10px 8px;
  border: 1px solid var(--w-border);
  border-radius: var(--w-radius-md);
  background: var(--w-surface);
  color: var(--w-text);
  font-size: 13px; font-weight: 600;
  text-decoration: none;
  cursor: pointer;
}
.phrase-btn:disabled { opacity: 0.55; cursor: not-allowed; }
</style>
