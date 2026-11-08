<template>
  <div class="crm-page">
    <!-- Header Zone -->
    <header class="crm-header">
      <div class="crm-header-left">
        <div class="crm-badge">SUPER ADMIN SALES PIPELINE</div>
        <h1 class="crm-title">Estate Sales CRM</h1>
        <p class="crm-sub">Track incoming estate leads, AI Concierge inquiries, and convert gated communities to active CheckPass portals.</p>
      </div>

      <div class="crm-header-actions">
        <button class="btn btn-secondary" @click="syncLeadsFromStorage">
          <svg width="15" height="15" viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="2"><path d="M21.5 2v6h-6M2.5 22v-6h6M2 11.5a10 10 0 0 1 18.8-4.3M22 12.5a10 10 0 0 1-18.8 4.2"/></svg>
          Sync AI Inquiries
        </button>
        <button class="btn btn-primary" @click="showAddLeadModal = true">
          + Add Estate Lead
        </button>
      </div>
    </header>

    <!-- Top KPI Metrics Bar (Inspired by KargulStudio Sales CRM) -->
    <div class="kpi-grid">
      <div class="kpi-card">
        <div class="kpi-icon kpi-icon--emerald">₦</div>
        <div class="kpi-body">
          <div class="kpi-value">{{ formatNaira(totalPipelineValue) }}</div>
          <div class="kpi-label">Active Pipeline Value</div>
        </div>
      </div>

      <div class="kpi-card">
        <div class="kpi-icon kpi-icon--blue">🏢</div>
        <div class="kpi-body">
          <div class="kpi-value">{{ activeLeadsCount }}</div>
          <div class="kpi-label">Active Estate Deals</div>
        </div>
      </div>

      <div class="kpi-card">
        <div class="kpi-icon kpi-icon--purple">🤖</div>
        <div class="kpi-body">
          <div class="kpi-value">{{ aiLeadsCount }}</div>
          <div class="kpi-label">AI Concierge Inflow</div>
        </div>
      </div>

      <div class="kpi-card">
        <div class="kpi-icon kpi-icon--teal">🧪</div>
        <div class="kpi-body">
          <div class="kpi-value">{{ pilotCount }}</div>
          <div class="kpi-label">Sandbox Pilots Active</div>
        </div>
      </div>

      <div class="kpi-card">
        <div class="kpi-icon kpi-icon--gold">🏆</div>
        <div class="kpi-body">
          <div class="kpi-value">{{ wonCount }}</div>
          <div class="kpi-label">Closed Won Estates</div>
        </div>
      </div>
    </div>

    <!-- Controls Bar -->
    <div class="controls-bar">
      <div class="search-wrap">
        <svg width="16" height="16" viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="2"><circle cx="11" cy="11" r="8"/><path d="m21 21-4.35-4.35"/></svg>
        <input
          v-model="searchQuery"
          type="text"
          placeholder="Search by estate, contact person, or city..."
          class="search-input"
        />
      </div>

      <div class="controls-right">
        <select v-model="selectedCityFilter" class="filter-select">
          <option value="All">All Locations</option>
          <option value="Lagos">Lagos</option>
          <option value="Abuja">Abuja</option>
          <option value="Port Harcourt">Port Harcourt</option>
          <option value="Ibadan">Ibadan</option>
        </select>

        <div class="view-switch">
          <button
            class="view-btn"
            :class="{ active: viewMode === 'kanban' }"
            @click="viewMode = 'kanban'"
            title="Kanban Board View"
          >
            <svg width="16" height="16" viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="2"><rect x="3" y="3" width="7" height="18" rx="1"/><rect x="14" y="3" width="7" height="10" rx="1"/></svg>
            <span>Kanban</span>
          </button>
          <button
            class="view-btn"
            :class="{ active: viewMode === 'table' }"
            @click="viewMode = 'table'"
            title="Table List View"
          >
            <svg width="16" height="16" viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="2"><line x1="8" y1="6" x2="21" y2="6"/><line x1="8" y1="12" x2="21" y2="12"/><line x1="8" y1="18" x2="21" y2="18"/><line x1="3" y1="6" x2="3.01" y2="6"/><line x1="3" y1="12" x2="3.01" y2="12"/><line x1="3" y1="18" x2="3.01" y2="18"/></svg>
            <span>Table</span>
          </button>
        </div>
      </div>
    </div>

    <!-- KANBAN BOARD VIEW -->
    <div v-if="viewMode === 'kanban'" class="kanban-board">
      <div
        v-for="stage in STAGES"
        :key="stage.key"
        class="kanban-col"
      >
        <div class="kanban-col-head">
          <div class="col-head-left">
            <span class="stage-dot" :style="{ background: stage.color }"></span>
            <span class="stage-name">{{ stage.label }}</span>
          </div>
          <span class="stage-count">{{ getLeadsForStage(stage.key).length }}</span>
        </div>

        <div class="kanban-cards-wrap">
          <div
            v-for="lead in getLeadsForStage(stage.key)"
            :key="lead.id"
            class="deal-card"
            @click="openLeadDetails(lead)"
          >
            <div class="deal-card-top">
              <span class="estate-name">{{ lead.estateName }}</span>
              <span class="source-tag" :class="`source-${lead.source.toLowerCase().replace(/[^a-z]/g, '')}`">
                {{ lead.source === 'AI Concierge' ? '🤖 AI Lead' : '🌐 Web' }}
              </span>
            </div>

            <div class="deal-meta-pills">
              <span class="meta-pill">📍 {{ lead.city }}</span>
              <span class="meta-pill">🏠 {{ lead.units }} Units</span>
            </div>

            <div class="deal-value-row">
              <span class="value-label">Est. Annual Value</span>
              <strong class="value-amount">{{ formatNaira(lead.dealValue) }}</strong>
            </div>

            <div class="deal-contact-row">
              <div class="contact-avatar">{{ lead.name.charAt(0) }}</div>
              <div class="contact-info">
                <span class="contact-name">{{ lead.name }}</span>
                <span class="contact-role">{{ lead.role || 'HOA Executive' }}</span>
              </div>
            </div>

            <!-- Card Actions -->
            <div class="deal-card-foot" @click.stop>
              <button
                v-if="stage.prev"
                class="step-btn"
                title="Move Back"
                @click="moveStage(lead, stage.prev)"
              >
                ←
              </button>
              <div class="foot-spacer"></div>
              <button
                v-if="stage.next"
                class="step-btn step-btn--next"
                title="Advance Stage"
                @click="moveStage(lead, stage.next)"
              >
                Move to {{ getStageLabel(stage.next) }} →
              </button>
            </div>
          </div>

          <div v-if="getLeadsForStage(stage.key).length === 0" class="empty-col-drop">
            <span>No leads in {{ stage.label }}</span>
          </div>
        </div>
      </div>
    </div>

    <!-- TABLE LIST VIEW -->
    <div v-else class="table-card">
      <table class="crm-table">
        <thead>
          <tr>
            <th>Estate Name</th>
            <th>Contact Person</th>
            <th>Location</th>
            <th>Units</th>
            <th>Est. Deal Value</th>
            <th>Pipeline Stage</th>
            <th>Lead Source</th>
            <th>Last Updated</th>
            <th>Actions</th>
          </tr>
        </thead>
        <tbody>
          <tr v-for="lead in filteredLeads" :key="lead.id" class="table-row" @click="openLeadDetails(lead)">
            <td class="bold-cell">{{ lead.estateName }}</td>
            <td>
              <div class="table-contact">
                <strong>{{ lead.name }}</strong>
                <small>{{ lead.email || lead.phone }}</small>
              </div>
            </td>
            <td>{{ lead.city }}</td>
            <td><span class="unit-badge">{{ lead.units }} units</span></td>
            <td class="deal-value-cell">{{ formatNaira(lead.dealValue) }}</td>
            <td>
              <span class="stage-badge" :style="{ background: getStageColor(lead.stage) }">
                {{ getStageLabel(lead.stage) }}
              </span>
            </td>
            <td>
              <span class="source-tag">
                {{ lead.source === 'AI Concierge' ? '🤖 AI Concierge' : '🌐 Web Form' }}
              </span>
            </td>
            <td>{{ formatDate(lead.updatedAt) }}</td>
            <td @click.stop>
              <button class="table-action-btn" @click="openLeadDetails(lead)">View Details</button>
            </td>
          </tr>
        </tbody>
      </table>
    </div>

    <!-- LEAD DETAIL DRAWER / MODAL -->
    <div v-if="selectedLead" class="modal-scrim" @click="selectedLead = null">
      <div class="lead-drawer" @click.stop>
        <div class="drawer-head">
          <div>
            <div class="drawer-kicker">ESTATE DEAL PROFILE</div>
            <h2 class="drawer-title">{{ selectedLead.estateName }}</h2>
          </div>
          <button class="drawer-close-btn" @click="selectedLead = null">✕</button>
        </div>

        <div class="drawer-body">
          <!-- Stage Progress Bar -->
          <div class="stage-stepper">
            <button
              v-for="st in STAGES"
              :key="st.key"
              class="stepper-step"
              :class="{
                'step-done': isStagePassed(selectedLead.stage, st.key),
                'step-current': selectedLead.stage === st.key
              }"
              @click="selectedLead.stage = st.key; saveLead(selectedLead)"
            >
              {{ st.label }}
            </button>
          </div>

          <!-- Quick Metrics -->
          <div class="lead-metrics-row">
            <div class="lm-item">
              <span class="lm-label">Est. Deal Value</span>
              <strong class="lm-val">{{ formatNaira(selectedLead.dealValue) }}/yr</strong>
            </div>
            <div class="lm-item">
              <span class="lm-label">Estate Size</span>
              <strong class="lm-val">{{ selectedLead.units }} Homes</strong>
            </div>
            <div class="lm-item">
              <span class="lm-label">City</span>
              <strong class="lm-val">{{ selectedLead.city }}</strong>
            </div>
            <div class="lm-item">
              <span class="lm-label">Lead Source</span>
              <strong class="lm-val">{{ selectedLead.source }}</strong>
            </div>
          </div>

          <!-- Contact Details -->
          <div class="drawer-card">
            <h3 class="drawer-card-title">Estate Executive Contact</h3>
            <div class="contact-grid">
              <div>
                <span class="cg-label">Name</span>
                <strong>{{ selectedLead.name }}</strong>
              </div>
              <div>
                <span class="cg-label">Role</span>
                <strong>{{ selectedLead.role || 'Exco / Manager' }}</strong>
              </div>
              <div>
                <span class="cg-label">Phone</span>
                <a :href="`tel:${selectedLead.phone}`">{{ selectedLead.phone || 'Not provided' }}</a>
              </div>
              <div>
                <span class="cg-label">Email</span>
                <a :href="`mailto:${selectedLead.email}`">{{ selectedLead.email || 'Not provided' }}</a>
              </div>
            </div>
          </div>

          <!-- AI Concierge Transcript (If Source is AI) -->
          <div v-if="selectedLead.chatTranscript" class="drawer-card">
            <h3 class="drawer-card-title">🤖 AI Concierge Inquiry Transcript</h3>
            <div class="chat-transcript-box">
              <pre>{{ selectedLead.chatTranscript }}</pre>
            </div>
          </div>

          <!-- Notes & Next Steps -->
          <div class="drawer-card">
            <h3 class="drawer-card-title">Executive Notes & Next Steps</h3>
            <textarea
              v-model="selectedLead.notes"
              rows="4"
              class="notes-textarea"
              placeholder="Record notes from your call or live meeting with the Estate Chairman..."
              @change="saveLead(selectedLead)"
            ></textarea>
          </div>

          <!-- Action Buttons -->
          <div class="drawer-foot">
            <button
              class="btn btn-outline"
              @click="deleteLead(selectedLead.id)"
            >
              Delete Lead
            </button>
            <button
              class="btn btn-primary"
              @click="convertLeadToSandbox(selectedLead)"
            >
              ⚡ Provision Free 3-Unit Sandbox Estate
            </button>
          </div>
        </div>
      </div>
    </div>

    <!-- ADD LEAD MODAL -->
    <div v-if="showAddLeadModal" class="modal-scrim" @click="showAddLeadModal = false">
      <div class="add-lead-modal" @click.stop>
        <div class="modal-head">
          <h3>Add New Estate Deal</h3>
          <button class="modal-close" @click="showAddLeadModal = false">✕</button>
        </div>

        <form class="add-lead-form" @submit.prevent="createNewLead">
          <div class="form-row">
            <div class="form-group">
              <label>Estate Name *</label>
              <input v-model="newLeadForm.estateName" type="text" placeholder="e.g. Lekki Phase 1 Scheme 2" required />
            </div>
            <div class="form-group">
              <label>City / Location *</label>
              <select v-model="newLeadForm.city" required>
                <option value="Lagos">Lagos</option>
                <option value="Abuja">Abuja</option>
                <option value="Port Harcourt">Port Harcourt</option>
                <option value="Ibadan">Ibadan</option>
                <option value="Other">Other</option>
              </select>
            </div>
          </div>

          <div class="form-row">
            <div class="form-group">
              <label>Contact Person *</label>
              <input v-model="newLeadForm.name" type="text" placeholder="e.g. Chief Femi Adeyemi" required />
            </div>
            <div class="form-group">
              <label>Role</label>
              <input v-model="newLeadForm.role" type="text" placeholder="e.g. HOA Chairman" />
            </div>
          </div>

          <div class="form-row">
            <div class="form-group">
              <label>Phone Number *</label>
              <input v-model="newLeadForm.phone" type="tel" placeholder="+234 803 000 0000" required />
            </div>
            <div class="form-group">
              <label>Email Address</label>
              <input v-model="newLeadForm.email" type="email" placeholder="chairman@estate.ng" />
            </div>
          </div>

          <div class="form-row">
            <div class="form-group">
              <label>Estimated Units / Households</label>
              <input v-model.number="newLeadForm.units" type="number" min="10" placeholder="100" />
            </div>
            <div class="form-group">
              <label>Pipeline Stage</label>
              <select v-model="newLeadForm.stage">
                <option v-for="st in STAGES" :key="st.key" :value="st.key">{{ st.label }}</option>
              </select>
            </div>
          </div>

          <div class="form-group">
            <label>Notes / Requirements</label>
            <textarea v-model="newLeadForm.notes" rows="3" placeholder="Needs boom barrier integration and central diesel levy automation..."></textarea>
          </div>

          <div class="form-actions">
            <button type="button" class="btn btn-secondary" @click="showAddLeadModal = false">Cancel</button>
            <button type="submit" class="btn btn-primary">Create Deal</button>
          </div>
        </form>
      </div>
    </div>
  </div>
