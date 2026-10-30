<template>
  <div class="state-select-wrap" ref="wrapRef">
    <div class="input-with-icon">
      <input
        type="text"
        class="form-input state-input"
        :placeholder="placeholder || 'Select or type state'"
        :value="modelValue"
        @input="onInput"
        @focus="isOpen = true"
        @keydown.down.prevent="onArrowDown"
        @keydown.up.prevent="onArrowUp"
        @keydown.enter.prevent="selectHighlighted"
        @keydown.esc="isOpen = false"
        autocomplete="off"
        spellcheck="false"
      />
      <span class="chevron" @click="toggleDropdown">
        <svg width="14" height="14" viewBox="0 0 20 20" fill="none">
          <path d="M5 7.5L10 12.5L15 7.5" stroke="currentColor" stroke-width="2" stroke-linecap="round" stroke-linejoin="round"/>
        </svg>
      </span>
    </div>

    <transition name="dropdown-fade">
      <ul v-if="isOpen && filteredStates.length > 0" class="states-dropdown">
        <li
          v-for="(st, idx) in filteredStates"
          :key="st"
          class="state-item"
          :class="{ 'state-item--active': idx === highlightedIdx, 'state-item--selected': st === modelValue }"
          @mousedown.prevent="selectState(st)"
        >
          <span>{{ st }}</span>
          <span v-if="st === modelValue" class="check">&check;</span>
        </li>
      </ul>
    </transition>
  </div>
</template>

<script setup lang="ts">
import { ref, computed, onMounted, onUnmounted } from 'vue'
import { NIGERIAN_STATES } from '@/data/nigerianStates'

const props = defineProps<{
  modelValue: string
  placeholder?: string
}>()

const emit = defineEmits<{
  (e: 'update:modelValue', val: string): void
}>()

const isOpen = ref(false)
const wrapRef = ref<HTMLElement | null>(null)
const highlightedIdx = ref(0)

const filteredStates = computed(() => {
  const query = (props.modelValue || '').trim().toLowerCase()
  if (!query) return NIGERIAN_STATES
  return NIGERIAN_STATES.filter(s => s.toLowerCase().includes(query))
})

function onInput(e: Event) {
  const val = (e.target as HTMLInputElement).value
  emit('update:modelValue', val)
  isOpen.value = true
  highlightedIdx.value = 0
}

function selectState(state: string) {
  emit('update:modelValue', state)
  isOpen.value = false
}

function toggleDropdown() {
  isOpen.value = !isOpen.value
}

function onArrowDown() {
  if (!isOpen.value) {
    isOpen.value = true
    return
  }
  if (highlightedIdx.value < filteredStates.value.length - 1) {
    highlightedIdx.value++
  }
}

function onArrowUp() {
  if (highlightedIdx.value > 0) {
    highlightedIdx.value--
  }
}

function selectHighlighted() {
  if (filteredStates.value[highlightedIdx.value]) {
    selectState(filteredStates.value[highlightedIdx.value])
  }
}

function handleClickOutside(e: MouseEvent) {
  if (wrapRef.value && !wrapRef.value.contains(e.target as Node)) {
    isOpen.value = false
  }
}

onMounted(() => {
  document.addEventListener('click', handleClickOutside)
})

onUnmounted(() => {
  document.removeEventListener('click', handleClickOutside)
})
</script>

<style scoped>
.state-select-wrap {
  position: relative;
  width: 100%;
}

.input-with-icon {
  position: relative;
  display: flex;
  align-items: center;
}

.state-input {
  width: 100%;
  padding-right: 36px;
}

.chevron {
  position: absolute;
  right: 12px;
  display: flex;
  align-items: center;
  justify-content: center;
  color: var(--c-muted);
  cursor: pointer;
  user-select: none;
  transition: transform 0.2s ease;
}

.chevron:hover {
  color: var(--c-primary, #0a5c38);
}

.states-dropdown {
  position: absolute;
  top: calc(100% + 4px);
  left: 0;
  right: 0;
  z-index: 1000;
  max-height: 220px;
  overflow-y: auto;
  background: var(--c-surface, #ffffff);
  border: 1px solid var(--c-border, #e2e8f0);
  border-radius: 12px;
  box-shadow: 0 10px 25px -5px rgba(0, 0, 0, 0.1), 0 8px 10px -6px rgba(0, 0, 0, 0.1);
  margin: 0;
  padding: 6px;
  list-style: none;
}

.state-item {
  display: flex;
  align-items: center;
  justify-content: space-between;
  padding: 9px 12px;
  border-radius: 8px;
  font-size: 14px;
  font-weight: 500;
  color: var(--c-text, #1e293b);
  cursor: pointer;
  transition: background 0.15s ease, color 0.15s ease;
}

.state-item:hover,
.state-item--active {
  background: rgba(10, 92, 56, 0.08);
  color: var(--c-primary, #0a5c38);
}

.state-item--selected {
  font-weight: 700;
  color: var(--c-primary, #0a5c38);
}

.check {
  font-size: 14px;
  color: var(--c-primary, #0a5c38);
  font-weight: bold;
}

.dropdown-fade-enter-active,
.dropdown-fade-leave-active {
  transition: opacity 0.18s ease, transform 0.18s ease;
}

.dropdown-fade-enter-from,
.dropdown-fade-leave-to {
  opacity: 0;
  transform: translateY(-6px);
}
</style>
