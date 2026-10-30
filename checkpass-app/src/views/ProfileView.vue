<template>
  <ion-page>
    <ion-header class="ion-no-border">
      <ion-toolbar>
        <ion-title>Profile</ion-title>
      </ion-toolbar>
    </ion-header>

    <ion-content :fullscreen="true" class="profile-content">
      <div class="content-pad">

        <!-- Profile card -->
        <div class="profile-card">
          <div class="profile-avatar">{{ initials }}</div>
          <div class="profile-info">
            <div class="profile-name">{{ auth.resident?.name }}</div>
            <div class="profile-unit">{{ auth.resident?.flatAddress }}</div>
            <div class="profile-estate">{{ auth.resident?.estateName }}</div>
          </div>
        </div>

        <!-- Push disabled banner -->
        <div v-if="pushDenied" class="push-banner">
          <ion-icon :icon="notificationsOffOutline" />
          Notifications are disabled.
          <button class="settings-link" @click="openSettings">Enable in Settings</button>
        </div>

        <div class="menu-section">
          <div class="menu-section-label">Account</div>
          <div class="menu-list">
            <button class="menu-row" @click="editProfile">
              <ion-icon :icon="personOutline" class="menu-icon" />
              <span class="menu-label">Edit Profile</span>
              <ion-icon :icon="chevronForwardOutline" class="menu-chevron" />
            </button>
            <button class="menu-row" @click="changePassword">
              <ion-icon :icon="lockClosedOutline" class="menu-icon" />
              <span class="menu-label">Change Password</span>
              <ion-icon :icon="chevronForwardOutline" class="menu-chevron" />
            </button>
          </div>
        </div>

        <!-- Estate Office & Support -->
        <div class="menu-section">
          <div class="menu-section-label">Estate Office &amp; Helpdesk</div>
          <div class="menu-list">
            <!-- 1. Send Message to Estate Admin -->
            <button class="menu-row" @click="showMessageModal = true">
              <div class="menu-icon-box menu-icon-box--green">
                <ion-icon :icon="chatbubbleEllipsesOutline" class="menu-icon" />
              </div>
              <div class="menu-text-col">
                <span class="menu-label">Message Estate Admin</span>
                <span class="menu-sublabel">Direct inquiries, feedback &amp; clearance requests</span>
              </div>
              <ion-icon :icon="chevronForwardOutline" class="menu-chevron" />
            </button>

            <!-- 2. Support Center & Tickets -->
            <button class="menu-row" @click="showSupportModal = true">
              <div class="menu-icon-box menu-icon-box--blue">
                <ion-icon :icon="ticketOutline" class="menu-icon" />
              </div>
              <div class="menu-text-col">
                <span class="menu-label">Support Center &amp; Issues</span>
                <span class="menu-sublabel">Report facility faults &amp; track tickets</span>
              </div>
              <span v-if="openTicketsCount > 0" class="badge-open-count">{{ openTicketsCount }} Open</span>
              <ion-icon :icon="chevronForwardOutline" class="menu-chevron" />
            </button>
          </div>
        </div>

        <!-- Preferences -->
        <div class="menu-section">
          <div class="menu-section-label">Preferences</div>
          <div class="menu-list">
            <div class="menu-row no-tap">
              <ion-icon :icon="notificationsOutline" class="menu-icon" />
              <span class="menu-label">Push Notifications</span>
              <ion-toggle v-model="notifEnabled" color="primary" />
            </div>
            <div class="menu-row no-tap">
              <ion-icon :icon="enterOutline" class="menu-icon" />
              <span class="menu-label">Visitor Arrival Alerts</span>
              <ion-toggle v-model="arrivalAlerts" color="primary" />
            </div>
            <div class="menu-row no-tap">
              <ion-icon :icon="timeOutline" class="menu-icon" />
              <span class="menu-label">Pass Expiry Alerts</span>
              <ion-toggle v-model="expiryAlerts" color="primary" />
            </div>
          </div>
        </div>

        <!-- Support -->
        <div class="menu-section">
          <div class="menu-section-label">Help &amp; Legal</div>
          <div class="menu-list">
            <button class="menu-row" @click="showFaq">
              <ion-icon :icon="helpCircleOutline" class="menu-icon" />
              <span class="menu-label">FAQ</span>
              <ion-icon :icon="chevronForwardOutline" class="menu-chevron" />
            </button>
            <button class="menu-row" @click="showPrivacy">
              <ion-icon :icon="shieldCheckmarkOutline" class="menu-icon" />
              <span class="menu-label">Privacy Policy</span>
              <ion-icon :icon="chevronForwardOutline" class="menu-chevron" />
            </button>
          </div>
        </div>

        <ion-button expand="block" fill="outline" color="danger" @click="handleLogout" class="logout-btn">
          Log Out
        </ion-button>

        <div class="version-label">Checkpass v1.0.0 &middot; {{ auth.resident?.estateName || 'Estate Access' }}</div>

      </div>

      <!-- ── MODAL 1: MESSAGE ESTATE ADMIN ── -->
      <ion-modal :is-open="showMessageModal" @didDismiss="showMessageModal = false">
        <div class="sheet-modal-shell">
          <div class="sheet-modal-header">
            <div>
              <h2>Message Estate Office</h2>
              <p>{{ auth.resident?.estateName || 'Estate Management' }}</p>
            </div>
            <button class="modal-close-btn" @click="showMessageModal = false">&times;</button>
          </div>

          <div class="sheet-modal-body">
            <!-- Office Quick Action Hotlines -->
            <div class="office-hotline-card">
              <div class="hotline-title">Need immediate assistance?</div>
              <div class="hotline-btns">
                <a :href="`tel:08000000000`" class="hotline-btn">
                  <ion-icon :icon="callOutline" />
                  <span>Call Admin Desk</span>
                </a>
                <a :href="`https://wa.me/2348132489619?text=Hello%20Estate%20Office,%20inquiry%20from%20${encodeURIComponent(auth.resident?.name || 'Resident')}%20(${encodeURIComponent(auth.resident?.flatAddress || 'Unit')})`" target="_blank" class="hotline-btn hotline-btn--green">
                  <span>WhatsApp Desk</span>
                </a>
              </div>
            </div>

            <!-- Message Form -->
            <div class="modal-form-section">
              <label class="form-field-label">Topic / Department</label>
              <select v-model="messageForm.category" class="custom-select">
                <option value="Security & Gate Access">Security &amp; Gate Access</option>
                <option value="Maintenance & Power">Maintenance &amp; Utilities</option>
                <option value="Billing & Levies">Billing &amp; Levy Inquiry</option>
                <option value="General Feedback">General Inquiry &amp; Suggestion</option>
              </select>

              <label class="form-field-label">Your Message</label>
              <textarea
                v-model="messageForm.body"
                rows="4"
                placeholder="Type your message to the estate management..."
                class="custom-textarea"
              ></textarea>

              <button
                class="submit-action-btn"
                :disabled="!messageForm.body.trim() || sendingMessage"
                @click="submitMessage"
              >
                <span>{{ sendingMessage ? 'Sending...' : 'Send Message' }}</span>
              </button>
            </div>

            <!-- Sent Messages History -->
            <div v-if="sentMessages.length > 0" class="history-block">
              <div class="history-title">Recent Conversations</div>
              <div v-for="m in sentMessages" :key="m.id" class="message-card">
                <div class="message-card-top">
                  <span class="message-cat-badge">{{ m.category }}</span>
                  <span class="message-time">{{ m.time }}</span>
                </div>
                <div class="message-card-body">{{ m.body }}</div>
                <div v-if="m.reply" class="message-card-reply">
                  <strong>Office Reply:</strong> {{ m.reply }}
                </div>
                <div v-else class="message-card-pending">
                  <em>Awaiting admin review</em>
                </div>
              </div>
            </div>
          </div>
        </div>
      </ion-modal>

      <!-- ── MODAL 2: SUPPORT CENTER & TICKETS ── -->
      <ion-modal :is-open="showSupportModal" @didDismiss="showSupportModal = false">
        <div class="sheet-modal-shell">
          <div class="sheet-modal-header">
            <div>
              <h2>Support &amp; Issue Tickets</h2>
              <p>Report faults &amp; monitor resolution</p>
            </div>
            <button class="modal-close-btn" @click="showSupportModal = false">&times;</button>
          </div>

          <!-- Sub-tabs: My Tickets vs Raise Ticket -->
          <div class="support-subtabs">
            <button
              class="support-tab-btn"
              :class="{ active: activeSupportTab === 'list' }"
              @click="activeSupportTab = 'list'"
            >
              My Tickets ({{ ticketsList.length }})
            </button>
            <button
              class="support-tab-btn"
              :class="{ active: activeSupportTab === 'new' }"
              @click="activeSupportTab = 'new'"
            >
              + Raise Ticket
            </button>
          </div>

          <div class="sheet-modal-body">
            <!-- LIST TAB -->
            <div v-if="activeSupportTab === 'list'">
              <!-- Status filter row -->
              <div class="ticket-filter-pills">
                <button
                  v-for="st in ['All', 'Open', 'In Progress', 'Resolved']"
                  :key="st"
                  class="filter-pill"
                  :class="{ active: ticketFilter === st }"
                  @click="ticketFilter = st"
                >
                  {{ st }}
                </button>
              </div>

              <div v-if="filteredTickets.length === 0" class="ticket-empty-wrap">
                <EmptyState
                  icon="🎫"
                  heading="No tickets found"
                  body="You have no tickets in this status. Raise a ticket whenever you notice an estate fault."
                  cta-label="+ Raise a Ticket"
                  @cta="activeSupportTab = 'new'"
                />
              </div>

              <div v-else class="tickets-stack">
                <div v-for="t in filteredTickets" :key="t.id" class="ticket-card">
                  <div class="ticket-top-row">
                    <span class="ticket-number">{{ t.id }}</span>
                    <span class="ticket-status-pill" :class="t.status.toLowerCase().replace(/\s+/g, '-')">
                      {{ t.status }}
                    </span>
                  </div>
                  <div class="ticket-title">{{ t.title }}</div>
                  <div class="ticket-desc">{{ t.description }}</div>
                  <div class="ticket-meta-row">
                    <span class="ticket-cat">{{ t.category }}</span>
                    <span class="ticket-date">{{ t.date }}</span>
                  </div>
                  <div v-if="t.adminNote" class="ticket-admin-update">
                    <span class="admin-note-label">Admin Update:</span>
                    <span>{{ t.adminNote }}</span>
                  </div>
                </div>
              </div>
            </div>

            <!-- RAISE TICKET TAB -->
            <div v-else class="modal-form-section">
              <label class="form-field-label">Fault / Issue Title*</label>
              <input
                v-model="newTicket.title"
                type="text"
                placeholder="e.g. Streetlight faulty on Lane 2 opposite Flat 3"
                class="custom-input"
              />

              <label class="form-field-label">Category</label>
              <select v-model="newTicket.category" class="custom-select">
                <option value="Power / Generator">Power / Generator</option>
                <option value="Water Supply & Plumbing">Water Supply &amp; Plumbing</option>
                <option value="Roads & Streetlights">Roads &amp; Streetlights</option>
                <option value="Gate & Access Intercom">Gate &amp; Access Intercom</option>
                <option value="Waste Disposal">Waste Disposal</option>
                <option value="Security / Disturbance">Security / Disturbance</option>
                <option value="Other">Other Estate Issue</option>
              </select>

              <label class="form-field-label">Priority</label>
              <div class="priority-options">
                <button
                  v-for="p in ['Normal', 'High', 'Urgent']"
                  :key="p"
                  type="button"
                  class="priority-chip"
                  :class="[p.toLowerCase(), { active: newTicket.priority === p }]"
                  @click="newTicket.priority = p"
                >
                  {{ p }}
                </button>
              </div>

              <label class="form-field-label">Detailed Description*</label>
              <textarea
                v-model="newTicket.description"
                rows="4"
                placeholder="Describe the issue, exact location, and how it impacts access or living in the estate..."
                class="custom-textarea"
              ></textarea>

              <button
                class="submit-action-btn"
                :disabled="!newTicket.title.trim() || !newTicket.description.trim() || submittingTicket"
                @click="submitTicket"
              >
                <span>{{ submittingTicket ? 'Submitting Ticket...' : 'Submit Ticket to Estate Admin' }}</span>
              </button>
            </div>
          </div>
        </div>
      </ion-modal>

    </ion-content>
  </ion-page>