</template>

<script setup lang="ts">
import { computed, onMounted, ref } from 'vue'
import { useRouter } from 'vue-router'
import { useToast } from '@/composables/useToast'

type LeadStage = 'new' | 'discovery' | 'demo' | 'proposal' | 'pilot' | 'won'

type Lead = {
  id: string
  estateName: string
  name: string
  role?: string
  email?: string
  phone?: string
  city: string
  units: number
  dealValue: number
  stage: LeadStage
  source: 'AI Concierge' | 'Website Form' | 'Referral' | 'Direct Outbound'
  chatTranscript?: string
  notes?: string
  createdAt: string
  updatedAt: string
}

const STAGES: Array<{ key: LeadStage; label: string; color: string; next: LeadStage | null; prev: LeadStage | null }> = [
  { key: 'new', label: 'New Inquiries', color: '#3b82f6', next: 'discovery', prev: null },
  { key: 'discovery', label: 'Discovery Call', color: '#6366f1', next: 'demo', prev: 'new' },
  { key: 'demo', label: 'Exco Demo', color: '#8b5cf6', next: 'proposal', prev: 'discovery' },
  { key: 'proposal', label: 'Proposal Sent', color: '#ec4899', next: 'pilot', prev: 'demo' },
  { key: 'pilot', label: 'Sandbox Pilot', color: '#f59e0b', next: 'won', prev: 'proposal' },
  { key: 'won', label: 'Closed Won', color: '#10b981', next: null, prev: 'pilot' },
]

