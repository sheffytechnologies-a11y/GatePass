<template>
  <div class="support-page">
    <!-- Header -->
    <section class="page-header">
      <div>
        <div class="eyebrow">Estate Helpdesk &amp; Communications</div>
        <h1 class="page-title">Resident Messages &amp; Support Tickets</h1>
        <p class="page-desc">Triage resident inquiries, resolve reported facility issues, and broadcast updates to your estate community.</p>
      </div>
      <div class="header-actions">
        <button class="btn btn-secondary" @click="showReportModal = true">
          📑 Generate Report
        </button>
        <button class="btn btn-secondary" @click="activeMainTab = 'messages'">
          💬 Messages ({{ unreadMessagesCount }} Unread)
        </button>
        <button class="btn btn-primary" @click="activeMainTab = 'tickets'">
          🎫 Tickets ({{ openTicketsCount }} Open)
        </button>
      </div>
    </section>

    <!-- Top KPI Metrics -->
    <div class="metrics-grid">
      <div class="metric-card">
        <div class="metric-icon metric-icon--amber">⚠️</div>
        <div class="metric-data">
          <div class="metric-val">{{ openTicketsCount }}</div>
          <div class="metric-lbl">Open Tickets</div>
        </div>
      </div>
      <div class="metric-card">
        <div class="metric-icon metric-icon--blue">🛠️</div>
        <div class="metric-data">
          <div class="metric-val">{{ inProgressTicketsCount }}</div>
          <div class="metric-lbl">In Progress</div>
        </div>
      </div>
      <div class="metric-card">
        <div class="metric-icon metric-icon--green">✅</div>
        <div class="metric-data">
          <div class="metric-val">{{ resolvedTicketsCount }}</div>
          <div class="metric-lbl">Resolved Issues</div>
        </div>
      </div>
      <div class="metric-card">
        <div class="metric-icon metric-icon--teal">💬</div>
        <div class="metric-data">
          <div class="metric-val">{{ messagesList.length }}</div>
          <div class="metric-lbl">Total Inquiries</div>
        </div>
      </div>
    </div>

    <!-- Main Navigation Tabs -->
    <div class="nav-tabs-bar">
      <button
        class="nav-tab"
        :class="{ active: activeMainTab === 'tickets' }"
        @click="activeMainTab = 'tickets'"
      >
        <span>Support Tickets</span>
        <span class="tab-badge">{{ ticketsList.length }}</span>
      </button>
      <button
        class="nav-tab"
        :class="{ active: activeMainTab === 'messages' }"
        @click="activeMainTab = 'messages'"
      >
        <span>Resident Messages</span>
        <span class="tab-badge" v-if="unreadMessagesCount > 0">{{ unreadMessagesCount }} new</span>
      </button>
    </div>

    <!-- ── TAB 1: SUPPORT TICKETS ── -->
    <section v-if="activeMainTab === 'tickets'" class="tab-content-section">
      <!-- Filter controls -->
      <div class="filters-toolbar">
        <div class="pills-group">
          <button
            v-for="st in ['All', 'Open', 'In Progress', 'Resolved']"
            :key="st"
            class="pill-btn"
            :class="{ active: ticketFilterStatus === st }"
            @click="ticketFilterStatus = st"
          >
            {{ st }}
          </button>
        </div>
        <div class="search-box">
          <input
            v-model="searchQuery"
            type="text"
            placeholder="Search by title, resident, or ticket #..."
            class="search-input"
          />
        </div>
      </div>

      <!-- Tickets Table / List -->
      <div class="card table-card">
        <div v-if="filteredTickets.length === 0" class="empty-placeholder">
          <div class="empty-icon">🎫</div>
          <div class="empty-title">No tickets match this filter</div>
          <div class="empty-sub">Resident reported faults will show up here in real time.</div>
        </div>

        <div v-else class="tickets-table-wrap">
          <table class="tickets-table">
            <thead>
              <tr>
                <th>Ticket ID</th>
                <th>Resident &amp; Unit</th>
                <th>Issue Title &amp; Category</th>
                <th>Priority</th>
                <th>Status</th>
                <th>Date</th>
                <th>Actions</th>
              </tr>
            </thead>
            <tbody>
              <tr v-for="t in filteredTickets" :key="t.id">
                <td class="cell-id">
                  <span class="code-badge">{{ t.id }}</span>
                </td>
                <td>
                  <div class="resident-cell">
                    <strong>{{ t.residentName }}</strong>
                    <span>{{ t.unit }}</span>
                  </div>
                </td>
                <td class="cell-issue">
                  <div class="issue-title">{{ t.title }}</div>
                  <span class="category-chip">{{ t.category }}</span>
                  <div v-if="t.adminNote" class="admin-reply-snippet">
                    <em>Update: {{ t.adminNote }}</em>
                  </div>
                </td>
                <td>
                  <span class="priority-pill" :class="(t.priority || 'Normal').toLowerCase()">
                    {{ t.priority || 'Normal' }}
                  </span>
                </td>
                <td>
                  <select
                    :value="t.status"
                    @change="updateTicketStatus(t, ($event.target as HTMLSelectElement).value)"
                    class="status-select"
                    :class="t.status.toLowerCase().replace(/\s+/g, '-')"
                  >
                    <option value="Open">Open</option>
                    <option value="In Progress">In Progress</option>
                    <option value="Resolved">Resolved</option>
                  </select>
                </td>
                <td class="cell-date">{{ t.date }}</td>
                <td>
                  <button class="btn btn-sm btn-outline" @click="openTicketDetail(t)">
                    Update
                  </button>
                </td>
              </tr>
            </tbody>
          </table>
        </div>
      </div>
    </section>

    <!-- ── TAB 2: RESIDENT MESSAGES ── -->
    <section v-else class="tab-content-section">
      <div class="card messages-grid-card">
        <div class="messages-list">
          <div
            v-for="msg in messagesList"
            :key="msg.id"
            class="message-item"
            :class="{ 'message-item--active': selectedMessage?.id === msg.id, 'message-item--unread': !msg.reply }"
            @click="selectedMessage = msg"
          >
            <div class="msg-item-top">
              <span class="msg-resident">{{ msg.residentName }}</span>
              <span class="msg-unit">{{ msg.unit }}</span>
            </div>
            <div class="msg-topic-badge">{{ msg.category }}</div>
            <p class="msg-preview">{{ msg.body }}</p>
            <div class="msg-item-bottom">
              <span class="msg-time">{{ msg.time }}</span>
              <span v-if="msg.reply" class="status-replied">✓ Replied</span>
              <span v-else class="status-pending">Needs Reply</span>
            </div>
          </div>
        </div>

        <!-- Selected Message Reader & Reply Composer -->
        <div class="message-reader">
          <div v-if="!selectedMessage" class="no-selection">
            Select a resident message on the left to read and reply.
          </div>
          <div v-else class="reader-content">
            <div class="reader-header">
              <div>
                <h2>{{ selectedMessage.residentName }}</h2>
                <div class="reader-sub">
                  <span>{{ selectedMessage.unit }}</span> &middot;
                  <span>{{ selectedMessage.phone }}</span> &middot;
                  <span>{{ selectedMessage.time }}</span>
                </div>
              </div>
              <span class="topic-pill">{{ selectedMessage.category }}</span>
            </div>

            <div class="reader-body">
              <div class="bubble bubble--resident">
                <div class="bubble-sender">Resident:</div>
                <div class="bubble-text">{{ selectedMessage.body }}</div>
              </div>

              <div v-if="selectedMessage.reply" class="bubble bubble--admin">
                <div class="bubble-sender">Estate Office Response:</div>
                <div class="bubble-text">{{ selectedMessage.reply }}</div>
              </div>
            </div>

            <!-- Reply Box -->
            <div class="reply-box">
              <label>Send Official Reply to Resident:</label>
              <textarea
                v-model="replyText"
                rows="3"
                placeholder="Type response back to resident's app..."
                class="reply-textarea"
              ></textarea>
              <div class="reply-actions">
                <button
                  class="btn btn-primary"
                  :disabled="!replyText.trim()"
                  @click="sendReply"
                >
                  Send Reply
                </button>
              </div>
            </div>
          </div>
        </div>
      </div>
    </section>

    <!-- ── MODAL: TICKET RESOLUTION MODAL ── -->
    <div v-if="activeTicketModal" class="modal-overlay" @click.self="activeTicketModal = null">
      <div class="modal-dialog">
        <div class="modal-header">
          <div>
            <h3>Update Ticket #{{ activeTicketModal.id }}</h3>
            <span class="modal-sub">{{ activeTicketModal.residentName }} &middot; {{ activeTicketModal.unit }}</span>
          </div>
          <button class="close-x-btn" @click="activeTicketModal = null">&times;</button>
        </div>

        <div class="modal-body">
          <div class="ticket-overview">
            <div class="overview-title">{{ activeTicketModal.title }}</div>
            <p class="overview-desc">{{ activeTicketModal.description }}</p>
          </div>

          <div class="form-group">
            <label>Update Status:</label>
            <select v-model="modalStatus" class="form-control">
              <option value="Open">Open</option>
              <option value="In Progress">In Progress</option>
              <option value="Resolved">Resolved</option>
            </select>
          </div>

          <div class="form-group">
            <label>Admin Note / Resolution Update for Resident:</label>
            <textarea
              v-model="modalNote"
              rows="3"
              placeholder="e.g. Electrician scheduled for 2 PM today. Will confirm once fixed."
              class="form-control"
            ></textarea>
          </div>
        </div>

        <div class="modal-footer">
          <button class="btn btn-outline" @click="activeTicketModal = null">Cancel</button>
          <button class="btn btn-primary" @click="saveTicketModal">Save Changes</button>
        </div>
      </div>
    </div>

    <!-- ── MODAL: ESTATE SUPPORT & COMMUNICATIONS REPORT ── -->
    <div v-if="showReportModal" class="modal-overlay" @click.self="showReportModal = false">
      <div class="modal-dialog modal-dialog--lg">
        <div class="modal-header">
          <div>
            <h3>Estate Incident &amp; Communications Report</h3>
            <p>Official summary of resident inquiries, reported faults, and resolution status</p>
          </div>
          <button class="modal-close" @click="showReportModal = false">&times;</button>
        </div>

        <div class="modal-body report-printable">
          <div class="report-meta-box">
            <div class="report-meta-item">
              <span class="meta-label">Estate Office:</span>
              <span class="meta-val">Helpdesk &amp; Resident Services</span>
            </div>
            <div class="report-meta-item">
              <span class="meta-label">Generated Date:</span>
              <span class="meta-val">{{ new Date().toLocaleDateString('en-GB', { day: 'numeric', month: 'short', year: 'numeric' }) }}</span>
            </div>
            <div class="report-meta-item">
              <span class="meta-label">Report ID:</span>
              <span class="meta-val">REP-SUP-{{ Date.now().toString().slice(-6) }}</span>
            </div>
          </div>

          <div class="report-kpi-grid">
            <div class="kpi-box">
              <div class="kpi-num">{{ openTicketsCount }}</div>
              <div class="kpi-title">Open Tickets</div>
            </div>
            <div class="kpi-box">
              <div class="kpi-num">{{ inProgressTicketsCount }}</div>
              <div class="kpi-title">In Progress</div>
            </div>
            <div class="kpi-box">
              <div class="kpi-num">{{ resolvedTicketsCount }}</div>
              <div class="kpi-title">Resolved Issues</div>
            </div>
            <div class="kpi-box">
              <div class="kpi-num">{{ messagesList.length }}</div>
              <div class="kpi-title">Resident Messages</div>
            </div>
          </div>

          <div class="report-table-section">
            <h4>Support Tickets Summary</h4>
            <table class="report-table">
              <thead>
                <tr>
                  <th>Ticket ID</th>
                  <th>Resident &amp; Unit</th>
                  <th>Category</th>
                  <th>Issue Title</th>
                  <th>Priority</th>
                  <th>Status</th>
                  <th>Date</th>
                </tr>
              </thead>
              <tbody>
                <tr v-for="t in ticketsList" :key="t.id">
                  <td><strong>{{ t.id }}</strong></td>
                  <td>{{ t.residentName }} ({{ t.unit }})</td>
                  <td>{{ t.category }}</td>
                  <td>{{ t.title }}</td>
                  <td>{{ t.priority || 'Normal' }}</td>
                  <td>{{ t.status }}</td>
                  <td>{{ t.date }}</td>
                </tr>
              </tbody>
            </table>
          </div>
        </div>

        <div class="modal-footer">
          <button class="btn btn-outline" @click="showReportModal = false">Close</button>
          <button class="btn btn-secondary" @click="exportSupportCSV">
            📥 Download CSV
          </button>
          <button class="btn btn-primary" @click="printSupportReport">
            🖨️ Print / Save PDF
          </button>
        </div>
      </div>
    </div>
  </div>
