<template>
  <ion-page>
    <ion-header class="ion-no-border">
      <ion-toolbar>
        <ion-buttons slot="start">
          <ion-back-button default-href="/tabs/home" text="" />
        </ion-buttons>
        <ion-title>My Passes</ion-title>
      </ion-toolbar>
      <!-- Integrated Search & Sort Container -->
      <div class="search-sort-bar">
        <div class="search-sort-container">
          <svg class="search-icon" width="18" height="18" viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="2.2" stroke-linecap="round" stroke-linejoin="round">
            <circle cx="11" cy="11" r="8" />
            <line x1="21" y1="21" x2="16.65" y2="16.65" />
          </svg>
          <input
            v-model="search"
            type="text"
            class="search-input"
            placeholder="Search by visitor name…"
          />
          <button
            v-if="search"
            type="button"
            class="search-clear-btn"
            @click="search = ''"
            aria-label="Clear search"
          >
            ✕
          </button>

          <!-- Dropdown inside search container (no border stroke, occupies space, clickable to filter) -->
          <div class="sort-dropdown-wrap">
            <select v-model="sort" class="sort-select-native" aria-label="Sort passes">
              <option value="newest">Newest</option>
              <option value="oldest">Oldest</option>
              <option value="name">A–Z</option>
            </select>
            <span class="sort-display-text">{{ sortLabel }}</span>
            <svg class="sort-chevron" width="13" height="13" viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="2.4" stroke-linecap="round" stroke-linejoin="round">
              <polyline points="6 9 12 15 18 9" />
            </svg>
          </div>
        </div>
      </div>
      <!-- Filter tabs -->
      <div class="filter-tabs">
        <button
          v-for="f in FILTERS" :key="f.value"
          class="filter-tab" :class="{ active: activeFilter === f.value }"
          @click="activeFilter = f.value"
        >{{ f.label }}</button>
      </div>
    </ion-header>

    <ion-content :fullscreen="true" class="passes-content">
      <ion-refresher slot="fixed" @ionRefresh="onRefresh">
        <ion-refresher-content />
      </ion-refresher>

      <div class="content-pad">

        <!-- Loading -->
        <SkeletonList v-if="store.loading" :rows="4" />

        <!-- Error -->
        <ErrorState v-else-if="store.error" :message="store.error" @retry="store.fetchAll()" />

        <!-- Empty state -->
        <EmptyState
          v-else-if="filteredPasses.length === 0"
          :icon="emptyIcon"
          :heading="emptyHeading"
          :body="emptyBody"
          :cta-label="activeFilter === 'all' && !search ? 'Create a pass' : undefined"
          @cta="router.push('/tabs/create')"
        />

        <!-- Pass list -->
        <div v-else class="pass-list">
          <PassCard
            v-for="pass in filteredPasses"
            :key="pass.id"
            :pass="pass"
            @click="router.push(`/pass/${pass.id}`)"
          />
        </div>

      </div>
    </ion-content>
  </ion-page>
</template>

<script setup lang="ts">
import { ref, computed, onMounted } from 'vue'
import { useRouter } from 'vue-router'
import {
  IonPage, IonHeader, IonToolbar, IonTitle, IonButtons, IonBackButton,
  IonContent, IonRefresher, IonRefresherContent
} from '@ionic/vue'
import { usePassesStore } from '@/stores/passes'
import PassCard from '@/components/PassCard.vue'
import SkeletonList from '@/components/SkeletonList.vue'
import EmptyState from '@/components/EmptyState.vue'
import ErrorState from '@/components/ErrorState.vue'

const router = useRouter()
const store  = usePassesStore()

const search      = ref('')
const sort        = ref<'newest' | 'oldest' | 'name'>('newest')
const activeFilter= ref('all')

const sortLabel = computed(() => {
  if (sort.value === 'newest') return 'Newest'
  if (sort.value === 'oldest') return 'Oldest'
  if (sort.value === 'name') return 'A–Z'
  return 'Newest'
})

const FILTERS = [
  { label: 'All',          value: 'all' },
  { label: 'On-site',      value: 'On-site' },
  { label: 'Pending',      value: 'Pending' },
  { label: 'Exited',       value: 'Exited' },
  // { label: 'Item Flagged', value: 'Item Flagged' },
]

