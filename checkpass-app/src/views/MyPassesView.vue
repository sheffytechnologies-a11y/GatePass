<template>
  <ion-page>
    <ion-header class="ion-no-border">
      <ion-toolbar>
        <ion-title>My Passes</ion-title>
      </ion-toolbar>
      <!-- Search -->
      <ion-toolbar>
        <ion-searchbar
          v-model="search"
          placeholder="Search by visitor name…"
          :debounce="300"
          class="passes-search"
        />
        <div slot="end" class="sort-wrap">
          <select v-model="sort" class="sort-select">
            <option value="newest">Newest</option>
            <option value="oldest">Oldest</option>
            <option value="name">A–Z</option>
          </select>
        </div>
      </ion-toolbar>
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
import { IonPage, IonHeader, IonToolbar, IonTitle, IonContent, IonSearchbar, IonRefresher, IonRefresherContent } from '@ionic/vue'
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
.passes-search  { --background: var(--w-surface); }
.sort-wrap { padding-right: 12px; }
.sort-select {
  border: 1.5px solid var(--w-border); border-radius: var(--w-radius-sm);
  padding: 6px 10px; font-size: 13px; background: var(--w-surface); color: var(--w-text);
  font-family: var(--w-font-body); outline: none;
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