const router = useRouter()
const { showToast } = useToast()

const viewMode = ref<'kanban' | 'table'>('kanban')
const searchQuery = ref('')
const selectedCityFilter = ref('All')

const showAddLeadModal = ref(false)
const selectedLead = ref<Lead | null>(null)

const newLeadForm = ref({
  estateName: '',
  city: 'Lagos',
  name: '',
  role: 'HOA Executive',
  phone: '',
  email: '',
  units: 100,
  stage: 'new' as const,
  notes: '',
})

// Seed realistic default leads
const DEFAULT_LEADS: Lead[] = [
  {
    id: 'lead-1',
    estateName: 'Victoria Crest Estate IV',
    name: 'Engr. Babatunde Alao',
    role: 'Exco Chairman',
    city: 'Lagos',
    units: 180,
    dealValue: 180 * 1200 * 12,
    stage: 'new',
    source: 'AI Concierge',
    phone: '0802 403 5326',
    email: 'babatunde@victoriacrest.ng',
    chatTranscript: "User: How does the 4-second clearance work when internet is down?\nAI: CheckPass guards have an offline cached cryptographic token engine...\nUser: We have 180 flats in Lekki. My name is Babatunde Alao.",
    notes: 'Inquired through on-site AI Concierge regarding offline guard scanning and dues automation.',
    createdAt: new Date(Date.now() - 3600000 * 4).toISOString(),
    updatedAt: new Date(Date.now() - 3600000 * 4).toISOString(),
  },
  {
    id: 'lead-2',
    estateName: 'Megamound Carlton Gate',
    name: 'Mrs. Folake Adeyemi',
    role: 'Facility Manager',
    city: 'Lagos',
    units: 240,
    dealValue: 240 * 1200 * 12,
    stage: 'discovery',
    source: 'Website Form',
    phone: '0803 555 1234',
    email: 'folake@carlton.ng',
    notes: 'Needs integration with 2 existing BFT hydraulic boom barriers.',
    createdAt: new Date(Date.now() - 86400000 * 2).toISOString(),
    updatedAt: new Date(Date.now() - 86400000 * 1).toISOString(),
  },
  {
    id: 'lead-3',
    estateName: 'Pinnock Beach Estate',
    name: 'Barrister Nnamdi Kanu',
    role: 'Secretary HOA',
    city: 'Lagos',
    units: 210,
    dealValue: 210 * 1200 * 12,
    stage: 'demo',
    source: 'AI Concierge',
    phone: '0812 345 6789',
    email: 'nnamdi@pinnockbeach.ng',
    chatTranscript: "User: Can we track move-out trucks and defaulting tenants?\nAI: Yes, CheckPass has move-out asset verification manifests.\nUser: Schedule a walkthrough for our Exco Saturday 10am.",
    notes: '15-min virtual walkthrough scheduled for Saturday 10:00 AM.',
    createdAt: new Date(Date.now() - 86400000 * 4).toISOString(),
    updatedAt: new Date(Date.now() - 86400000 * 2).toISOString(),
  },
  {
    id: 'lead-4',
    estateName: 'Crown Estate Sangotedo',
    name: 'Alhaji Musa Bello',
    role: 'Estate Admin',
    city: 'Lagos',
    units: 420,
    dealValue: 420 * 1200 * 12,
    stage: 'proposal',
    source: 'Referral',
    phone: '0809 111 2233',
    email: 'admin@crownestate.ng',
    notes: 'Custom proposal sent for 420 units on Enterprise tier with guard devices included.',
    createdAt: new Date(Date.now() - 86400000 * 7).toISOString(),
    updatedAt: new Date(Date.now() - 86400000 * 3).toISOString(),
  },
  {
    id: 'lead-5',
    estateName: 'Sunrise Hills Estate',
    name: 'Dr. Chidi Okeke',
    role: 'HOA President',
    city: 'Abuja',
    units: 110,
    dealValue: 110 * 1200 * 12,
    stage: 'pilot',
    source: 'AI Concierge',
    phone: '0806 777 8899',
    email: 'chidi@sunrisehills.ng',
    notes: '3-unit test sandbox active at Main Gate terminal.',
    createdAt: new Date(Date.now() - 86400000 * 10).toISOString(),
    updatedAt: new Date(Date.now() - 86400000 * 5).toISOString(),
  },
  {
    id: 'lead-6',
    estateName: 'PHDL Estate Guzape',
    name: 'Captain Danjuma',
    role: 'Chief Security Officer',
    city: 'Abuja',
    units: 75,
    dealValue: 75 * 1200 * 12,
    stage: 'won',
    source: 'Direct Outbound',
    phone: '0802 999 0000',
    email: 'sec@phdl.ng',
    notes: 'Successfully deployed! Gate terminal and resident app live.',
    createdAt: new Date(Date.now() - 86400000 * 20).toISOString(),
    updatedAt: new Date(Date.now() - 86400000 * 12).toISOString(),
  },
]

