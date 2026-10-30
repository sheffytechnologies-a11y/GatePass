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
          @click="selectedType = opt.value"
        >
          {{ opt.label }}
        </button>
      </div>

      <template v-if="selectedType === 'resident'">
        <h3 class="section-sub">Residency Details</h3>
        <div class="form-group">
          <label class="form-label">Owner's Full Name</label>
          <input v-model="form.name" class="form-input" placeholder="Enter Full Name" />
        </div>
        <div class="form-group">
          <label class="form-label">Owner's Phone No.</label>
          <input v-model="form.phone" class="form-input" type="tel" placeholder="Enter Phone No" />
        </div>
        <div class="form-group">
          <label class="form-label">Owner's Email Address</label>
          <input v-model="form.email" class="form-input" type="email" placeholder="Enter Email Address" />
        </div>
        <div class="double-grid">
          <div class="form-group">
            <label class="form-label">Password</label>
            <input v-model="form.password" class="form-input" type="password" placeholder="Minimum 6 characters" />
          </div>
          <div class="form-group">
            <label class="form-label">Confirm Password</label>
            <input v-model="form.confirmPassword" class="form-input" type="password" placeholder="Re-enter password" />
          </div>
        </div>
        <div class="address-row">
          <div class="form-group">
            <label class="form-label">Lane</label>
            <input v-model="form.lane" class="form-input" />
          </div>
          <div class="form-group">
            <label class="form-label">House</label>
            <input v-model="form.house" class="form-input" />
          </div>
          <div class="form-group">
            <label class="form-label">Flat</label>
            <input v-model="form.flat" class="form-input" />
          </div>
        </div>
        <div class="toggle-row">
          <label class="toggle-switch">
            <input type="checkbox" v-model="form.landlordIsOccupant" />
            <span class="toggle-track"><span class="toggle-thumb"></span></span>
          </label>
          <span class="toggle-label">Landlord is occupant</span>
        </div>

        <div class="occupants-head">
          <h3 class="section-sub">Occupants</h3>
          <button type="button" class="btn btn-sm btn-primary btn-add-tenant" @click="addTenant">
            Add Tenant
          </button>
        </div>

        <div v-for="(tenant, i) in form.tenants" :key="i" class="tenant-block">
          <div class="form-group">
            <div class="label-row">
              <label class="form-label">Full Name</label>
              <button type="button" class="tenant-del" @click="removeTenant(i)">Remove</button>
            </div>
            <input v-model="tenant.name" class="form-input" placeholder="Enter Full Name" />
          </div>
          <div class="form-group">
            <label class="form-label">Phone No.</label>
            <input v-model="tenant.phone" class="form-input" type="tel" placeholder="Enter Phone No" />
          </div>
          <div class="form-group">
            <label class="form-label">Email Address</label>
            <input v-model="tenant.email" class="form-input" type="email" placeholder="Enter Email Address" />
          </div>
          <div class="double-grid">
            <div class="form-group">
              <label class="form-label">Password</label>
              <input v-model="tenant.password" class="form-input" type="password" placeholder="Minimum 6 characters" />
            </div>
            <div class="form-group">
              <label class="form-label">Confirm Password</label>
              <input v-model="tenant.confirmPassword" class="form-input" type="password" placeholder="Re-enter password" />
            </div>
          </div>
        </div>
      </template>

      <template v-else>
        <div class="form-group">
          <label class="form-label">Full Name</label>
          <input v-model="form.name" class="form-input" placeholder="Enter Full Name" />
        </div>
        <div class="form-group">
          <label class="form-label">Phone No.</label>
          <input v-model="form.phone" class="form-input" type="tel" placeholder="Enter Phone No" />
        </div>
        <div class="form-group">
          <label class="form-label">Email Address</label>
          <input v-model="form.email" class="form-input" type="email" placeholder="Enter Email Address" />
        </div>
        <div class="double-grid">
          <div class="form-group">
            <label class="form-label">Password</label>
            <input v-model="form.password" class="form-input" type="password" placeholder="Minimum 6 characters" />
          </div>
          <div class="form-group">
            <label class="form-label">Confirm Password</label>
            <input v-model="form.confirmPassword" class="form-input" type="password" placeholder="Re-enter password" />
          </div>
        </div>
      </template>

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
import { residentsApi, usersApi } from '@/api'
import { useToast } from '@/composables/useToast'

type UserType = 'resident' | 'security' | 'admin'

const router = useRouter()
const { showToast } = useToast()

const selectedType = ref<UserType>('resident')
const saving = ref(false)
const formError = ref('')

const typeOptions: Array<{ label: string; value: UserType }> = [
  { label: 'Resident', value: 'resident' },
  { label: 'Security', value: 'security' },
  { label: 'Admin', value: 'admin' },
]

const form = ref({
  name: '',
  phone: '',
  email: '',
  password: '',
  confirmPassword: '',
  lane: '',
  house: '',
  flat: '',
  landlordIsOccupant: false,
  tenants: [] as Array<{ name: string; phone: string; email: string; password: string; confirmPassword: string }>,
})

