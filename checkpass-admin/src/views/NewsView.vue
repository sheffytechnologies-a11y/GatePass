<template>
  <div class="mobile-page news-page">
    <!-- Hero Header -->
    <section class="hero-card hero-card--emerald">
      <div class="hero-content">
        <div class="hero-eyebrow">Estate Broadcasts &amp; Notices</div>
        <h1 class="hero-title">Publish Announcements &amp; Updates to Residents</h1>
        <p class="hero-copy">
          Official estate announcements, facility maintenance schedules, and security advisories are delivered directly to resident mobile app feeds.
        </p>
      </div>
      <button class="btn btn-gold hero-cta-btn" @click="openCreateModal">
        <svg width="18" height="18" viewBox="0 0 24 24" fill="none"><path d="M12 5v14M5 12h14" stroke="currentColor" stroke-width="2" stroke-linecap="round"/></svg>
        New Announcement
      </button>
    </section>

    <!-- Filters & Action Bar -->
    <section class="card section-card filter-card">
      <div class="filter-row">
        <div class="search-input-wrap">
          <svg class="search-icon" width="16" height="16" viewBox="0 0 24 24" fill="none"><circle cx="11" cy="11" r="8" stroke="currentColor" stroke-width="2"/><path d="M21 21l-4.35-4.35" stroke="currentColor" stroke-width="2" stroke-linecap="round"/></svg>
          <input
            v-model="searchQuery"
            type="search"
            placeholder="Search announcements by title or content..."
            class="search-input"
            @input="handleSearch"
          />
        </div>

        <div class="estate-filter-wrap" v-if="estates.length > 1">
          <select v-model="selectedEstateId" class="estate-select" @change="loadNews">
            <option :value="null">All Estates ({{ estates.length }})</option>
            <option v-for="e in estates" :key="e.id" :value="e.id">{{ e.name }}</option>
          </select>
        </div>
      </div>
    </section>

    <!-- Announcements List -->
    <section class="card section-card">
      <div class="section-head">
        <div>
          <h2 class="section-title">Published Announcements</h2>
          <p class="section-copy">{{ newsList.length }} broadcast{{ newsList.length === 1 ? '' : 's' }} active in resident feeds.</p>
        </div>
        <button class="btn btn-sm btn-outline refresh-btn" @click="loadNews" :disabled="loading">
          <svg width="14" height="14" viewBox="0 0 24 24" fill="none" :class="{ spinning: loading }"><path d="M23 4v6h-6M1 20v-6h6" stroke="currentColor" stroke-width="2" stroke-linecap="round" stroke-linejoin="round"/><path d="M3.51 9a9 9 0 0 1 14.85-3.36L23 10M1 14l4.64 4.36A9 9 0 0 0 20.49 15" stroke="currentColor" stroke-width="2" stroke-linecap="round" stroke-linejoin="round"/></svg>
          Refresh
        </button>
      </div>

      <div v-if="loading" class="loading-state">
        <BrandSpinner :size="40" color="#0A5C38" />
        <p>Loading estate announcements...</p>
      </div>

      <div v-else-if="newsList.length === 0" class="empty-state">
        <div class="empty-icon">📢</div>
        <h3>No Announcements Published Yet</h3>
        <p>Keep your community informed about maintenance, security, dues, or meetings.</p>
        <button class="btn btn-primary" @click="openCreateModal">+ Create First Announcement</button>
      </div>

      <div v-else class="news-grid">
        <article v-for="item in newsList" :key="item.id" class="news-card card">
          <div class="news-card-header">
            <div class="news-card-meta">
              <span class="estate-badge">{{ item.estateName || 'Estate' }}</span>
              <span class="news-date">{{ fmtDate(item.createdAt) }}</span>
            </div>
            <div class="news-card-actions">
              <button class="icon-btn edit-btn" title="Edit Announcement" @click="openEditModal(item)">
                <svg width="16" height="16" viewBox="0 0 24 24" fill="none"><path d="M11 4H4a2 2 0 0 0-2 2v14a2 2 0 0 0 2 2h14a2 2 0 0 0 2-2v-7" stroke="currentColor" stroke-width="2" stroke-linecap="round"/><path d="M18.5 2.5a2.121 2.121 0 0 1 3 3L12 15l-4 1 1-4 9.5-9.5z" stroke="currentColor" stroke-width="2" stroke-linecap="round"/></svg>
              </button>
              <button class="icon-btn delete-btn" title="Delete Announcement" @click="confirmDelete(item)">
                <svg width="16" height="16" viewBox="0 0 24 24" fill="none"><path d="M3 6h18M19 6v14a2 2 0 0 1-2 2H7a2 2 0 0 1-2-2V6m3 0V4a2 2 0 0 1 2-2h4a2 2 0 0 1 2 2v2" stroke="currentColor" stroke-width="2" stroke-linecap="round" stroke-linejoin="round"/></svg>
              </button>
            </div>
          </div>

          <h3 class="news-item-title">{{ item.title }}</h3>
          <p class="news-item-preview">{{ item.content }}</p>

          <div v-if="item.image" class="news-image-preview">
            <img :src="item.image" alt="Announcement graphic" />
          </div>

          <div class="news-card-footer">
            <div class="author-tag">
              <span class="author-avatar">{{ (item.adminName || 'A').charAt(0).toUpperCase() }}</span>
              <span class="author-name">Posted by {{ item.adminName || 'Estate Admin' }}</span>
            </div>
            <span class="feed-sync-tag">✓ Visible in Resident App</span>
          </div>
        </article>
      </div>
    </section>

    <!-- Create / Edit Announcement Modal -->
    <div v-if="showModal" class="sheet-overlay" @click.self="showModal = false">
      <div class="sheet sheet-md card">
        <div class="sheet-head">
          <div>
            <div class="sheet-eyebrow">{{ isEditing ? 'Edit Broadcast' : 'Resident Announcement' }}</div>
            <h3 class="sheet-title">{{ isEditing ? 'Update Announcement' : 'Publish New Announcement' }}</h3>
          </div>
          <button class="sheet-close" @click="showModal = false">&times;</button>
        </div>

        <form class="news-form" @submit.prevent="saveAnnouncement">
          <!-- Target Estate Selector -->
          <div class="form-group" v-if="estates.length > 1">
            <label class="form-label">Target Estate *</label>
            <select v-model="form.estate_id" class="form-input" required>
              <option v-for="e in estates" :key="e.id" :value="e.id">{{ e.name }}</option>
            </select>
          </div>

          <!-- Announcement Title -->
          <div class="form-group">
            <label class="form-label">Announcement Title *</label>
            <input
              v-model="form.title"
              type="text"
              class="form-input"
              placeholder="e.g. Scheduled Transformer Maintenance This Saturday"
              required
              maxlength="255"
            />
          </div>

          <!-- Content Body -->
          <div class="form-group">
            <label class="form-label">Announcement Message *</label>
            <textarea
              v-model="form.content"
              class="form-input form-textarea"
              rows="6"
              placeholder="Detail the announcement for residents: date, time, affected areas, guidelines, or contacts..."
              required
            ></textarea>
            <span class="form-hint">Residents will see this formatted cleanly in their Checkpass app under the News tab.</span>
          </div>

          <!-- Optional Image URL -->
          <div class="form-group">
            <label class="form-label">Optional Banner Image URL</label>
            <input
              v-model="form.image"
              type="url"
              class="form-input"
              placeholder="https://example.com/notice-banner.jpg"
            />
          </div>

          <div class="sheet-actions">
            <button type="button" class="btn btn-outline" @click="showModal = false">Cancel</button>
            <button type="submit" class="btn btn-primary" :disabled="saving">
              {{ saving ? 'Publishing…' : (isEditing ? 'Save Changes' : 'Broadcast to Residents') }}
            </button>
          </div>
        </form>
      </div>
    </div>

    <!-- Confirm Delete Modal -->
    <div v-if="deleteTarget" class="sheet-overlay" @click.self="deleteTarget = null">
      <div class="sheet sheet-sm card">
        <div class="sheet-head">
          <div>
            <div class="sheet-eyebrow">Remove Announcement</div>
            <h3 class="sheet-title">Delete Announcement?</h3>
          </div>
          <button class="sheet-close" @click="deleteTarget = null">&times;</button>
        </div>
        <p class="confirm-copy">
          Are you sure you want to remove <strong>"{{ deleteTarget.title }}"</strong>? It will immediately disappear from resident mobile feeds.
        </p>
        <div class="sheet-actions">
          <button class="btn btn-outline" @click="deleteTarget = null">Cancel</button>
          <button class="btn btn-danger" :disabled="deleting" @click="doDelete">
            {{ deleting ? 'Deleting…' : 'Delete Now' }}
          </button>
        </div>
      </div>
    </div>
  </div>