</template>

<script setup lang="ts">
import { ref, computed } from 'vue'

const activeMainTab = ref<'tickets' | 'messages'>('tickets')
const ticketFilterStatus = ref('All')
const searchQuery = ref('')

const MSG_STORAGE_KEY = 'cp_resident_messages'
const TKT_STORAGE_KEY = 'cp_support_tickets'

interface SupportTicket {
  id: string
  residentName?: string
  unit?: string
  title: string
  category: string
  priority?: string
  status: string
  description: string
  date: string
  adminNote?: string | null
}

interface ResidentMessage {
  id: string
  residentName?: string
  unit?: string
  phone?: string
  category: string
  body: string
  time: string
  reply: string | null
}

function loadSavedTickets(): SupportTicket[] {
  try {
    const saved = localStorage.getItem(TKT_STORAGE_KEY)
    if (saved) return JSON.parse(saved)
  } catch {}
  return [
    {
      id: 'TKT-1048',
      residentName: 'Sheriff Ibram',
      unit: 'Flat 3B • Block 2',
      title: 'Streetlight pole blinking intermittently on Lane 3',
      category: 'Roads & Streetlights',
      priority: 'Normal',
      status: 'In Progress',
      description: 'The pole opposite unit 4B is flickering loudly and cutting out after 9 PM.',
      date: 'Oct 27, 2026',
      adminNote: 'Facility technician scheduled for replacement this afternoon.',
    },
    {
      id: 'TKT-1042',
      residentName: 'Amaka Eze',
      unit: 'House 14 • Palm Avenue',
      title: 'Water pump pressure dropping during morning hours',
      category: 'Water Supply & Plumbing',
      priority: 'High',
      status: 'Open',
      description: 'Borehole pressure severely drops from 6:30 AM to 8:00 AM.',
      date: 'Oct 28, 2026',
      adminNote: null,
    },
    {
      id: 'TKT-1025',
      residentName: 'Folake Adeyemi',
      unit: 'Flat 1A • Block C',
      title: 'Intercom unit not buzzing for incoming visitor pass verification',
      category: 'Gate & Access Intercom',
      priority: 'High',
      status: 'Resolved',
      description: 'Unit intercom at entrance not ringing when security dials.',
      date: 'Oct 20, 2026',
      adminNote: 'Wiring connection restored at distribution board.',
    },
  ]
}

