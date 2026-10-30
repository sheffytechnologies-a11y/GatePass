<!-- src/components/PhraseEntrySheet.vue -->
<!-- Security: admit a visitor without a smartphone using the unit's daily phrase -->
<template>
  <ion-modal
    :is-open="isOpen"
    @didDismiss="onDismiss"
    :initial-breakpoint="0.92"
    :breakpoints="[0, 0.92]"
  >
    <ion-page class="sheet-wrap">
      <div class="sheet-header">
        <div class="sheet-handle" />
        <div class="header-row">
          <div>
            <div class="sheet-title">Visitor Without Phone</div>
            <div class="sheet-sub">Ask which house they're visiting and today's phrase</div>
          </div>
          <button class="close-btn" @click="$emit('close')">✕</button>
        </div>
      </div>

      <ion-content class="sheet-content">
        <div class="sheet-body">
          <!-- Unit -->
          <div class="section-label">HOUSE / UNIT <span class="req">*</span></div>
          <div v-if="selectedUnit" class="unit-selected">
            <span>🏠 {{ selectedUnit.flatAddress }}</span>
            <button class="link-btn" @click="selectedUnit = null">Change</button>
          </div>
          <template v-else>
            <input
              v-model="unitSearch"
              class="field"
              placeholder="Search e.g. L3, H7"
              autocomplete="off"
            />
            <div v-if="unitsLoading" class="hint">Loading units…</div>
            <div v-else-if="unitsError" class="hint">
              Couldn't load units. <button class="link-btn" @click="loadUnits">Retry</button>
            </div>
            <div v-else class="unit-list">
              <button
                v-for="unit in filteredUnits"
                :key="unit.id"
                class="unit-row"
                @click="selectedUnit = unit"
              >
                {{ unit.flatAddress }}
              </button>
              <div v-if="filteredUnits.length === 0" class="hint">No matching units</div>
            </div>
          </template>

          <!-- Phrase -->
          <div class="section-label">ACCESS PHRASE <span class="req">*</span></div>
          <input
            v-model="phrase"
            class="field phrase-field"
            placeholder="e.g. AMBER LION 27"
            autocomplete="off"
            autocapitalize="characters"
            spellcheck="false"
          />

          <!-- Visitor -->
          <div class="section-label">VISITOR NAME <span class="req">*</span></div>
          <input v-model="visitorName" class="field" placeholder="Full name" />

          <div class="section-label">VISITOR PHONE</div>
          <input v-model="visitorPhone" class="field" type="tel" placeholder="Optional" />

          <div v-if="errorMessage" class="form-error">{{ errorMessage }}</div>

          <div class="submit-area">
            <button class="verify-btn" :disabled="!canSubmit || submitting" @click="submit">
              <ion-spinner v-if="submitting" name="crescent" color="light" style="width:20px;height:20px" />
              <template v-else>✅ Verify &amp; Allow Entry</template>
            </button>
            <div class="submit-caption">The residents will be notified</div>
          </div>
        </div>
      </ion-content>
    </ion-page>
  </ion-modal>
</template>

<script setup lang="ts">
import { ref, computed, watch } from 'vue'
import { IonModal, IonPage, IonContent, IonSpinner } from '@ionic/vue'
import client from '@/api/client'

type UnitOption = { id: string; flatAddress: string }

const props = defineProps<{ isOpen: boolean }>()
const emit  = defineEmits<{ close: []; admitted: [passId: string] }>()

const units        = ref<UnitOption[]>([])
const unitsLoading = ref(false)
const unitsError   = ref(false)
const unitSearch   = ref('')
const selectedUnit = ref<UnitOption | null>(null)
const phrase       = ref('')
const visitorName  = ref('')
const visitorPhone = ref('')
const submitting   = ref(false)
const errorMessage = ref('')

const filteredUnits = computed(() => {
  const q = unitSearch.value.trim().toLowerCase().replace(/[\s,]+/g, '')
  const list = q
    ? units.value.filter(u => u.flatAddress.toLowerCase().replace(/[\s,]+/g, '').includes(q))
    : units.value
  return list.slice(0, 8)
})

const canSubmit = computed(() =>
  !!selectedUnit.value && phrase.value.trim().length > 0 && visitorName.value.trim().length > 0
)

async function loadUnits() {
  unitsLoading.value = true
  unitsError.value   = false
  try {
    const res = await client.get('/v1/access-phrase/units')
    units.value = res.data.units || []
  } catch {
    unitsError.value = true
  } finally {
    unitsLoading.value = false
  }
}

watch(() => props.isOpen, (open) => { if (open && units.value.length === 0) loadUnits() }, { immediate: true })