</template>

<script setup lang="ts">
import { ref, computed, watch } from 'vue'
import { useRouter } from 'vue-router'
import {
  IonPage, IonHeader, IonToolbar, IonTitle, IonContent, IonButton,
  IonIcon, IonToggle, IonModal, alertController,
} from '@ionic/vue'
import {
  personOutline, lockClosedOutline, notificationsOutline, notificationsOffOutline,
  enterOutline, timeOutline, helpCircleOutline, shieldCheckmarkOutline,
  chevronForwardOutline, chatbubbleEllipsesOutline, ticketOutline, callOutline,
} from 'ionicons/icons'
import EmptyState from '@/components/EmptyState.vue'
import { useAuthStore } from '@/stores/auth'
import { useToast } from '@/composables/useToast'
import { authApi } from '@/api/auth'
import client from '@/api/client'

const router = useRouter()
const auth   = useAuthStore()
const { showToast } = useToast()

const pushDenied   = ref(false)
const notifEnabled = ref(auth.resident?.pushEnabled ?? true)
const arrivalAlerts= ref(true)
const expiryAlerts = ref(true)

// ── ESTATE ADMIN MESSAGING STATE ──
const showMessageModal = ref(false)
const sendingMessage   = ref(false)
const messageForm      = ref({
  category: 'Security & Gate Access',
  body: '',
})
interface ResidentMessage {
  id: string
  category: string
  body: string
  time: string
  reply: string | null
}