// ── TICKETS STATE ──
const ticketsList = ref<SupportTicket[]>(loadSavedTickets())

function persistTickets() {
  try {
    localStorage.setItem(TKT_STORAGE_KEY, JSON.stringify(ticketsList.value))
  } catch {}
}

const openTicketsCount = computed(() =>
  ticketsList.value.filter((t: SupportTicket) => t.status === 'Open').length
)
const inProgressTicketsCount = computed(() =>
  ticketsList.value.filter((t: SupportTicket) => t.status === 'In Progress').length
)
const resolvedTicketsCount = computed(() =>
  ticketsList.value.filter((t: SupportTicket) => t.status === 'Resolved').length
)

const filteredTickets = computed(() => {
  return ticketsList.value.filter((t: SupportTicket) => {
    const matchStatus = ticketFilterStatus.value === 'All' || t.status === ticketFilterStatus.value
    const q = searchQuery.value.toLowerCase().trim()
    const matchQuery = !q ||
      t.title.toLowerCase().includes(q) ||
      (t.residentName && t.residentName.toLowerCase().includes(q)) ||
      t.id.toLowerCase().includes(q) ||
      t.category.toLowerCase().includes(q)
    return matchStatus && matchQuery
  })
})

function updateTicketStatus(ticket: any, newStatus: string) {
  ticket.status = newStatus
  persistTickets()
}