</template>

<script setup lang="ts">
import { ref, onMounted } from 'vue'
import { newsApi, estatesApi } from '@/api/index'
import { useToast } from '@/composables/useToast'
import BrandSpinner from '@/components/BrandSpinner.vue'

interface NewsItem {
  id: number
  estateId: number
  estateName?: string
  adminId: number
  adminName?: string
  title: string
  content: string
  image?: string | null
  createdAt?: string
  updatedAt?: string
}

const { showToast } = useToast()

const loading = ref(true)
const saving = ref(false)
const deleting = ref(false)
const newsList = ref<NewsItem[]>([])
const estates = ref<any[]>([])
const selectedEstateId = ref<number | null>(null)
const searchQuery = ref('')
const searchTimeout = ref<any>(null)

const showModal = ref(false)
const isEditing = ref(false)
const editingId = ref<number | null>(null)
const deleteTarget = ref<NewsItem | null>(null)

const form = ref({
  estate_id: 1,
  title: '',
  content: '',
  image: '',
})

async function loadEstates() {
  try {
    const res = await estatesApi.getAll()
    estates.value = res.data?.estates || []
    if (estates.value.length > 0 && !form.value.estate_id) {
      form.value.estate_id = estates.value[0].id
    }
  } catch (err) {
    console.warn('Could not load estates', err)
  }
}