async function submit() {
  if (!canSubmit.value || submitting.value) return
  submitting.value   = true
  errorMessage.value = ''
  try {
    const res = await client.post('/v1/access-phrase/verify', {
      unitId: Number(selectedUnit.value!.id),
      phrase: phrase.value.trim(),
      visitorName: visitorName.value.trim(),
      visitorPhone: visitorPhone.value.trim() || null,
    })
    emit('admitted', res.data.pass.id)
    reset()
  } catch (e: any) {
    const data = e?.response?.data
    if (data?.code === 'PHRASE_INVALID') {
      errorMessage.value = typeof data.attemptsRemaining === 'number'
        ? `Wrong phrase for this unit. ${data.attemptsRemaining} attempt(s) left today.`
        : 'Wrong phrase for this unit.'
    } else if (data?.code === 'PHRASE_LOCKED' || e?.response?.status === 429) {
      errorMessage.value = data?.message || 'Too many attempts. Please wait and try again.'
    } else {
      errorMessage.value = data?.message || 'Verification failed. Try again.'
    }
  } finally {
    submitting.value = false
  }
}

function reset() {
  unitSearch.value   = ''
  selectedUnit.value = null
  phrase.value       = ''
  visitorName.value  = ''
  visitorPhone.value = ''
  errorMessage.value = ''
}

function onDismiss() {
  reset()
  emit('close')
}
</script>

<style scoped>
.sheet-wrap {
  display: flex; flex-direction: column; height: 100%;
  background: var(--w-bg, #fff);
}
.sheet-content { --background: var(--w-bg, #fff); }

.sheet-header {
  padding: 10px 20px 0;
  border-bottom: 1px solid var(--w-border, #eee);
  background: var(--w-surface, #fff);
}
.sheet-handle {
  width: 36px; height: 4px; border-radius: 2px;
  background: var(--w-border, #ddd); margin: 0 auto 14px;
}
.header-row {
  display: flex; justify-content: space-between; align-items: flex-start;
  padding-bottom: 16px;
}
.sheet-title { font-size: 20px; font-weight: 700; color: var(--w-text, #111); }
.sheet-sub   { font-size: 13px; color: var(--w-muted, #888); margin-top: 3px; }
.close-btn {
  width: 32px; height: 32px; border-radius: 50%;
  background: var(--w-border, #eee); border: none;
  font-size: 14px; color: var(--w-muted, #666); cursor: pointer;
  display: flex; align-items: center; justify-content: center;
}

.sheet-body {
  padding: 20px 20px 40px;
  display: flex; flex-direction: column; gap: 10px;
}
.section-label {
  font-size: 11px; font-weight: 700; letter-spacing: 0.8px;
  color: var(--w-muted, #888); text-transform: uppercase; margin-top: 6px;
}
.req { color: #e55; }

.field {
  width: 100%; border: none; border-radius: 14px;
  padding: 14px 16px; font-size: 15px;
  background: var(--w-surface, #f5f5f5);
  color: var(--w-text, #111); font-family: var(--w-font-body, sans-serif);
  outline: none; box-sizing: border-box;
}
.field::placeholder { color: #bbb; }
.phrase-field { font-size: 18px; font-weight: 700; letter-spacing: 1px; text-transform: uppercase; }

.unit-list { display: flex; flex-direction: column; gap: 6px; }
.unit-row {
  width: 100%; text-align: left;
  padding: 12px 14px; border: 1px solid var(--w-border);
  border-radius: var(--w-radius-md); background: var(--w-surface);
  font-size: 14px; font-weight: 600; color: var(--w-text); cursor: pointer;
}
.unit-selected {
  display: flex; justify-content: space-between; align-items: center;
  padding: 14px 16px; border-radius: 14px;
  background: var(--w-primary-light); color: var(--w-primary);
  font-size: 15px; font-weight: 700;
}
.link-btn { background: none; border: none; color: var(--w-primary); font-weight: 600; cursor: pointer; padding: 0 4px; }
.hint { font-size: 13px; color: var(--w-muted); padding: 4px 2px; }

.form-error {
  background: var(--w-danger-light); border-radius: 10px;
  padding: 10px 14px; font-size: 13px; color: var(--w-danger);
}

.submit-area { display: flex; flex-direction: column; align-items: center; gap: 8px; margin-top: 8px; }
.verify-btn {
  width: 100%; padding: 17px;
  background: var(--w-primary); border: none; border-radius: 50px;
  font-size: 16px; font-weight: 700; color: #fff;
  cursor: pointer; transition: opacity 0.15s;
  display: flex; align-items: center; justify-content: center; gap: 8px;
}
.verify-btn:disabled { opacity: 0.55; cursor: not-allowed; }
.verify-btn:active:not(:disabled) { opacity: 0.85; }
.submit-caption { font-size: 12px; color: var(--w-muted, #888); }
</style>