// ── TICKET MODAL STATE ──
const activeTicketModal = ref<any>(null)
const modalStatus = ref('Open')
const modalNote = ref('')

function openTicketDetail(ticket: any) {
  activeTicketModal.value = ticket
  modalStatus.value = ticket.status
  modalNote.value = ticket.adminNote || ''
}

function saveTicketModal() {
  if (activeTicketModal.value) {
    activeTicketModal.value.status = modalStatus.value
    activeTicketModal.value.adminNote = modalNote.value.trim() || null
    persistTickets()
  }
  activeTicketModal.value = null
}

function loadSavedMessages(): ResidentMessage[] {
  try {
    const saved = localStorage.getItem(MSG_STORAGE_KEY)
    if (saved) return JSON.parse(saved)
  } catch {}
  return [
    {
      id: 'msg-1',
      residentName: 'Sheriff Ibram',
      unit: 'Flat 3B • Block 2',
      phone: '08132489619',
      category: 'Security & Gate Access',
      body: 'Good morning, please inform the gate that moving van for Flat 3 will arrive at 2 PM today.',
      time: 'Yesterday, 10:15 AM',
      reply: 'Acknowledged. Gate officers notified.',
    },
    {
      id: 'msg-2',
      residentName: 'Babatunde Alao',
      unit: 'House 8 • Hibiscus Lane',
      phone: '08024035326',
      category: 'Billing & Levies',
      body: 'Kindly confirm receipt of my October estate security levy paid yesterday via Paystack.',
      time: 'Today, 8:40 AM',
      reply: null,
    },
  ]
}

