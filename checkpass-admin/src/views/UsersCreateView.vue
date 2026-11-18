<template>
  <div class="create-page">
    <div class="create-head">
      <button class="nav-back" @click="goBack">
        <svg width="24" height="24" viewBox="0 0 24 24" fill="none"><path d="M15 18l-6-6 6-6" stroke="#1A1A1A" stroke-width="2.2" stroke-linecap="round" stroke-linejoin="round"/></svg>
      </button>
      <h1 class="create-title">Add Staff Member</h1>
      <div class="nav-spacer"></div>
    </div>

    <div class="create-card card">
      <h2 class="section-title">Select Staff Role</h2>
      <div class="type-row">
        <button
          v-for="opt in typeOptions"
          :key="opt.value"
          class="type-chip"
          :class="{ active: selectedType === opt.value }"
          @click="selectedType = opt.value"
        >
          {{ opt.label }}
        </button>
      </div>

      <div class="form-group">
        <label class="form-label">Full Name <span class="req">*</span></label>
        <input v-model="form.name" class="form-input" placeholder="e.g. Officer Samuel Okon" />
      </div>

      <div class="form-group">
        <label class="form-label">
          Phone Number <span v-if="selectedType === 'security'" class="req">*</span>
        </label>
        <input v-model="form.phone" class="form-input" type="tel" placeholder="e.g. 08012345678" />
      </div>

      <div class="form-group">
        <label class="form-label">
          Email Address <span v-if="selectedType === 'admin'" class="req">*</span>
        </label>
        <input v-model="form.email" class="form-input" type="email" placeholder="e.g. admin@estate.com" />
      </div>

      <div class="double-grid">
        <div class="form-group">
          <label class="form-label">Password <span class="req">*</span></label>
          <div class="password-input-wrap">
            <input
              v-model="form.password"
              class="form-input with-toggle"
              :type="showPassword ? 'text' : 'password'"
              placeholder="Minimum 6 characters"
            />
            <button
              type="button"
              class="password-toggle-btn"
              @click="showPassword = !showPassword"
              :aria-label="showPassword ? 'Hide password' : 'Show password'"
              tabindex="-1"
            >
              <svg v-if="!showPassword" width="18" height="18" viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="2" stroke-linecap="round" stroke-linejoin="round">
                <path d="M2 12s3-7 10-7 10 7 10 7-3 7-10 7-10-7-10-7Z"/>
                <circle cx="12" cy="12" r="3"/>
              </svg>
              <svg v-else width="18" height="18" viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="2" stroke-linecap="round" stroke-linejoin="round">
                <path d="M9.88 9.88a3 3 0 1 0 4.24 4.24"/>
                <path d="M10.73 5.08A10.43 10.43 0 0 1 12 5c7 0 10 7 10 7a13.16 13.16 0 0 1-1.67 2.68"/>
                <path d="M6.61 6.61A13.526 13.526 0 0 0 2 12s3 7 10 7a9.74 9.74 0 0 0 5.39-1.61"/>
                <line x1="2" x2="22" y1="2" y2="22"/>
              </svg>
            </button>
          </div>
        </div>
        <div class="form-group">
          <label class="form-label">Confirm Password <span class="req">*</span></label>
          <div class="password-input-wrap">
            <input
              v-model="form.confirmPassword"
              class="form-input with-toggle"
              :type="showConfirmPassword ? 'text' : 'password'"
              placeholder="Re-enter password"
            />
            <button
              type="button"
              class="password-toggle-btn"
              @click="showConfirmPassword = !showConfirmPassword"
              :aria-label="showConfirmPassword ? 'Hide password' : 'Show password'"
              tabindex="-1"
            >
              <svg v-if="!showConfirmPassword" width="18" height="18" viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="2" stroke-linecap="round" stroke-linejoin="round">
                <path d="M2 12s3-7 10-7 10 7 10 7-3 7-10 7-10-7-10-7Z"/>
                <circle cx="12" cy="12" r="3"/>
              </svg>
              <svg v-else width="18" height="18" viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="2" stroke-linecap="round" stroke-linejoin="round">
                <path d="M9.88 9.88a3 3 0 1 0 4.24 4.24"/>
                <path d="M10.73 5.08A10.43 10.43 0 0 1 12 5c7 0 10 7 10 7a13.16 13.16 0 0 1-1.67 2.68"/>
                <path d="M6.61 6.61A13.526 13.526 0 0 0 2 12s3 7 10 7a9.74 9.74 0 0 0 5.39-1.61"/>
                <line x1="2" x2="22" y1="2" y2="22"/>
              </svg>
            </button>
          </div>
        </div>
      </div>

      <p v-if="formError" class="form-error">{{ formError }}</p>

      <div class="action-row">
        <button class="btn btn-outline" :disabled="saving" @click="goBack">Cancel</button>
        <button class="btn btn-primary" :disabled="saving" @click="save">
          {{ saving ? 'Saving…' : `Create ${selectedLabel}` }}
        </button>
      </div>
    </div>
  </div>
</template>

<script setup lang="ts">
import { computed, ref } from 'vue'
import { useRouter } from 'vue-router'
import { usersApi } from '@/api'
import { useToast } from '@/composables/useToast'

type StaffType = 'admin' | 'security'

const router = useRouter()
const { showToast } = useToast()