async function loadNews() {
  loading.value = true
  try {
    const params: Record<string, unknown> = {}
    if (selectedEstateId.value) params.estate_id = selectedEstateId.value
    if (searchQuery.value.trim()) params.search = searchQuery.value.trim()
    const res = await newsApi.getAll(params)
    newsList.value = res.data?.news || []
  } catch (err) {
    showToast('Failed to load announcements.', 'error')
  } finally {
    loading.value = false
  }
}

function handleSearch() {
  if (searchTimeout.value) clearTimeout(searchTimeout.value)
  searchTimeout.value = setTimeout(() => {
    loadNews()
  }, 350)
}

function openCreateModal() {
  isEditing.value = false
  editingId.value = null
  const defaultEstate = selectedEstateId.value || (estates.value[0]?.id ?? 1)
  form.value = {
    estate_id: defaultEstate,
    title: '',
    content: '',
    image: '',
  }
  showModal.value = true
}

function openEditModal(item: NewsItem) {
  isEditing.value = true
  editingId.value = item.id
  form.value = {
    estate_id: item.estateId,
    title: item.title,
    content: item.content,
    image: item.image || '',
  }
  showModal.value = true
}

async function saveAnnouncement() {
  saving.value = true
  try {
    if (isEditing.value && editingId.value) {
      await newsApi.update(editingId.value, {
        title: form.value.title.trim(),
        content: form.value.content.trim(),
        image: form.value.image.trim() || null,
      })
      showToast('Announcement updated successfully.', 'success')
    } else {
      await newsApi.create({
        estate_id: form.value.estate_id,
        title: form.value.title.trim(),
        content: form.value.content.trim(),
        image: form.value.image.trim() || null,
      })
      showToast('Announcement broadcasted to residents!', 'success')
    }
    showModal.value = false
    await loadNews()
  } catch (err: any) {
    const msg = err.response?.data?.message || 'Failed to save announcement.'
    showToast(msg, 'error')
  } finally {
    saving.value = false
  }
}

function confirmDelete(item: NewsItem) {
  deleteTarget.value = item
}