// ── MESSAGES STATE ──
const messagesList = ref<ResidentMessage[]>(loadSavedMessages())

function persistMessages() {
  try {
    localStorage.setItem(MSG_STORAGE_KEY, JSON.stringify(messagesList.value))
  } catch {}
}

const unreadMessagesCount = computed(() =>
  messagesList.value.filter((m: ResidentMessage) => !m.reply).length
)

const selectedMessage = ref<any>(messagesList.value[1] || messagesList.value[0] || null)
const replyText = ref('')

function sendReply() {
  if (!selectedMessage.value || !replyText.value.trim()) return
  selectedMessage.value.reply = replyText.value.trim()
  persistMessages()
  replyText.value = ''
}

const showReportModal = ref(false)

function exportSupportCSV() {
  const headers = ['Ticket ID', 'Resident Name', 'Unit', 'Category', 'Title', 'Priority', 'Status', 'Date']
  const rows = ticketsList.value.map((t: SupportTicket) => [
    t.id,
    `"${(t.residentName || '').replace(/"/g, '""')}"`,
    `"${(t.unit || '').replace(/"/g, '""')}"`,
    `"${t.category}"`,
    `"${t.title.replace(/"/g, '""')}"`,
    t.priority || 'Normal',
    t.status,
    `"${t.date}"`,
  ])
  const csvContent = 'data:text/csv;charset=utf-8,' + [headers.join(','), ...rows.map(e => e.join(','))].join('\n')
  const encodedUri = encodeURI(csvContent)
  const link = document.createElement('a')
  link.setAttribute('href', encodedUri)
  link.setAttribute('download', `Estate_Support_Report_${new Date().toISOString().slice(0, 10)}.csv`)
  document.body.appendChild(link)
  link.click()
  document.body.removeChild(link)
}

function printSupportReport() {
  window.print()
}
</script>

<style scoped>
.support-page {
  display: flex;
  flex-direction: column;
  gap: 24px;
}