const MSG_STORAGE_KEY = 'cp_resident_messages'
const TKT_STORAGE_KEY = 'cp_support_tickets'

function loadSavedMessages(): ResidentMessage[] {
  try {
    const saved = localStorage.getItem(MSG_STORAGE_KEY)
    if (saved) return JSON.parse(saved)
  } catch {}
  return [
    {
      id: 'msg-1',
      category: 'Security & Gate Access',
      body: 'Good morning, please inform the gate that moving van for Flat 3 will arrive at 2 PM today.',
      time: 'Yesterday, 10:15 AM',
      reply: 'Acknowledged. Gate officers notified.',
    },
  ]
}

const sentMessages = ref<ResidentMessage[]>(loadSavedMessages())

async function submitMessage() {
  if (!messageForm.value.body.trim()) return
  sendingMessage.value = true
  try {
    await new Promise(r => setTimeout(r, 600)) // smooth simulated network dispatch
    sentMessages.value.unshift({
      id: `msg-${Date.now()}`,
      category: messageForm.value.category,
      body: messageForm.value.body.trim(),
      time: 'Just now',
      reply: null,
    })
    try {
      localStorage.setItem(MSG_STORAGE_KEY, JSON.stringify(sentMessages.value))
    } catch {}
    messageForm.value.body = ''
    showToast('Message sent to Estate Office.', 'success')
  } catch {
    showToast('Failed to send message. Try again.', 'error')
  } finally {
    sendingMessage.value = false
  }
}