async function doDelete() {
  if (!deleteTarget.value) return
  deleting.value = true
  try {
    await newsApi.delete(deleteTarget.value.id)
    showToast('Announcement deleted.', 'success')
    deleteTarget.value = null
    await loadNews()
  } catch (err) {
    showToast('Failed to delete announcement.', 'error')
  } finally {
    deleting.value = false
  }
}

function fmtDate(iso?: string) {
  if (!iso) return '—'
  const d = new Date(iso)
  return d.toLocaleDateString('en-GB', { day: 'numeric', month: 'short', year: 'numeric', hour: '2-digit', minute: '2-digit' })
}

onMounted(async () => {
  await Promise.all([loadEstates(), loadNews()])
})
</script>

<style scoped>
.news-page {
  display: flex;
  flex-direction: column;
  gap: 20px;
}

.hero-card--emerald {
  background: linear-gradient(135deg, #0B2B1F 0%, #0A5C38 100%);
  color: #FFFFFF;
  display: flex;
  justify-content: space-between;
  align-items: center;
  gap: 20px;
  flex-wrap: wrap;
  padding: 28px;
  border-radius: 18px;
}

.hero-content {
  flex: 1;
  min-width: 260px;
}

.hero-eyebrow {
  font-size: 11.5px;
  text-transform: uppercase;
  letter-spacing: 0.1em;
  font-weight: 700;
  color: #34D399;
  margin-bottom: 6px;
}

.hero-title {
  font-size: 24px;
  font-weight: 800;
  line-height: 1.25;
  margin: 0 0 8px;
}

.hero-copy {
  font-size: 13.5px;
  color: #D1FAE5;
  line-height: 1.5;
  margin: 0;
  max-width: 620px;
}

.hero-cta-btn {
  display: inline-flex;
  align-items: center;
  gap: 8px;
  font-weight: 700;
  padding: 12px 20px;
  white-space: nowrap;
}

.filter-row {
  display: flex;
  gap: 12px;
  flex-wrap: wrap;
}

.search-input-wrap {
  position: relative;
  flex: 1;
  min-width: 240px;
}

.search-icon {
  position: absolute;
  left: 14px;
  top: 50%;
  transform: translateY(-50%);
  color: #94A3B8;
}

.search-input {
  width: 100%;
  padding: 10px 14px 10px 40px;
  border: 1px solid #E2E8F0;
  border-radius: 10px;
  font-size: 13.5px;
  outline: none;
  background: #F8FAFC;
}

.search-input:focus {
  border-color: #0A5C38;
  background: #FFFFFF;
}

.estate-select {
  padding: 10px 14px;
  border: 1px solid #E2E8F0;
  border-radius: 10px;
  font-size: 13.5px;
  background: #FFFFFF;
  color: #1E293B;
  outline: none;
  cursor: pointer;
}

.news-grid {
  display: grid;
  grid-template-columns: repeat(auto-fill, minmax(320px, 1fr));
  gap: 16px;
  margin-top: 16px;
}

.news-card {
  display: flex;
  flex-direction: column;
  padding: 20px;
  border: 1px solid #E2E8F0;
  border-radius: 14px;
  background: #FFFFFF;
  transition: transform 0.15s ease, box-shadow 0.15s ease;
}

.news-card:hover {
  transform: translateY(-2px);
  box-shadow: 0 6px 18px rgba(0, 0, 0, 0.06);
}

.news-card-header {
  display: flex;
  justify-content: space-between;
  align-items: flex-start;
  margin-bottom: 12px;
}

.news-card-meta {
  display: flex;
  flex-direction: column;
  gap: 4px;
}

.estate-badge {
  font-size: 11px;
  font-weight: 700;
  background: #ECFDF5;
  color: #0A5C38;
  padding: 3px 8px;
  border-radius: 6px;
  display: inline-block;
  align-self: flex-start;
}

.news-date {
  font-size: 11.5px;
  color: #94A3B8;
}

.news-card-actions {
  display: flex;
  gap: 6px;
}

.icon-btn {
  background: #F1F5F9;
  border: none;
  border-radius: 8px;
  width: 32px;
  height: 32px;
  display: flex;
  align-items: center;
  justify-content: center;
  cursor: pointer;
  color: #475569;
  transition: all 0.15s ease;
}

.icon-btn:hover {
  background: #E2E8F0;
  color: #0F172A;
}

.icon-btn.delete-btn:hover {
  background: #FEE2E2;
  color: #DC2626;
}

.news-item-title {
  font-size: 16px;
  font-weight: 700;
  color: #0F172A;
  margin: 0 0 8px;
  line-height: 1.35;
}

.news-item-preview {
  font-size: 13.5px;
  color: #475569;
  line-height: 1.55;
  margin: 0 0 14px;
  flex: 1;
  display: -webkit-box;
  -webkit-line-clamp: 4;
  -webkit-box-orient: vertical;
  overflow: hidden;
}

.news-image-preview {
  margin-bottom: 14px;
  border-radius: 10px;
  overflow: hidden;
  max-height: 160px;
}

.news-image-preview img {
  width: 100%;
  height: 100%;
  object-fit: cover;
}

.news-card-footer {
  display: flex;
  justify-content: space-between;
  align-items: center;
  padding-top: 12px;
  border-top: 1px solid #F1F5F9;
  font-size: 11.5px;
  color: #64748B;
  flex-wrap: wrap;
  gap: 8px;
}

.author-tag {
  display: flex;
  align-items: center;
  gap: 6px;
}

.author-avatar {
  width: 20px;
  height: 20px;
  border-radius: 50%;
  background: #0A5C38;
  color: #FFFFFF;
  display: flex;
  align-items: center;
  justify-content: center;
  font-size: 10px;
  font-weight: 700;
}

.feed-sync-tag {
  color: #059669;
  font-weight: 600;
}

/* Modals */
.sheet-overlay {
  position: fixed;
  inset: 0;
  background: rgba(0, 0, 0, 0.45);
  backdrop-filter: blur(4px);
  display: flex;
  align-items: center;
  justify-content: center;
  z-index: 9999;
  padding: 16px;
}

.sheet {
  background: #FFFFFF;
  border-radius: 18px;
  width: 100%;
  box-shadow: 0 20px 40px rgba(0, 0, 0, 0.2);
  display: flex;
  flex-direction: column;
  max-height: 90vh;
  overflow-y: auto;
}

.sheet-md {
  max-width: 580px;
  padding: 24px;
}

.sheet-sm {
  max-width: 420px;
  padding: 24px;
}

.sheet-head {
  display: flex;
  justify-content: space-between;
  align-items: flex-start;
  margin-bottom: 20px;
}

.sheet-eyebrow {
  font-size: 11px;
  text-transform: uppercase;
  letter-spacing: 0.08em;
  font-weight: 700;
  color: #0A5C38;
}

.sheet-title {
  font-size: 20px;
  font-weight: 800;
  color: #0F172A;
  margin: 4px 0 0;
}

.sheet-close {
  background: transparent;
  border: none;
  font-size: 24px;
  color: #94A3B8;
  cursor: pointer;
  line-height: 1;
}

.form-group {
  margin-bottom: 16px;
}

.form-label {
  display: block;
  font-size: 12.5px;
  font-weight: 700;
  color: #334155;
  margin-bottom: 6px;
}

.form-input {
  width: 100%;
  padding: 11px 14px;
  border: 1px solid #CBD5E1;
  border-radius: 10px;
  font-size: 13.5px;
  outline: none;
  color: #0F172A;
  background: #FFFFFF;
  box-sizing: border-box;
}

.form-input:focus {
  border-color: #0A5C38;
  box-shadow: 0 0 0 3px rgba(10, 92, 56, 0.12);
}

.form-textarea {
  resize: vertical;
  line-height: 1.5;
}

.form-hint {
  display: block;
  font-size: 11.5px;
  color: #64748B;
  margin-top: 5px;
}

.sheet-actions {
  display: flex;
  justify-content: flex-end;
  gap: 10px;
  margin-top: 24px;
}

.confirm-copy {
  font-size: 14px;
  color: #475569;
  line-height: 1.5;
  margin: 0 0 20px;
}

.spinning {
  animation: spin 0.8s linear infinite;
}

@keyframes spin {
  to { transform: rotate(360deg); }
}
</style>