.page-header {
  display: flex;
  align-items: flex-start;
  justify-content: space-between;
  gap: 16px;
  flex-wrap: wrap;
}
.eyebrow {
  font-size: 11px;
  font-weight: 700;
  text-transform: uppercase;
  letter-spacing: 0.08em;
  color: var(--c-primary, #0A5C38);
  margin-bottom: 4px;
}
.page-title {
  font-size: 24px;
  font-weight: 800;
  color: #112019;
  letter-spacing: -0.02em;
  margin: 0 0 6px;
}
.page-desc {
  font-size: 13.5px;
  color: #64746C;
  max-width: 650px;
  margin: 0;
}
.header-actions {
  display: flex;
  gap: 10px;
}

/* Metrics */
.metrics-grid {
  display: grid;
  grid-template-columns: repeat(auto-fit, minmax(200px, 1fr));
  gap: 14px;
}
.metric-card {
  background: #FFFFFF;
  border: 1px solid var(--c-border, #E5EAE7);
  border-radius: 14px;
  padding: 16px;
  display: flex;
  align-items: center;
  gap: 14px;
}
.metric-icon {
  width: 44px;
  height: 44px;
  border-radius: 12px;
  display: flex;
  align-items: center;
  justify-content: center;
  font-size: 20px;
}
.metric-icon--amber { background: #FEF3C7; }
.metric-icon--blue  { background: #E0F2FE; }
.metric-icon--green { background: #DCFCE7; }
.metric-icon--teal  { background: #E6F4EA; }
.metric-val {
  font-size: 22px;
  font-weight: 800;
  color: #112019;
  line-height: 1;
}
.metric-lbl {
  font-size: 12px;
  color: #64746C;
  font-weight: 600;
  margin-top: 4px;
}

/* Nav Tabs */
.nav-tabs-bar {
  display: flex;
  border-bottom: 1.5px solid var(--c-border, #E5EAE7);
  gap: 20px;
}
.nav-tab {
  background: none;
  border: none;
  padding: 0 0 12px;
  font-size: 14.5px;
  font-weight: 600;
  color: #64746C;
  cursor: pointer;
  display: flex;
  align-items: center;
  gap: 8px;
  position: relative;
  transition: color 0.15s ease;
}
.nav-tab.active {
  color: var(--c-primary, #0A5C38);
  font-weight: 700;
}
.nav-tab.active::after {
  content: '';
  position: absolute;
  bottom: -1.5px;
  left: 0;
  right: 0;
  height: 2.5px;
  background: var(--c-primary, #0A5C38);
  border-radius: 999px;
}
.tab-badge {
  font-size: 11px;
  font-weight: 700;
  padding: 2px 7px;
  border-radius: 999px;
  background: #E8F5EE;
  color: #0A5C38;
}

/* Filters */
.filters-toolbar {
  display: flex;
  align-items: center;
  justify-content: space-between;
  gap: 14px;
  flex-wrap: wrap;
  margin-bottom: 14px;
}
.pills-group {
  display: flex;
  gap: 6px;
}
.pill-btn {
  border: 1px solid var(--c-border, #E5EAE7);
  border-radius: 999px;
  background: #FFFFFF;
  padding: 6px 14px;
  font-size: 12.5px;
  font-weight: 600;
  color: #556B60;
  cursor: pointer;
}
.pill-btn.active {
  background: var(--c-primary, #0A5C38);
  border-color: var(--c-primary, #0A5C38);
  color: #FFFFFF;
}
.search-input {
  border: 1.5px solid var(--c-border, #E5EAE7);
  border-radius: 10px;
  padding: 8px 14px;
  font-size: 13px;
  width: 260px;
  outline: none;
}
.search-input:focus {
  border-color: var(--c-primary, #0A5C38);
}

/* Table */
.card {
  background: #FFFFFF;
  border: 1px solid var(--c-border, #E5EAE7);
  border-radius: 16px;
  overflow: hidden;
}
.tickets-table-wrap {
  overflow-x: auto;
}
.tickets-table {
  width: 100%;
  border-collapse: collapse;
  text-align: left;
  font-size: 13px;
}
.tickets-table th {
  background: #F8FAF9;
  padding: 12px 16px;
  font-weight: 700;
  color: #4A5850;
  border-bottom: 1px solid var(--c-border, #E5EAE7);
}
.tickets-table td {
  padding: 14px 16px;
  border-bottom: 1px solid #F1F4F2;
  vertical-align: middle;
}
.code-badge {
  font-family: monospace;
  font-weight: 700;
  color: #0A5C38;
  background: #E8F5EE;
  padding: 3px 6px;
  border-radius: 6px;
}
.resident-cell {
  display: flex;
  flex-direction: column;
  gap: 2px;
}
.resident-cell span {
  font-size: 11.5px;
  color: #6C7A72;
}
.cell-issue {
  max-width: 280px;
}
.issue-title {
  font-weight: 700;
  color: #112019;
}
.category-chip {
  display: inline-block;
  font-size: 10.5px;
  color: #6C7A72;
  background: #F1F4F2;
  padding: 1px 6px;
  border-radius: 4px;
  margin-top: 3px;
}
.admin-reply-snippet {
  font-size: 11.5px;
  color: #0A5C38;
  margin-top: 3px;
}
.priority-pill {
  font-size: 10.5px;
  font-weight: 700;
  padding: 2px 7px;
  border-radius: 999px;
  text-transform: uppercase;
}
.priority-pill.normal { background: #E0F2FE; color: #0369A1; }
.priority-pill.high { background: #FEF3C7; color: #B45309; }
.priority-pill.urgent { background: #FEE2E2; color: #B91C1C; }

.status-select {
  border-radius: 999px;
  padding: 4px 10px;
  font-size: 12px;
  font-weight: 700;
  border: 1px solid transparent;
  cursor: pointer;
  outline: none;
}
.status-select.open { background: #FEF3C7; color: #B45309; }
.status-select.in-progress { background: #E0F2FE; color: #0369A1; }
.status-select.resolved { background: #DCFCE7; color: #15803D; }

/* Messages layout */
.messages-grid-card {
  display: grid;
  grid-template-columns: 340px 1fr;
  min-height: 520px;
}
.messages-list {
  border-right: 1px solid var(--c-border, #E5EAE7);
  overflow-y: auto;
  max-height: 600px;
}
.message-item {
  padding: 14px 16px;
  border-bottom: 1px solid #F1F4F2;
  cursor: pointer;
  display: flex;
  flex-direction: column;
  gap: 4px;
  transition: background 0.15s ease;
}
.message-item:hover {
  background: #F8FAF9;
}
.message-item--active {
  background: #EAF4EE !important;
  border-left: 3px solid #0A5C38;
}
.msg-item-top {
  display: flex;
  justify-content: space-between;
  font-size: 13px;
  font-weight: 700;
}
.msg-unit {
  font-size: 11px;
  color: #6C7A72;
  font-weight: normal;
}
.msg-topic-badge {
  font-size: 10.5px;
  color: #0A5C38;
  font-weight: 600;
}
.msg-preview {
  font-size: 12.5px;
  color: #4A5850;
  margin: 2px 0 0;
  display: -webkit-box;
  -webkit-line-clamp: 2;
  -webkit-box-orient: vertical;
  overflow: hidden;
}
.msg-item-bottom {
  display: flex;
  justify-content: space-between;
  font-size: 11px;
  color: #8C9991;
  margin-top: 4px;
}
.status-replied { color: #0A5C38; font-weight: 700; }
.status-pending { color: #D97706; font-weight: 700; }

.message-reader {
  padding: 24px;
  display: flex;
  flex-direction: column;
  background: #FFFFFF;
}
.no-selection {
  margin: auto;
  color: #8C9991;
  font-size: 14px;
}
.reader-content {
  display: flex;
  flex-direction: column;
  height: 100%;
}
.reader-header {
  display: flex;
  align-items: flex-start;
  justify-content: space-between;
  border-bottom: 1px solid #EAEFEA;
  padding-bottom: 14px;
}
.reader-header h2 {
  font-size: 18px;
  font-weight: 800;
  margin: 0;
}
.reader-sub {
  font-size: 12px;
  color: #6C7A72;
  margin-top: 3px;
}
.topic-pill {
  font-size: 11px;
  font-weight: 700;
  background: #E8F5EE;
  color: #0A5C38;
  padding: 3px 8px;
  border-radius: 999px;
}
.reader-body {
  flex: 1;
  padding: 20px 0;
  display: flex;
  flex-direction: column;
  gap: 14px;
  overflow-y: auto;
}
.bubble {
  border-radius: 12px;
  padding: 14px;
  max-width: 80%;
  display: flex;
  flex-direction: column;
  gap: 4px;
}
.bubble--resident {
  background: #F1F4F2;
  align-self: flex-start;
}
.bubble--admin {
  background: #E8F5EE;
  border: 1px solid rgba(10, 92, 56, 0.2);
  align-self: flex-end;
}
.bubble-sender {
  font-size: 11px;
  font-weight: 700;
  color: #6C7A72;
  text-transform: uppercase;
}
.bubble-text {
  font-size: 13.5px;
  color: #112019;
  line-height: 1.45;
}

.reply-box {
  border-top: 1px solid #EAEFEA;
  padding-top: 14px;
  display: flex;
  flex-direction: column;
  gap: 8px;
}
.reply-box label {
  font-size: 12px;
  font-weight: 700;
  color: #4A5850;
}
.reply-textarea {
  border: 1.5px solid #DCE6E0;
  border-radius: 10px;
  padding: 10px 12px;
  font-size: 13.5px;
  outline: none;
  font-family: inherit;
}
.reply-textarea:focus {
  border-color: #0A5C38;
}
.reply-actions {
  display: flex;
  justify-content: flex-end;
}

/* Modal */
.modal-overlay {
  position: fixed;
  inset: 0;
  background: rgba(0, 0, 0, 0.5);
  display: flex;
  align-items: center;
  justify-content: center;
  z-index: 1000;
  padding: 20px;
}
.modal-dialog {
  background: #FFFFFF;
  border-radius: 16px;
  width: 100%;
  max-width: 500px;
  overflow: hidden;
  box-shadow: 0 10px 30px rgba(0,0,0,0.2);
}
.modal-header {
  padding: 18px 20px;
  border-bottom: 1px solid #EAEFEA;
  display: flex;
  align-items: center;
  justify-content: space-between;
}
.modal-header h3 {
  margin: 0;
  font-size: 17px;
  font-weight: 800;
}
.modal-sub {
  font-size: 12px;
  color: #6C7A72;
}
.close-x-btn {
  background: none;
  border: none;
  font-size: 22px;
  cursor: pointer;
  color: #6C7A72;
}
.modal-body {
  padding: 20px;
  display: flex;
  flex-direction: column;
  gap: 14px;
}
.ticket-overview {
  background: #F8FAF9;
  border: 1px solid #EAEFEA;
  border-radius: 10px;
  padding: 12px;
}
.overview-title {
  font-size: 13.5px;
  font-weight: 700;
  color: #112019;
}
.overview-desc {
  font-size: 12px;
  color: #556B60;
  margin: 4px 0 0;
}
.form-group {
  display: flex;
  flex-direction: column;
  gap: 6px;
}
.form-group label {
  font-size: 12.5px;
  font-weight: 700;
  color: #24352D;
}
.form-control {
  border: 1.5px solid #DCE6E0;
  border-radius: 8px;
  padding: 8px 12px;
  font-size: 13px;
  outline: none;
  font-family: inherit;
}
.form-control:focus {
  border-color: #0A5C38;
}
.modal-footer {
  padding: 14px 20px;
  border-top: 1px solid #EAEFEA;
  background: #F8FAF9;
  display: flex;
  justify-content: flex-end;
  gap: 10px;
}

/* Button styles */
.btn {
  border: none;
  border-radius: 10px;
  font-weight: 700;
  font-size: 13px;
  padding: 8px 16px;
  cursor: pointer;
  transition: all 0.15s ease;
  display: inline-flex;
  align-items: center;
  justify-content: center;
  gap: 6px;
}
.btn-primary {
  background: var(--c-primary, #0A5C38);
  color: #FFFFFF;
}
.btn-secondary {
  background: #E8F5EE;
  color: #0A5C38;
}
.btn-outline {
  background: #FFFFFF;
  border: 1px solid var(--c-border, #E5EAE7);
  color: #4A5850;
}
.btn-sm {
  padding: 5px 10px;
  font-size: 11.5px;
  border-radius: 6px;
}
.btn:disabled {
  opacity: 0.5;
  cursor: not-allowed;
}

.empty-placeholder {
  text-align: center;
  padding: 48px 20px;
  color: #6C7A72;
}
.empty-icon {
  font-size: 36px;
  margin-bottom: 8px;
}
.empty-title {
  font-size: 16px;
  font-weight: 700;
  color: #112019;
}
.empty-sub {
  font-size: 13px;
  margin-top: 4px;
}

/* ── Report Modal Styles ── */
.modal-dialog--lg {
  max-width: 820px;
}
.report-meta-box {
  display: grid;
  grid-template-columns: repeat(3, 1fr);
  gap: 12px;
  background: #F8FAF9;
  padding: 14px 18px;
  border-radius: 12px;
  border: 1px solid #EAEFEA;
  margin-bottom: 20px;
}
.report-meta-item {
  display: flex;
  flex-direction: column;
  gap: 2px;
}
.meta-label {
  font-size: 11px;
  text-transform: uppercase;
  color: #6C7A72;
  font-weight: 600;
}
.meta-val {
  font-size: 13.5px;
  font-weight: 700;
  color: #112019;
}
.report-kpi-grid {
  display: grid;
  grid-template-columns: repeat(4, 1fr);
  gap: 12px;
  margin-bottom: 24px;
}
.kpi-box {
  background: #FFFFFF;
  border: 1px solid #EAEFEA;
  border-radius: 12px;
  padding: 12px;
  text-align: center;
}
.kpi-num {
  font-size: 20px;
  font-weight: 800;
  color: #0A5C38;
}
.kpi-title {
  font-size: 11.5px;
  color: #6C7A72;
  margin-top: 3px;
  font-weight: 600;
}
.report-table-section h4 {
  font-size: 14px;
  font-weight: 700;
  color: #112019;
  margin: 0 0 10px;
}
.report-table {
  width: 100%;
  border-collapse: collapse;
  font-size: 12.5px;
  border: 1px solid #EAEFEA;
}
.report-table th {
  background: #F1F5F3;
  padding: 8px 12px;
  font-size: 11px;
  font-weight: 700;
  color: #374151;
  text-align: left;
  border-bottom: 1px solid #EAEFEA;
}
.report-table td {
  padding: 10px 12px;
  border-bottom: 1px solid #EAEFEA;
  color: #1F2937;
}
</style>