const selectedType = ref<StaffType>('admin')
const saving = ref(false)
const formError = ref('')
const showPassword = ref(false)
const showConfirmPassword = ref(false)

const typeOptions: Array<{ label: string; value: StaffType }> = [
  { label: 'Estate Admin', value: 'admin' },
  { label: 'Gate Security', value: 'security' },
]

const form = ref({
  name: '',
  phone: '',
  email: '',
  password: '',
  confirmPassword: '',
})

const selectedLabel = computed(() => typeOptions.find((option) => option.value === selectedType.value)?.label ?? 'Staff')

function goBack() {
  router.push('/users')
}

async function save() {
  formError.value = ''
  if (!form.value.name.trim()) {
    formError.value = 'Full name is required.'
    return
  }
  if (selectedType.value === 'security' && !form.value.phone.trim()) {
    formError.value = 'Phone number is required for gate security personnel.'
    return
  }
  if (selectedType.value === 'admin' && !form.value.email.trim()) {
    formError.value = 'Email address is required for administrator accounts.'
    return
  }
  if (!form.value.password || form.value.password.length < 6) {
    formError.value = 'Password must be at least 6 characters.'
    return
  }
  if (form.value.password !== form.value.confirmPassword) {
    formError.value = 'Password confirmation does not match.'
    return
  }

  saving.value = true
  try {
    const payload: Record<string, unknown> = {
      name: form.value.name.trim(),
      password: form.value.password,
      type: selectedType.value,
    }
    if (form.value.phone.trim()) payload.phone = form.value.phone.trim()
    if (form.value.email.trim()) payload.email = form.value.email.trim()

    await usersApi.create(payload)

    showToast(`${selectedLabel.value} account created successfully.`, 'success')
    router.push('/users')
  } catch (error: any) {
    formError.value = error?.response?.data?.message ?? 'Failed to create staff account.'
  } finally {
    saving.value = false
  }
}
</script>

<style scoped>
.create-page {
  display: flex;
  flex-direction: column;
  gap: 14px;
  max-width: 640px;
  margin: 0 auto;
  width: 100%;
}

.create-head {
  display: flex;
  align-items: center;
  justify-content: space-between;
}

.create-title {
  font-size: 20px;
  font-weight: 700;
}

.nav-back {
  width: 36px;
  height: 36px;
  display: flex;
  align-items: center;
  justify-content: center;
  border: none;
  border-radius: 50%;
  background: #edf2ee;
  cursor: pointer;
}

.nav-spacer {
  width: 36px;
}

.create-card {
  border-radius: 24px;
  padding: 24px;
  display: flex;
  flex-direction: column;
  gap: 16px;
  background: #ffffff;
  border: 1px solid #E5E7EB;
}

.section-title {
  font-size: 16px;
  font-weight: 700;
  margin: 0;
  color: #111827;
}

.type-row {
  display: flex;
  gap: 8px;
}

.type-chip {
  border: 1px solid #E5E7EB;
  border-radius: 999px;
  padding: 8px 18px;
  background: #F9FAFB;
  color: #4B5563;
  font-weight: 600;
  font-size: 13.5px;
  cursor: pointer;
  transition: all 0.15s ease;
}

.type-chip:hover {
  background: #F3F4F6;
  border-color: #D1D5DB;
}

.type-chip.active {
  background: #0A5C38;
  border-color: #0A5C38;
  color: white;
}

.resident-redirect-card {
  display: flex;
  flex-direction: column;
  align-items: center;
  text-align: center;
  padding: 32px 20px;
  background: #F9FAFB;
  border: 1px dashed #D1D5DB;
  border-radius: 16px;
  gap: 14px;
  margin-top: 8px;
}

.resident-redirect-icon {
  width: 48px;
  height: 48px;
  border-radius: 12px;
  background: #ECFDF5;
  border: 1px solid #A7F3D0;
  display: flex;
  align-items: center;
  justify-content: center;
}

.redirect-title {
  margin: 0 0 6px;
  font-size: 16px;
  font-weight: 700;
  color: #111827;
}

.redirect-desc {
  margin: 0;
  font-size: 13px;
  color: #6B7280;
  max-width: 400px;
  line-height: 1.45;
}

.req {
  color: #DC2626;
}

.double-grid {
  display: grid;
  grid-template-columns: 1fr 1fr;
  gap: 12px;
}

.form-error {
  color: #DC2626;
  font-size: 13px;
  font-weight: 500;
  margin: 0;
}

.action-row {
  display: flex;
  justify-content: flex-end;
  gap: 10px;
  margin-top: 10px;
}

.password-input-wrap {
  position: relative;
  display: flex;
  align-items: center;
  width: 100%;
}

.password-input-wrap .with-toggle {
  padding-right: 44px;
}

.password-toggle-btn {
  position: absolute;
  right: 12px;
  top: 50%;
  transform: translateY(-50%);
  background: transparent;
  border: none;
  padding: 6px;
  display: flex;
  align-items: center;
  justify-content: center;
  color: #6B7280;
  cursor: pointer;
  border-radius: 8px;
  transition: color 0.15s ease, background 0.15s ease;
}

.password-toggle-btn:hover {
  color: #111827;
  background: rgba(0, 0, 0, 0.04);
}

.password-toggle-btn:active {
  transform: translateY(-50%) scale(0.94);
}

@media (max-width: 600px) {
  .double-grid {
    grid-template-columns: 1fr;
  }
}
</style>