const leads = ref<Lead[]>([])

function loadLeads() {
  try {
    const raw = localStorage.getItem('checkpass_crm_leads')
    if (raw) {
      leads.value = JSON.parse(raw)
      return
    }
  } catch {}
  leads.value = DEFAULT_LEADS
  saveAllLeads()
}

function saveAllLeads() {
  try {
    localStorage.setItem('checkpass_crm_leads', JSON.stringify(leads.value))
  } catch {}
}

function saveLead(lead: Lead) {
  lead.updatedAt = new Date().toISOString()
  const idx = leads.value.findIndex(l => l.id === lead.id)
  if (idx !== -1) {
    leads.value[idx] = { ...lead }
  }
  saveAllLeads()
}

function syncLeadsFromStorage() {
  loadLeads()
  showToast('Synchronized live AI Concierge leads and web inquiries!', 'success')
}

// KPI Computations
const totalPipelineValue = computed(() => {
  return leads.value
    .filter(l => l.stage !== 'won')
    .reduce((sum, l) => sum + (l.dealValue || 0), 0)
})

const activeLeadsCount = computed(() => {
  return leads.value.filter(l => l.stage !== 'won').length
})

const aiLeadsCount = computed(() => {
  return leads.value.filter(l => l.source === 'AI Concierge').length
})