const selectedLabel = computed(() => typeOptions.find((option) => option.value === selectedType.value)?.label ?? 'User')

function goBack() {
  router.push('/users')
}

function addTenant() {
  form.value.tenants.push({ name: '', phone: '', email: '', password: '', confirmPassword: '' })
}

function removeTenant(index: number) {
  form.value.tenants.splice(index, 1)
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
    if (selectedType.value === 'resident') {
      const ownerPayload: Record<string, unknown> = {
        name: form.value.name,
        phone: form.value.phone,
        password: form.value.password,
        role: 'owner',
        lane: form.value.lane,
        house: form.value.house,
        flat: form.value.flat,
        landlord_is_occupant: form.value.landlordIsOccupant,
      }
      if (form.value.email) ownerPayload.email = form.value.email

      await residentsApi.create(ownerPayload)

      for (const tenant of form.value.tenants) {
        if (!tenant.name.trim() || !tenant.phone.trim()) continue
        if (!tenant.password || tenant.password.length < 6) {
          formError.value = 'Each tenant password must be at least 6 characters.'
          saving.value = false
          return
        }
        if (tenant.password !== tenant.confirmPassword) {
          formError.value = 'Tenant password confirmation does not match.'
          saving.value = false
          return
        }

        const tenantPayload: Record<string, unknown> = {
          name: tenant.name,
          phone: tenant.phone,
          password: tenant.password,
          role: 'tenant',
          lane: form.value.lane,
          house: form.value.house,
          flat: form.value.flat,
        }
        if (tenant.email) tenantPayload.email = tenant.email

        await residentsApi.create(tenantPayload)
      }
    } else {
      const payload: Record<string, unknown> = {
        name: form.value.name,
        phone: form.value.phone,
        password: form.value.password,
        type: selectedType.value,
      }
      if (form.value.email) payload.email = form.value.email
      await usersApi.create(payload)
    }

    showToast(`${selectedLabel.value} created.`, 'success')
    router.push('/users')
  } catch (error: any) {
    formError.value = error?.response?.data?.message ?? 'Something went wrong.'
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
}

.nav-spacer {
  width: 36px;
}

.create-card {
  border-radius: 24px;
  padding: 16px;
  display: flex;
  flex-direction: column;
  gap: 12px;
}

.section-title {
  font-size: 17px;
  font-weight: 700;
}

.section-sub {
  margin-top: 8px;
  font-size: 16px;
  font-weight: 700;
}

.type-row {
  display: flex;
  gap: 8px;
  overflow-x: auto;
}

.type-chip {
  border: none;
  border-radius: 999px;
  padding: 8px 14px;
  background: #edf2ee;
  color: #4f5f56;
  font-weight: 700;
}

.type-chip.active {
  background: var(--c-primary);
  color: white;
}

.address-row {
  display: grid;
  grid-template-columns: 1fr 1.4fr 1fr;
  gap: 8px;
}

.toggle-row {
  display: flex;
  align-items: center;
  gap: 12px;
}

.toggle-switch {
  position: relative;
  display: inline-flex;
  cursor: pointer;
}

.toggle-switch input {
  position: absolute;
  opacity: 0;
  width: 0;
  height: 0;
}

.toggle-track {
  width: 44px;
  height: 26px;
  background: #e0e0e0;
  border-radius: 13px;
  transition: background 0.2s;
  position: relative;
  display: block;
}

.toggle-switch input:checked + .toggle-track {
  background: var(--c-primary);
}

.toggle-thumb {
  position: absolute;
  top: 3px;
  left: 3px;
  width: 20px;
  height: 20px;
  border-radius: 50%;
  background: white;
  transition: left 0.2s;
  box-shadow: 0 1px 3px rgba(0, 0, 0, 0.2);
}

.toggle-switch input:checked + .toggle-track .toggle-thumb {
  left: 21px;
}

.toggle-label {
  font-size: 14px;
  color: #1a1a1a;
}

.occupants-head {
  display: flex;
  align-items: center;
  justify-content: space-between;
}

.btn-add-tenant {
  border-radius: 999px;
}

.tenant-block {
  display: flex;
  flex-direction: column;
  gap: 12px;
  padding-top: 12px;
  border-top: 1px solid #f0f0f0;
}

.label-row {
  display: flex;
  align-items: center;
  justify-content: space-between;
}

.tenant-del {
  border: none;
  background: transparent;
  color: #8a8f97;
  font-size: 13px;
}

.action-row {
  display: flex;
  justify-content: flex-end;
  gap: 8px;
  margin-top: 10px;
}

@media (max-width: 420px) {
  .address-row {
    grid-template-columns: 1fr;
  }

  .action-row {
    flex-direction: column;
  }

  .action-row .btn {
    width: 100%;
    justify-content: center;
  }
}
</style>