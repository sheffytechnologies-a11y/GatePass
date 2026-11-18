<template>
  <div class="create-page">
    <div class="create-head">
      <button class="nav-back" @click="goBack">
        <svg width="24" height="24" viewBox="0 0 24 24" fill="none"><path d="M15 18l-6-6 6-6" stroke="#1A1A1A" stroke-width="2.2" stroke-linecap="round" stroke-linejoin="round"/></svg>
      </button>
      <h1 class="create-title">Add User</h1>
      <div class="nav-spacer"></div>
    </div>

    <div class="create-card card">
      <h2 class="section-title">Select User Type</h2>
      <div class="type-row">
        <button
          v-for="opt in typeOptions"
          :key="opt.value"
          class="type-chip"
          :class="{ active: selectedType === opt.value }"
          @click="handleTypeSelect(opt.value)"
        >
          {{ opt.label }}
        </button>
      </div>

      <div v-if="selectedType === 'resident'" class="resident-redirect-card">
        <div class="resident-redirect-icon">
          <svg width="24" height="24" viewBox="0 0 24 24" fill="none" stroke="#0A5C38" stroke-width="2">
            <path d="M3 9l9-7 9 7v11a2 2 0 0 1-2 2H5a2 2 0 0 1-2-2z"/>
            <polyline points="9 22 9 12 15 12 15 22"/>
          </svg>
        </div>
        <div class="resident-redirect-info">
          <h3 class="redirect-title">Resident Registration Workflow</h3>
          <p class="redirect-desc">
            Residents are registered with property unit assignment, household occupants, and gate passes.
          </p>
        </div>
        <button type="button" class="btn btn-primary" @click="router.push('/residents/new')">
          Go to Resident Registration →
        </button>
      </div>

      <template v-else>
        <div class="form-group">
          <label class="form-label">Full Name <span class="req">*</span></label>
          <input v-model="form.name" class="form-input" placeholder="Enter Full Name" />
        </div>
        <div class="form-group">
          <label class="form-label">Phone No. <span class="req">*</span></label>
          <input v-model="form.phone" class="form-input" type="tel" placeholder="Enter Phone No" />
        </div>
        <div class="form-group">
          <label class="form-label">Email Address</label>
          <input v-model="form.email" class="form-input" type="email" placeholder="Enter Email Address" />
        </div>
        <div class="double-grid">
          <div class="form-group">
            <label class="form-label">Password <span class="req">*</span></label>
            <input v-model="form.password" class="form-input" type="password" placeholder="Minimum 6 characters" />
          </div>
          <div class="form-group">
            <label class="form-label">Confirm Password <span class="req">*</span></label>
            <input v-model="form.confirmPassword" class="form-input" type="password" placeholder="Re-enter password" />
          </div>
        </div>

        <p v-if="formError" class="form-error">{{ formError }}</p>

        <div class="action-row">
          <button class="btn btn-outline" :disabled="saving" @click="goBack">Cancel</button>
          <button class="btn btn-primary" :disabled="saving" @click="save">
            {{ saving ? 'Saving…' : `Create ${selectedLabel}` }}
          </button>
        </div>
      </template>
    </div>
  </div>
</template>

<script setup lang="ts">
import { computed, ref } from 'vue'
import { useRouter } from 'vue-router'
import { usersApi } from '@/api'
import { useToast } from '@/composables/useToast'

type UserType = 'admin' | 'security' | 'resident'

const router = useRouter()
const { showToast } = useToast()

const selectedType = ref<UserType>('admin')
const saving = ref(false)
const formError = ref('')

const typeOptions: Array<{ label: string; value: UserType }> = [
  { label: 'Admin', value: 'admin' },
  { label: 'Security', value: 'security' },
  { label: 'Resident', value: 'resident' },
]

const form = ref({
  name: '',
  phone: '',
  email: '',
  password: '',
  confirmPassword: '',
})

const selectedLabel = computed(() => typeOptions.find((option) => option.value === selectedType.value)?.label ?? 'User')

function goBack() {
  router.push('/users')
}

function handleTypeSelect(val: UserType) {
  if (val === 'resident') {
    router.push('/residents/new')
    return
  }
  selectedType.value = val
}

async function save() {
  formError.value = ''
  if (!form.value.name.trim()) {
    formError.value = 'Name is required.'
    return
  }
  if (!form.value.phone.trim()) {
    formError.value = 'Phone number is required.'
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
      phone: form.value.phone.trim(),
      password: form.value.password,
      type: selectedType.value,
    }
    if (form.value.email) payload.email = form.value.email.trim()

    await usersApi.create(payload)

    showToast(`${selectedLabel.value} account created successfully.`, 'success')
    router.push('/users')
  } catch (error: any) {
    formError.value = error?.response?.data?.message ?? 'Failed to create user account.'
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

@media (max-width: 600px) {
  .double-grid {
    grid-template-columns: 1fr;
  }
}
</style>