const pilotCount = computed(() => {
  return leads.value.filter(l => l.stage === 'pilot').length
})

const wonCount = computed(() => {
  return leads.value.filter(l => l.stage === 'won').length
})

const filteredLeads = computed(() => {
  let list = leads.value
  if (selectedCityFilter.value !== 'All') {
    list = list.filter(l => l.city.toLowerCase() === selectedCityFilter.value.toLowerCase())
  }
  if (searchQuery.value.trim()) {
    const q = searchQuery.value.toLowerCase()
    list = list.filter(l =>
      l.estateName.toLowerCase().includes(q) ||
      l.name.toLowerCase().includes(q) ||
      l.city.toLowerCase().includes(q)
    )
  }
  return list
})

function getLeadsForStage(stageKey: string) {
  return filteredLeads.value.filter(l => l.stage === stageKey)
}

function getStageLabel(key: string) {
  const match = STAGES.find(s => s.key === key)
  return match?.label || key
}

function getStageColor(key: string) {
  const match = STAGES.find(s => s.key === key)
  return match?.color || '#0A5C38'
}

function moveStage(lead: Lead, nextStage: LeadStage) {
  lead.stage = nextStage
  saveLead(lead)
  showToast(`Moved ${lead.estateName} to ${getStageLabel(nextStage)}`, 'info')
}

function isStagePassed(currentStage: string, checkStage: string) {
  const curIdx = STAGES.findIndex(s => s.key === currentStage)
  const chkIdx = STAGES.findIndex(s => s.key === checkStage)
  return chkIdx <= curIdx
}

function openLeadDetails(lead: Lead) {
  selectedLead.value = { ...lead }
}

function deleteLead(id: string) {
  leads.value = leads.value.filter(l => l.id !== id)
  saveAllLeads()
  selectedLead.value = null
  showToast('Lead removed from CRM', 'warning')
}

function createNewLead() {
  const units = newLeadForm.value.units || 80
  const dealValue = units * 1200 * 12
  const newLead: Lead = {
    id: `lead-${Date.now()}`,
    estateName: newLeadForm.value.estateName,
    city: newLeadForm.value.city,
    name: newLeadForm.value.name,
    role: newLeadForm.value.role,
    phone: newLeadForm.value.phone,
    email: newLeadForm.value.email,
    units,
    dealValue,
    stage: newLeadForm.value.stage,
    source: 'Direct Outbound',
    notes: newLeadForm.value.notes,
    createdAt: new Date().toISOString(),
    updatedAt: new Date().toISOString(),
  }

  leads.value.unshift(newLead)
  saveAllLeads()
  showAddLeadModal.value = false
  showToast(`Created lead for ${newLead.estateName}`, 'success')

  // Reset form
  newLeadForm.value = {
    estateName: '',
    city: 'Lagos',
    name: '',
    role: 'HOA Executive',
    phone: '',
    email: '',
    units: 100,
    stage: 'new',
    notes: '',
  }
}

function convertLeadToSandbox(lead: Lead) {
  showToast(`Provisioning Sandbox portal for ${lead.estateName}...`, 'info')
  selectedLead.value = null
  router.push({
    path: '/register/estate',
    query: {
      name: lead.estateName,
      city: lead.city,
      units: String(lead.units),
      leadId: lead.id
    }
  })
}

function formatNaira(val: number) {
  return `₦${(val || 0).toLocaleString('en-NG')}`
}

function formatDate(iso: string) {
  if (!iso) return ''
  const d = new Date(iso)
  return d.toLocaleDateString('en-NG', { month: 'short', day: 'numeric', hour: '2-digit', minute: '2-digit' })
}

