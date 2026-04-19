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
          <div class="menu-section-label">Support</div>
          <div class="menu-list">
            <button class="menu-row">
              <ion-icon :icon="helpCircleOutline" class="menu-icon" />
              <span class="menu-label">FAQ</span>
              <ion-icon :icon="chevronForwardOutline" class="menu-chevron" />
            </button>
            <button class="menu-row">
              <ion-icon :icon="shieldCheckmarkOutline" class="menu-icon" />
              <span class="menu-label">Privacy Policy</span>
              <ion-icon :icon="chevronForwardOutline" class="menu-chevron" />
            </button>
          </div>
        </div>

        <ion-button expand="block" fill="outline" color="danger" @click="handleLogout" class="logout-btn">
          Log Out
        </ion-button>

        <div class="version-label">gatepass v1.0.0 • PHDL Estate</div>

      </div>
    </ion-content>
  </ion-page>
</template>

<script setup lang="ts">
import { ref, computed } from 'vue'
import { useRouter } from 'vue-router'
import {
  IonPage, IonHeader, IonToolbar, IonTitle, IonContent, IonButton,
  IonIcon, IonToggle, alertController,
} from '@ionic/vue'
import {
  personOutline, lockClosedOutline, notificationsOutline, notificationsOffOutline,
  enterOutline, timeOutline, helpCircleOutline, shieldCheckmarkOutline,
  chevronForwardOutline,
} from 'ionicons/icons'
import { useAuthStore } from '@/stores/auth'
import { useToast } from '@/composables/useToast'
import { delay } from '@/api/mock'

const router = useRouter()
const auth   = useAuthStore()
const { showToast } = useToast()

const pushDenied   = ref(false)
const notifEnabled = ref(auth.resident?.pushEnabled ?? true)
const arrivalAlerts= ref(true)
const expiryAlerts = ref(true)

const initials = computed(() =>
  auth.resident?.name.split(' ').map(w => w[0]).slice(0,2).join('').toUpperCase() || ''
)

function openSettings() {
  showToast('Opening settings…', 'success')
}

async function editProfile() {
  const alert = await alertController.create({
    header: 'Edit Profile',
    inputs: [
      { name: 'name',  type: 'text', value: auth.resident?.name,  placeholder: 'Full name' },
      { name: 'phone', type: 'tel',  value: auth.resident?.phone, placeholder: 'Phone number' },
    ],
    buttons: [
      { text: 'Cancel', role: 'cancel' },
      {
        text: 'Save',
        handler: async (_data) => {
          // ── Replace with real API call ──────────────────────────
          // await client.patch('/resident/profile', _data)
          await delay(400)
          showToast('Profile updated', 'success')
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
          if (data.new !== data.confirm) {
            showToast('Passwords do not match.', 'error')
            return false
          }
          // ── Replace with real API call ──────────────────────────
          // await authApi.changePassword(data.current, data.new)
          await delay(600)
          showToast('Password changed.', 'success')
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
          // ── Replace with real API call ──────────────────────────
          // await authApi.logout(auth.refreshToken!)
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
</style>