const filteredPasses = computed(() => {
  let list = [...store.passes]

  // Filter by status
  if (activeFilter.value !== 'all') {
    list = list.filter(p => store.displayStatus(p) === activeFilter.value)
  }

  // Search
  if (search.value.trim()) {
    const q = search.value.toLowerCase()
    list = list.filter(p => p.visitorName.toLowerCase().includes(q))
  }

  // Sort
  if (sort.value === 'newest') list.sort((a, b) => new Date(b.createdAt).getTime() - new Date(a.createdAt).getTime())
  if (sort.value === 'oldest') list.sort((a, b) => new Date(a.createdAt).getTime() - new Date(b.createdAt).getTime())
  if (sort.value === 'name')   list.sort((a, b) => a.visitorName.localeCompare(b.visitorName))

  return list
})

const EMPTY_STATES: Record<string, { icon: string; heading: string; body: string }> = {
  all:          { icon: '🎟️', heading: 'No passes yet',       body: 'Create your first visitor pass and it will appear here.' },
  'On-site':    { icon: '👤', heading: 'No visitors on-site', body: 'Nobody has checked in today.' },
  Pending:      { icon: '⏳', heading: 'No pending passes',   body: "Passes you've created that haven't been used yet will show here." },
  Exited:       { icon: '🚪', heading: 'No exits yet',        body: 'Passes used and exited will appear here.' },
  'Item Flagged':{ icon:'📦', heading: 'No item declarations', body: "When you declare items on a pass exit, they'll appear here." },
  search:       { icon: '🔍', heading: `No results`,          body: 'Try a different name or clear the search.' },
}

const emptyKey     = computed(() => search.value.trim() ? 'search' : activeFilter.value)
const emptyIcon    = computed(() => EMPTY_STATES[emptyKey.value]?.icon || '🎟️')
const emptyHeading = computed(() => search.value.trim() ? `No results for "${search.value}"` : EMPTY_STATES[emptyKey.value]?.heading || '')
const emptyBody    = computed(() => EMPTY_STATES[emptyKey.value]?.body || '')

async function onRefresh(e: CustomEvent) {
  await store.fetchAll()
  ;(e.target as HTMLIonRefresherElement).complete()
}

onMounted(() => store.fetchAll())
</script>

<style scoped>
.passes-content { --background: var(--w-bg); }
.search-sort-bar {
  padding: 4px 16px 10px;
  background: var(--w-bg);
}
.search-sort-container {
  display: flex;
  align-items: center;
  background: #FFFFFF;
  border-radius: 12px;
  border: 1px solid rgba(0, 0, 0, 0.08);
  height: 44px;
  padding: 0 12px;
}
.search-icon {
  color: #8E8E93;
  margin-right: 8px;
  flex-shrink: 0;
}
.search-input {
  flex: 1;
  min-width: 0;
  border: none;
  background: transparent;
  outline: none;
  font-size: 14px;
  color: var(--w-text);
  font-family: inherit;
}
.search-input::placeholder {
  color: #8E8E93;
}
.search-clear-btn {
  border: none;
  background: #E5E5EA;
  color: #666;
  width: 18px;
  height: 18px;
  border-radius: 50%;
  font-size: 10px;
  display: flex;
  align-items: center;
  justify-content: center;
  cursor: pointer;
  margin-right: 8px;
  flex-shrink: 0;
}
.sort-dropdown-wrap {
  position: relative;
  display: flex;
  align-items: center;
  gap: 3px;
  padding-left: 10px;
  border-left: 1px solid rgba(0, 0, 0, 0.08);
  cursor: pointer;
  flex-shrink: 0;
}
.sort-select-native {
  position: absolute;
  inset: 0;
  width: 100%;
  height: 100%;
  opacity: 0;
  cursor: pointer;
  z-index: 2;
  -webkit-appearance: none;
}
.sort-display-text {
  font-size: 13px;
  font-weight: 500;
  color: var(--w-text);
  white-space: nowrap;
  pointer-events: none;
}
.sort-chevron {
  color: #666666;
  pointer-events: none;
  flex-shrink: 0;
}
.filter-tabs { display: flex; overflow-x: auto; gap: 6px; padding: 8px 16px; border-bottom: 1px solid var(--w-border); scrollbar-width: none; }
.filter-tabs::-webkit-scrollbar { display: none; }
.filter-tab {
  padding: 7px 14px; border-radius: 20px; white-space: nowrap;
  border: 1.5px solid var(--w-border); background: var(--w-surface);
  font-size: 13px; font-weight: 500; color: var(--w-muted); cursor: pointer;
  transition: all 0.15s; font-family: var(--w-font-body);
}
.filter-tab.active { background: var(--w-primary); border-color: var(--w-primary); color: white; font-weight: 600; }
.content-pad { padding: 16px 16px 100px; }
.pass-list   { display: flex; flex-direction: column; gap: 10px; }
</style>