onMounted(() => {
  loadLeads()
})
</script>

<style scoped>
.crm-page {
  padding: 24px 28px 48px;
  display: flex;
  flex-direction: column;
  gap: 20px;
}

/* Header */
.crm-header {
  display: flex;
  align-items: flex-start;
  justify-content: space-between;
  gap: 16px;
  flex-wrap: wrap;
}

.crm-badge {
  display: inline-block;
  font-size: 11px;
  font-weight: 800;
  letter-spacing: 0.06em;
  color: #0A5C38;
  background: #e6f4ee;
  padding: 4px 10px;
  border-radius: 9999px;
  margin-bottom: 6px;
}

.crm-title {
  font-size: 26px;
  font-weight: 800;
  color: #111827;
  letter-spacing: -0.02em;
  margin: 0;
}

.crm-sub {
  font-size: 13.5px;
  color: #6b7280;
  margin-top: 4px;
}

.crm-header-actions {
  display: flex;
  align-items: center;
  gap: 10px;
}

/* KPI Grid */
.kpi-grid {
  display: grid;
  grid-template-columns: repeat(auto-fit, minmax(200px, 1fr));
  gap: 14px;
}

.kpi-card {
  background: #ffffff;
  border: 1px solid #e5e7eb;
  border-radius: 12px;
  padding: 16px;
  display: flex;
  align-items: center;
  gap: 14px;
  box-shadow: 0 1px 3px rgba(0, 0, 0, 0.04);
}

.kpi-icon {
  width: 44px;
  height: 44px;
  border-radius: 10px;
  display: flex;
  align-items: center;
  justify-content: center;
  font-size: 18px;
  font-weight: 800;
  flex-shrink: 0;
}