// ── SUPPORT CENTER & TICKETS STATE ──
const showSupportModal = ref(false)
const activeSupportTab = ref<'list' | 'new'>('list')
const ticketFilter     = ref('All')
const submittingTicket = ref(false)

const newTicket = ref({
  title: '',
  category: 'Power / Generator',
  priority: 'Normal',
  description: '',
})

interface SupportTicketItem {
  id: string
  title: string
  category: string
  priority: string
  status: string
  description: string
  date: string
  adminNote?: string | null
}

function loadSavedTickets(): SupportTicketItem[] {
  try {
    const saved = localStorage.getItem(TKT_STORAGE_KEY)
    if (saved) return JSON.parse(saved)
  } catch {}
  return [
    {
      id: 'TKT-1048',
      title: 'Streetlight pole blinking intermittently on Lane 3',
      category: 'Roads & Streetlights',
      priority: 'Normal',
      status: 'In Progress',
      description: 'The pole opposite unit 4B is flickering loudly and cutting out after 9 PM.',
      date: 'Oct 27, 2026',
      adminNote: 'Facility technician scheduled for replacement this afternoon.',
    },
    {
      id: 'TKT-1025',
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

const ticketsList = ref<SupportTicketItem[]>(loadSavedTickets())

const openTicketsCount = computed(() =>
  ticketsList.value.filter((t: SupportTicketItem) => t.status === 'Open' || t.status === 'In Progress').length
)

const filteredTickets = computed(() => {
  if (ticketFilter.value === 'All') return ticketsList.value
  return ticketsList.value.filter((t: SupportTicketItem) => t.status.toLowerCase() === ticketFilter.value.toLowerCase())
})

async function submitTicket() {
  if (!newTicket.value.title.trim() || !newTicket.value.description.trim()) return
  submittingTicket.value = true
  try {
    await new Promise(r => setTimeout(r, 700))
    const randomNum = Math.floor(1000 + Math.random() * 9000)
    ticketsList.value.unshift({
      id: `TKT-${randomNum}`,
      title: newTicket.value.title.trim(),
      category: newTicket.value.category,
      priority: newTicket.value.priority,
      status: 'Open',
      description: newTicket.value.description.trim(),
      date: 'Today',
      adminNote: 'Ticket logged. Awaiting estate officer dispatch.',
    })
    try {
      localStorage.setItem(TKT_STORAGE_KEY, JSON.stringify(ticketsList.value))
    } catch {}
    newTicket.value = {
      title: '',
      category: 'Power / Generator',
      priority: 'Normal',
      description: '',
    }
    activeSupportTab.value = 'list'
    ticketFilter.value = 'All'
    showToast('Support ticket logged successfully!', 'success')
  } catch {
    showToast('Failed to submit ticket.', 'error')
  } finally {
    submittingTicket.value = false
  }
}

async function showFaq() {
  const alert = await alertController.create({
    header: 'Frequently Asked Questions',
    subHeader: 'Resident Help',
    message: '1. How do visitor passes work?\nCreate a pass in the app and share the link/code with your guest for gate entry.\n\n2. Who manages my account?\nYour estate administrative office provisions units and issues resident credentials.',
    buttons: ['OK'],
  })
  await alert.present()
}

async function showPrivacy() {
  const alert = await alertController.create({
    header: 'Privacy & Security',
    message: 'Checkpass stores encrypted pass records strictly for estate perimeter safety and audit clearance. Data is never sold or shared with 3rd parties.',
    buttons: ['Got it'],
  })
  await alert.present()
}

const initials = computed(() =>
  auth.resident?.name?.split(' ').map(w => w[0]).slice(0,2).join('').toUpperCase() || ''
)

async function savePreferences() {
  try {
    await client.patch('/v1/profile/preferences', {
      pushEnabled: notifEnabled.value,
      arrivalAlerts: arrivalAlerts.value,
      expiryAlerts: expiryAlerts.value,
    })
  } catch {}
}

watch([notifEnabled, arrivalAlerts, expiryAlerts], savePreferences)

function openSettings() {
  showToast('Opening settings…', 'success')
}

async function editProfile() {
  const alert = await alertController.create({
    header: 'Edit Profile',
    inputs: [
      { name: 'name', type: 'text', value: auth.resident?.name, placeholder: 'Full name' },
    ],
    buttons: [
      { text: 'Cancel', role: 'cancel' },
      {
        text: 'Save',
        handler: async (data) => {
          if (!data.name?.trim()) return false
          try {
            const res = await client.patch('/v1/profile', { name: data.name.trim() })
            if (res.data.resident) {
              auth.updateResident({ ...auth.resident!, ...res.data.resident })
            }
            showToast('Profile updated', 'success')
          } catch {
            showToast('Failed to update profile.', 'error')
          }
        },
      },
    ],
  })
  await alert.present()
}

async function changePassword() {
  const alert = await alertController.create({
    header: 'Change Password',
    inputs: [
      { name: 'current', type: 'password', placeholder: 'Current password' },
      { name: 'new',     type: 'password', placeholder: 'New password' },
      { name: 'confirm', type: 'password', placeholder: 'Confirm new password' },
    ],
    buttons: [
      { text: 'Cancel', role: 'cancel' },
      {
        text: 'Change',
        handler: async (data) => {
          if (!data.current || !data.new) {
            showToast('Please fill all fields.', 'error')
            return false
          }
          if (data.new !== data.confirm) {
            showToast('Passwords do not match.', 'error')
            return false
          }
          try {
            await authApi.changePassword(data.current, data.new)
            showToast('Password changed successfully.', 'success')
          } catch (err: any) {
            showToast(err.response?.data?.message || 'Failed to change password.', 'error')
            return false
          }
        },
      },
    ],
  })
  await alert.present()
}

async function handleLogout() {
  const alert = await alertController.create({
    header: 'Log Out',
    message: 'Are you sure you want to log out?',
    buttons: [
      { text: 'Cancel', role: 'cancel' },
      {
        text: 'Log Out',
        role: 'destructive',
        handler: async () => {
          try {
            if (auth.refreshToken) {
              await authApi.logout(auth.refreshToken)
            }
          } catch {}
          auth.clearSession()
          router.replace('/login')
        },
      },
    ],
  })
  await alert.present()
}
</script>

<style scoped>
.profile-content { --background: var(--w-bg); }
.content-pad { padding: 16px 16px 80px; display: flex; flex-direction: column; gap: 20px; }

.profile-card { background: var(--w-primary); border-radius: var(--w-radius-xl); padding: 24px; display: flex; align-items: center; gap: 16px; color: white; }
.profile-avatar { width: 60px; height: 60px; border-radius: 50%; background: rgba(255,255,255,0.2); display: flex; align-items: center; justify-content: center; font-size: 22px; font-weight: 700; flex-shrink: 0; }
.profile-name   { font-size: 20px; font-weight: 700; font-family: var(--w-font-display); }
.profile-unit   { font-size: 14px; opacity: 0.8; margin-top: 4px; }
.profile-estate { font-size: 13px; opacity: 0.6; margin-top: 2px; }

.push-banner { background: var(--w-warning-light); border-radius: var(--w-radius-md); padding: 12px 16px; font-size: 14px; color: var(--w-warning); display: flex; align-items: center; gap: 8px; }
.settings-link { background: none; border: none; color: var(--w-warning); font-weight: 700; font-size: 14px; cursor: pointer; text-decoration: underline; padding: 0; }

.menu-section { display: flex; flex-direction: column; gap: 8px; }
.menu-section-label { font-size: 12px; font-weight: 700; color: var(--w-muted); text-transform: uppercase; letter-spacing: 0.5px; }
.menu-list  { background: var(--w-surface); border-radius: var(--w-radius-md); overflow: hidden; }
.menu-row   { display: flex; align-items: center; gap: 12px; padding: 15px 16px; border-bottom: 1px solid var(--w-border); background: none; border-left: none; border-right: none; border-top: none; width: 100%; cursor: pointer; font-family: var(--w-font-body); transition: background 0.15s; }
.menu-row.no-tap { cursor: default; }
.menu-row:last-child { border-bottom: none; }
.menu-icon   { font-size: 20px; color: var(--w-primary); flex-shrink: 0; }
.menu-label  { flex: 1; font-size: 15px; color: var(--w-text); text-align: left; }
.menu-chevron{ font-size: 16px; color: var(--w-muted); }

.logout-btn { --border-radius: 14px; height: 52px; font-size: 16px; font-weight: 700; }
.version-label { text-align: center; font-size: 13px; color: var(--w-muted); }

/* ── Menu Enhancements ── */
.menu-icon-box {
  width: 36px;
  height: 36px;
  border-radius: 10px;
  display: flex;
  align-items: center;
  justify-content: center;
  flex-shrink: 0;
}
.menu-icon-box--green {
  background: rgba(10, 92, 56, 0.1);
  color: #0A5C38;
}
.menu-icon-box--blue {
  background: rgba(2, 132, 199, 0.1);
  color: #0284C7;
}
.menu-icon-box .menu-icon {
  font-size: 19px;
  color: inherit;
}
.menu-text-col {
  flex: 1;
  display: flex;
  flex-direction: column;
  gap: 2px;
  text-align: left;
}
.menu-sublabel {
  font-size: 11.5px;
  color: #6C7A72;
}
.badge-open-count {
  background: #FEF3C7;
  color: #B45309;
  font-size: 11px;
  font-weight: 700;
  padding: 3px 8px;
  border-radius: 999px;
  letter-spacing: 0.02em;
}

/* ── Sheet Modal Styles ── */
.sheet-modal-shell {
  display: flex;
  flex-direction: column;
  height: 100%;
  background: #F8FAF9;
  color: #112019;
}
.sheet-modal-header {
  display: flex;
  align-items: center;
  justify-content: space-between;
  padding: 20px 20px 14px;
  background: #FFFFFF;
  border-bottom: 1px solid #EAEFEA;
}
.sheet-modal-header h2 {
  font-size: 18px;
  font-weight: 700;
  margin: 0;
  color: #112019;
}
.sheet-modal-header p {
  font-size: 12.5px;
  color: #6C7A72;
  margin: 2px 0 0;
}
.modal-close-btn {
  background: #F1F4F2;
  border: none;
  width: 32px;
  height: 32px;
  border-radius: 50%;
  font-size: 20px;
  color: #4A5850;
  display: flex;
  align-items: center;
  justify-content: center;
  cursor: pointer;
}
.sheet-modal-body {
  flex: 1;
  overflow-y: auto;
  padding: 16px 18px 40px;
  display: flex;
  flex-direction: column;
  gap: 16px;
  scrollbar-width: none;
}
.sheet-modal-body::-webkit-scrollbar {
  display: none;
}

/* Office Hotlines */
.office-hotline-card {
  background: #EAF4EE;
  border: 1px solid rgba(10, 92, 56, 0.15);
  border-radius: 14px;
  padding: 14px;
  display: flex;
  flex-direction: column;
  gap: 8px;
}
.hotline-title {
  font-size: 12px;
  font-weight: 700;
  color: #0A5C38;
}
.hotline-btns {
  display: grid;
  grid-template-columns: 1fr 1fr;
  gap: 8px;
}
.hotline-btn {
  display: flex;
  align-items: center;
  justify-content: center;
  gap: 6px;
  background: #FFFFFF;
  color: #0A5C38;
  height: 36px;
  border-radius: 9px;
  font-size: 12px;
  font-weight: 700;
  text-decoration: none;
  border: 1px solid rgba(10, 92, 56, 0.2);
}
.hotline-btn--green {
  background: #25D366;
  color: #FFFFFF;
  border: none;
}

/* Form inputs inside modal */
.modal-form-section {
  background: #FFFFFF;
  border: 1px solid #E4EAE6;
  border-radius: 16px;
  padding: 16px;
  display: flex;
  flex-direction: column;
  gap: 8px;
}
.form-field-label {
  font-size: 12.5px;
  font-weight: 700;
  color: #24352D;
  margin-top: 4px;
}
.custom-input,
.custom-select,
.custom-textarea {
  width: 100%;
  border: 1.5px solid #DCE6E0;
  border-radius: 10px;
  padding: 10px 12px;
  font-size: 13.5px;
  color: #112019;
  font-family: inherit;
  background: #FAFBFB;
  outline: none;
  transition: border-color 0.2s ease;
}
.custom-input:focus,
.custom-select:focus,
.custom-textarea:focus {
  border-color: #0A5C38;
  background: #FFFFFF;
}
.priority-options {
  display: grid;
  grid-template-columns: repeat(3, 1fr);
  gap: 8px;
  margin-bottom: 4px;
}
.priority-chip {
  height: 34px;
  border-radius: 8px;
  border: 1.5px solid #E2E8E4;
  background: #FFFFFF;
  font-size: 12px;
  font-weight: 600;
  color: #4A5850;
  cursor: pointer;
  transition: all 0.15s ease;
}
.priority-chip.normal.active {
  background: #E0F2FE;
  border-color: #0284C7;
  color: #0369A1;
  font-weight: 700;
}
.priority-chip.high.active {
  background: #FEF3C7;
  border-color: #D97706;
  color: #B45309;
  font-weight: 700;
}
.priority-chip.urgent.active {
  background: #FEE2E2;
  border-color: #DC2626;
  color: #B91C1C;
  font-weight: 700;
}

.submit-action-btn {
  height: 44px;
  border-radius: 10px;
  background: #0A5C38;
  color: #FFFFFF;
  border: none;
  font-size: 14px;
  font-weight: 700;
  cursor: pointer;
  display: flex;
  align-items: center;
  justify-content: center;
  margin-top: 8px;
  box-shadow: 0 4px 12px rgba(10, 92, 56, 0.25);
  transition: opacity 0.15s ease;
}
.submit-action-btn:disabled {
  opacity: 0.55;
  cursor: not-allowed;
  box-shadow: none;
}

/* History block */
.history-block {
  display: flex;
  flex-direction: column;
  gap: 8px;
}
.history-title {
  font-size: 13px;
  font-weight: 700;
  color: #4A5850;
}
.message-card {
  background: #FFFFFF;
  border: 1px solid #E4EAE6;
  border-radius: 12px;
  padding: 12px 14px;
  display: flex;
  flex-direction: column;
  gap: 6px;
}
.message-card-top {
  display: flex;
  align-items: center;
  justify-content: space-between;
}
.message-cat-badge {
  font-size: 11px;
  font-weight: 700;
  color: #0A5C38;
  background: #E8F5EE;
  padding: 2px 7px;
  border-radius: 999px;
}
.message-time {
  font-size: 11px;
  color: #8C9991;
}
.message-card-body {
  font-size: 13px;
  color: #1F2E26;
  line-height: 1.4;
}
.message-card-reply {
  background: #F3F8F5;
  border-left: 3px solid #0A5C38;
  padding: 8px 10px;
  border-radius: 6px;
  font-size: 12px;
  color: #0E3E27;
  margin-top: 2px;
}
.message-card-pending {
  font-size: 11.5px;
  color: #94A39A;
}

/* ── Support Sub-tabs & Tickets ── */
.support-subtabs {
  display: flex;
  background: #FFFFFF;
  border-bottom: 1px solid #EAEFEA;
  padding: 0 16px;
  gap: 16px;
}
.support-tab-btn {
  background: none;
  border: none;
  padding: 12px 4px;
  font-size: 13px;
  font-weight: 600;
  color: #7A8B81;
  cursor: pointer;
  position: relative;
  transition: color 0.15s ease;
}
.support-tab-btn.active {
  color: #0A5C38;
  font-weight: 700;
}
.support-tab-btn.active::after {
  content: '';
  position: absolute;
  bottom: 0;
  left: 0;
  right: 0;
  height: 2.5px;
  background: #0A5C38;
  border-radius: 999px;
}

.ticket-filter-pills {
  display: flex;
  gap: 6px;
  overflow-x: auto;
  padding-bottom: 4px;
  scrollbar-width: none;
}
.ticket-filter-pills::-webkit-scrollbar {
  display: none;
}
.filter-pill {
  border: 1px solid #DCE6E0;
  border-radius: 999px;
  background: #FFFFFF;
  color: #64746C;
  font-size: 12px;
  font-weight: 600;
  padding: 4px 12px;
  cursor: pointer;
  white-space: nowrap;
}
.filter-pill.active {
  border-color: #0A5C38;
  background: #0A5C38;
  color: #FFFFFF;
}

.tickets-stack {
  display: flex;
  flex-direction: column;
  gap: 10px;
}
.ticket-card {
  background: #FFFFFF;
  border: 1px solid #E2EAE5;
  border-radius: 14px;
  padding: 14px;
  display: flex;
  flex-direction: column;
  gap: 6px;
  box-shadow: 0 2px 6px rgba(0, 0, 0, 0.02);
}
.ticket-top-row {
  display: flex;
  align-items: center;
  justify-content: space-between;
}
.ticket-number {
  font-size: 11.5px;
  font-weight: 700;
  color: #0A5C38;
  letter-spacing: 0.03em;
}
.ticket-status-pill {
  font-size: 10px;
  font-weight: 700;
  padding: 2px 7px;
  border-radius: 999px;
  text-transform: uppercase;
}
.ticket-status-pill.open {
  background: #FEF3C7;
  color: #B45309;
}
.ticket-status-pill.in-progress {
  background: #E0F2FE;
  color: #0369A1;
}
.ticket-status-pill.resolved {
  background: #DCFCE7;
  color: #15803D;
}
.ticket-title {
  font-size: 13.5px;
  font-weight: 700;
  color: #112019;
}
.ticket-desc {
  font-size: 12.5px;
  color: #556B60;
  line-height: 1.4;
}
.ticket-meta-row {
  display: flex;
  align-items: center;
  justify-content: space-between;
  font-size: 11px;
  color: #8C9991;
  margin-top: 4px;
  padding-top: 6px;
  border-top: 1px dashed #E8EFEA;
}
.ticket-admin-update {
  background: #F4FAF6;
  border-radius: 8px;
  padding: 8px 10px;
  font-size: 12px;
  color: #0E3E27;
  display: flex;
  flex-direction: column;
  gap: 2px;
  margin-top: 4px;
}
.admin-note-label {
  font-size: 10.5px;
  font-weight: 700;
  color: #0A5C38;
  text-transform: uppercase;
}
.ticket-empty-wrap {
  background: #FFFFFF;
  border-radius: 16px;
  border: 1px solid #E2EAE5;
  margin-top: 6px;
}
</style>