.kpi-icon--emerald { background: #e6f4ee; color: #0A5C38; }
.kpi-icon--blue { background: #eff6ff; color: #2563eb; }
.kpi-icon--purple { background: #faf5ff; color: #9333ea; }
.kpi-icon--teal { background: #f0fdfa; color: #0d9488; }
.kpi-icon--gold { background: #fefce8; color: #ca8a04; }

.kpi-value {
  font-size: 20px;
  font-weight: 800;
  color: #111827;
  line-height: 1.2;
}

.kpi-label {
  font-size: 12px;
  color: #6b7280;
  margin-top: 2px;
}

/* Controls */
.controls-bar {
  display: flex;
  align-items: center;
  justify-content: space-between;
  gap: 12px;
  flex-wrap: wrap;
}

.search-wrap {
  position: relative;
  flex: 1;
  min-width: 260px;
  max-width: 400px;
}

.search-wrap svg {
  position: absolute;
  left: 12px;
  top: 50%;
  transform: translateY(-50%);
  color: #9ca3af;
}

.search-input {
  width: 100%;
  padding: 9px 12px 9px 36px;
  border: 1px solid #d1d5db;
  border-radius: 8px;
  font-size: 13.5px;
  outline: none;
  background: #ffffff;
}

.search-input:focus {
  border-color: #0A5C38;
}

.controls-right {
  display: flex;
  align-items: center;
  gap: 10px;
}

.filter-select {
  padding: 8px 12px;
  border: 1px solid #d1d5db;
  border-radius: 8px;
  font-size: 13px;
  background: #ffffff;
  outline: none;
}

.view-switch {
  display: flex;
  background: #f3f4f6;
  border-radius: 8px;
  padding: 2px;
}

.view-btn {
  display: flex;
  align-items: center;
  gap: 6px;
  padding: 6px 12px;
  border: none;
  background: transparent;
  font-size: 12.5px;
  font-weight: 600;
  color: #4b5563;
  border-radius: 6px;
  cursor: pointer;
}

.view-btn.active {
  background: #ffffff;
  color: #111827;
  box-shadow: 0 1px 2px rgba(0, 0, 0, 0.05);
}

/* KANBAN BOARD */
.kanban-board {
  display: grid;
  grid-template-columns: repeat(6, minmax(260px, 1fr));
  gap: 14px;
  overflow-x: auto;
  padding-bottom: 16px;
}

.kanban-col {
  background: #f9fafb;
  border: 1px solid #e5e7eb;
  border-radius: 12px;
  display: flex;
  flex-direction: column;
  min-height: 480px;
}

.kanban-col-head {
  padding: 12px 14px;
  border-bottom: 1px solid #e5e7eb;
  display: flex;
  align-items: center;
  justify-content: space-between;
  background: #ffffff;
  border-radius: 12px 12px 0 0;
}

.col-head-left {
  display: flex;
  align-items: center;
  gap: 8px;
}

.stage-dot {
  width: 8px;
  height: 8px;
  border-radius: 50%;
}

.stage-name {
  font-size: 13px;
  font-weight: 700;
  color: #1f2937;
}

.stage-count {
  font-size: 11px;
  font-weight: 700;
  background: #e5e7eb;
  color: #4b5563;
  padding: 2px 7px;
  border-radius: 9999px;
}

.kanban-cards-wrap {
  padding: 10px;
  display: flex;
  flex-direction: column;
  gap: 10px;
  flex: 1;
}

.deal-card {
  background: #ffffff;
  border: 1px solid #e5e7eb;
  border-radius: 10px;
  padding: 12px;
  cursor: pointer;
  display: flex;
  flex-direction: column;
  gap: 8px;
  box-shadow: 0 1px 3px rgba(0, 0, 0, 0.03);
  transition: transform 0.15s ease, box-shadow 0.15s ease;
}

.deal-card:hover {
  transform: translateY(-2px);
  box-shadow: 0 4px 12px rgba(0, 0, 0, 0.08);
  border-color: #cbd5e1;
}

.deal-card-top {
  display: flex;
  align-items: flex-start;
  justify-content: space-between;
  gap: 8px;
}

.estate-name {
  font-size: 13.5px;
  font-weight: 700;
  color: #111827;
  line-height: 1.25;
}

.source-tag {
  font-size: 10.5px;
  font-weight: 700;
  padding: 2px 6px;
  border-radius: 4px;
  background: #e0f2fe;
  color: #0369a1;
  white-space: nowrap;
}

.source-tag.source-aiconcierge {
  background: #f3e8ff;
  color: #7e22ce;
}

.deal-meta-pills {
  display: flex;
  gap: 6px;
  flex-wrap: wrap;
}

.meta-pill {
  font-size: 11px;
  color: #4b5563;
  background: #f3f4f6;
  padding: 2px 6px;
  border-radius: 4px;
}

.deal-value-row {
  display: flex;
  align-items: baseline;
  justify-content: space-between;
  padding-top: 6px;
  border-top: 1px dashed #f1f5f9;
}

.value-label {
  font-size: 10.5px;
  color: #9ca3af;
  text-transform: uppercase;
}

.value-amount {
  font-size: 13.5px;
  color: #0A5C38;
}

.deal-contact-row {
  display: flex;
  align-items: center;
  gap: 8px;
  margin-top: 2px;
}

.contact-avatar {
  width: 24px;
  height: 24px;
  border-radius: 50%;
  background: #111827;
  color: #ffffff;
  display: flex;
  align-items: center;
  justify-content: center;
  font-size: 11px;
  font-weight: 700;
}

.contact-info {
  display: flex;
  flex-direction: column;
  min-width: 0;
}

.contact-name {
  font-size: 12px;
  font-weight: 600;
  color: #1f2937;
  white-space: nowrap;
  overflow: hidden;
  text-overflow: ellipsis;
}

.contact-role {
  font-size: 10.5px;
  color: #6b7280;
}

.deal-card-foot {
  display: flex;
  align-items: center;
  padding-top: 6px;
  border-top: 1px solid #f3f4f6;
}

.step-btn {
  background: transparent;
  border: 1px solid #e5e7eb;
  border-radius: 4px;
  padding: 3px 8px;
  font-size: 11px;
  color: #4b5563;
  cursor: pointer;
  transition: all 0.15s ease;
}

.step-btn:hover {
  background: #f3f4f6;
}

.step-btn--next {
  background: #e6f4ee;
  border-color: #a7f3d0;
  color: #0A5C38;
  font-weight: 600;
}

.step-btn--next:hover {
  background: #0A5C38;
  color: #ffffff;
}

.foot-spacer {
  flex: 1;
}

.empty-col-drop {
  border: 1.5px dashed #e5e7eb;
  border-radius: 8px;
  padding: 24px 12px;
  text-align: center;
  color: #9ca3af;
  font-size: 12px;
}

/* Table View */
.table-card {
  background: #ffffff;
  border: 1px solid #e5e7eb;
  border-radius: 12px;
  overflow-x: auto;
}

.crm-table {
  width: 100%;
  border-collapse: collapse;
  text-align: left;
  font-size: 13.5px;
}

.crm-table th {
  background: #f9fafb;
  padding: 12px 16px;
  font-size: 12px;
  font-weight: 700;
  color: #6b7280;
  border-bottom: 1px solid #e5e7eb;
  text-transform: uppercase;
  letter-spacing: 0.04em;
}

.crm-table td {
  padding: 12px 16px;
  border-bottom: 1px solid #f3f4f6;
  color: #374151;
}

.table-row:hover {
  background: #f9fafb;
  cursor: pointer;
}

.bold-cell {
  font-weight: 700;
  color: #111827;
}

.unit-badge {
  background: #f3f4f6;
  padding: 2px 8px;
  border-radius: 4px;
  font-size: 12px;
  font-weight: 600;
}

.deal-value-cell {
  font-weight: 700;
  color: #0A5C38;
}

.stage-badge {
  color: #ffffff;
  font-size: 11px;
  font-weight: 700;
  padding: 3px 8px;
  border-radius: 9999px;
  display: inline-block;
}

.table-action-btn {
  background: #ffffff;
  border: 1px solid #d1d5db;
  border-radius: 6px;
  padding: 4px 10px;
  font-size: 12px;
  cursor: pointer;
}

/* Lead Drawer */
.modal-scrim {
  position: fixed;
  inset: 0;
  background: rgba(0, 0, 0, 0.45);
  backdrop-filter: blur(2px);
  z-index: 99999;
  display: flex;
  justify-content: flex-end;
}

.lead-drawer {
  background: #ffffff;
  width: 100%;
  max-width: 540px;
  height: 100%;
  display: flex;
  flex-direction: column;
  box-shadow: -4px 0 24px rgba(0, 0, 0, 0.15);
  overflow-y: auto;
}

.drawer-head {
  padding: 20px 24px;
  border-bottom: 1px solid #e5e7eb;
  display: flex;
  align-items: flex-start;
  justify-content: space-between;
}

.drawer-kicker {
  font-size: 11px;
  font-weight: 800;
  color: #0A5C38;
  letter-spacing: 0.05em;
}

.drawer-title {
  font-size: 20px;
  font-weight: 800;
  color: #111827;
  margin-top: 2px;
}

.drawer-close-btn {
  background: #f3f4f6;
  border: none;
  width: 32px;
  height: 32px;
  border-radius: 50%;
  cursor: pointer;
  font-weight: 700;
}

.drawer-body {
  padding: 20px 24px;
  display: flex;
  flex-direction: column;
  gap: 16px;
}

.stage-stepper {
  display: flex;
  overflow-x: auto;
  gap: 6px;
  padding-bottom: 4px;
}

.stepper-step {
  padding: 6px 12px;
  border: 1px solid #e5e7eb;
  background: #f9fafb;
  border-radius: 9999px;
  font-size: 11.5px;
  font-weight: 600;
  color: #6b7280;
  cursor: pointer;
  white-space: nowrap;
}

.stepper-step.step-current {
  background: #0A5C38;
  color: #ffffff;
  border-color: #0A5C38;
}

.stepper-step.step-done:not(.step-current) {
  background: #e6f4ee;
  color: #0A5C38;
  border-color: #a7f3d0;
}

.lead-metrics-row {
  display: grid;
  grid-template-columns: 1fr 1fr;
  gap: 10px;
}

.lm-item {
  background: #f9fafb;
  border-radius: 8px;
  padding: 10px 12px;
  border: 1px solid #f3f4f6;
}

.lm-label {
  font-size: 11px;
  color: #6b7280;
  display: block;
}

.lm-val {
  font-size: 14px;
  color: #111827;
}

.drawer-card {
  background: #ffffff;
  border: 1px solid #e5e7eb;
  border-radius: 10px;
  padding: 16px;
}

.drawer-card-title {
  font-size: 13px;
  font-weight: 700;
  color: #374151;
  margin-bottom: 10px;
}

.contact-grid {
  display: grid;
  grid-template-columns: 1fr 1fr;
  gap: 10px;
}

.cg-label {
  font-size: 11px;
  color: #9ca3af;
  display: block;
}

.chat-transcript-box pre {
  background: #f8fafc;
  border: 1px solid #e2e8f0;
  border-radius: 6px;
  padding: 10px;
  font-size: 11.5px;
  white-space: pre-wrap;
  font-family: inherit;
  color: #334155;
  max-height: 180px;
  overflow-y: auto;
}

.notes-textarea {
  width: 100%;
  border: 1px solid #d1d5db;
  border-radius: 8px;
  padding: 10px;
  font-size: 13px;
  font-family: inherit;
  outline: none;
}

.notes-textarea:focus {
  border-color: #0A5C38;
}

.drawer-foot {
  display: flex;
  gap: 10px;
  margin-top: 10px;
}

/* Add Lead Modal */
.add-lead-modal {
  background: #ffffff;
  border-radius: 16px;
  width: 90%;
  max-width: 520px;
  margin: auto;
  box-shadow: 0 12px 36px rgba(0, 0, 0, 0.2);
  overflow: hidden;
}

.modal-head {
  padding: 18px 24px;
  border-bottom: 1px solid #e5e7eb;
  display: flex;
  align-items: center;
  justify-content: space-between;
}

.modal-close {
  background: transparent;
  border: none;
  font-size: 18px;
  cursor: pointer;
}

.add-lead-form {
  padding: 20px 24px;
  display: flex;
  flex-direction: column;
  gap: 12px;
}

.form-row {
  display: grid;
  grid-template-columns: 1fr 1fr;
  gap: 12px;
}

.form-group {
  display: flex;
  flex-direction: column;
  gap: 4px;
}

.form-group label {
  font-size: 11.5px;
  font-weight: 600;
  color: #4b5563;
}

.form-group input,
.form-group select,
.form-group textarea {
  border: 1px solid #d1d5db;
  border-radius: 8px;
  padding: 8px 10px;
  font-size: 13px;
  outline: none;
}

.form-group input:focus,
.form-group select:focus,
.form-group textarea:focus {
  border-color: #0A5C38;
}

.form-actions {
  display: flex;
  justify-content: flex-end;
  gap: 10px;
  margin-top: 10px;
}

/* Button utility */
.btn {
  display: inline-flex;
  align-items: center;
  gap: 6px;
  padding: 8px 14px;
  font-size: 13px;
  font-weight: 600;
  border-radius: 8px;
  cursor: pointer;
  border: none;
  transition: all 0.15s ease;
}

.btn-primary {
  background: #0A5C38;
  color: #ffffff;
}

.btn-primary:hover {
  background: #08492c;
}

.btn-secondary {
  background: #ffffff;
  color: #374151;
  border: 1px solid #d1d5db;
}

.btn-secondary:hover {
  background: #f9fafb;
}

.btn-outline {
  background: transparent;
  border: 1px solid #ef4444;
  color: #ef4444;
}

.btn-outline:hover {
  background: #fef2f2;
}
</style>
