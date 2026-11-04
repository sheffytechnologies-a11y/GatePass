<!-- src/components/AppToast.vue -->
<template>
  <teleport to="body">
    <div v-if="toasts.length > 0" class="toast-overlay">
      <transition-group name="toast-slide" tag="div" class="toast-list">
        <div
          v-for="toast in toasts"
          :key="toast.id"
          class="toast-pill"
          :class="`toast-${toast.variant}`"
          @click="dismissToast(toast.id)"
        >
          <!-- Left Icon Badge -->
          <div class="toast-icon-wrap" :class="`icon-${toast.variant}`">
            <!-- 1. Success: Green Circle with Checkmark -->
            <svg
              v-if="toast.variant === 'success'"
              class="toast-svg"
              width="16"
              height="16"
              viewBox="0 0 24 24"
              fill="none"
              stroke="#FFFFFF"
              stroke-width="3"
              stroke-linecap="round"
              stroke-linejoin="round"
            >
              <polyline points="20 6 9 17 4 12" />
            </svg>

            <!-- 2. Warning: Amber Triangle with Exclamation -->
            <svg
              v-else-if="toast.variant === 'warning'"
              class="toast-svg-poly"
              width="24"
              height="24"
              viewBox="0 0 24 24"
              fill="none"
            >
              <path
                d="M12 2.8L1.6 20.8C1.4 21.2 1.7 21.8 2.2 21.8H21.8C22.3 21.8 22.6 21.2 22.4 20.8L12 2.8Z"
                fill="#F59E0B"
              />
              <line x1="12" y1="9" x2="12" y2="13.8" stroke="#FFFFFF" stroke-width="2.3" stroke-linecap="round" />
              <circle cx="12" cy="17.5" r="1.25" fill="#FFFFFF" />
            </svg>

            <!-- 3. Info: Blue Circle with Exclamation -->
            <svg
              v-else-if="toast.variant === 'info'"
              class="toast-svg"
              width="16"
              height="16"
              viewBox="0 0 24 24"
              fill="none"
            >
              <line x1="12" y1="7.5" x2="12" y2="13" stroke="#FFFFFF" stroke-width="2.6" stroke-linecap="round" />
              <circle cx="12" cy="17" r="1.3" fill="#FFFFFF" />
            </svg>

            <!-- 4. Error: Red Octagon with Exclamation -->
            <svg
              v-else
              class="toast-svg-poly"
              width="24"
              height="24"
              viewBox="0 0 24 24"
              fill="none"
            >
              <polygon
                points="7.86 2 16.14 2 22 7.86 22 16.14 16.14 22 7.86 22 2 16.14 2 7.86 7.86 2"
                fill="#DC2626"
              />
              <line x1="12" y1="8" x2="12" y2="13.2" stroke="#FFFFFF" stroke-width="2.4" stroke-linecap="round" />
              <circle cx="12" cy="17" r="1.25" fill="#FFFFFF" />
            </svg>
          </div>

          <!-- Middle Content (Title + Subtitle) -->
          <div class="toast-content">
            <div class="toast-title">{{ toast.title }}</div>
            <div v-if="toast.subtitle" class="toast-subtitle">{{ toast.subtitle }}</div>
          </div>

          <!-- Right Close Button -->
          <button
            class="toast-close"
            type="button"
            @click.stop="dismissToast(toast.id)"
            aria-label="Close notification"
          >
            <svg
              width="14"
              height="14"
              viewBox="0 0 24 24"
              fill="none"
              stroke="currentColor"
              stroke-width="2.2"
              stroke-linecap="round"
              stroke-linejoin="round"
            >
              <line x1="18" y1="6" x2="6" y2="18" />
              <line x1="6" y1="6" x2="18" y2="18" />
            </svg>
          </button>
        </div>
      </transition-group>
    </div>
  </teleport>
</template>

<script setup lang="ts">
import { useToast } from '@/composables/useToast'

const { toasts, dismissToast } = useToast()
</script>

<style scoped>
.toast-overlay {
  position: fixed;
  top: calc(var(--ion-safe-area-top, 0px) + 14px);
  left: 16px;
  right: 16px;
  display: flex;
  justify-content: center;
  z-index: 999999;
  pointer-events: none;
}

.toast-list {
  display: flex;
  flex-direction: column;
  gap: 10px;
  align-items: center;
  width: 100%;
  max-width: 420px;
}

/* Floating Capsule Pill */
.toast-pill {
  pointer-events: auto;
  width: 100%;
  background: #FFFFFF;
  border-radius: 9999px;
  padding: 12px 18px;
  display: flex;
  align-items: center;
  gap: 14px;
  box-shadow: 0 12px 32px rgba(0, 0, 0, 0.09), 0 3px 10px rgba(0, 0, 0, 0.04);
  border: 1px solid rgba(0, 0, 0, 0.05);
  cursor: pointer;
  user-select: none;
  box-sizing: border-box;
}

/* Icon Badges */
.toast-icon-wrap {
  width: 34px;
  height: 34px;
  min-width: 34px;
  min-height: 34px;
  border-radius: 50%;
  display: flex;
  align-items: center;
  justify-content: center;
  flex-shrink: 0;
}

.toast-icon-wrap.icon-success {
  background: #16A34A;
}

.toast-icon-wrap.icon-info {
  background: #2563EB;
}

.toast-icon-wrap.icon-warning,
.toast-icon-wrap.icon-error {
  background: transparent;
}

.toast-svg-poly {
  width: 34px;
  height: 34px;
}

/* Content */
.toast-content {
  flex: 1;
  display: flex;
  flex-direction: column;
  gap: 1px;
  min-width: 0;
}

.toast-title {
  font-size: 14.5px;
  font-weight: 700;
  color: #1E293B;
  line-height: 1.25;
  letter-spacing: -0.2px;
  white-space: nowrap;
  overflow: hidden;
  text-overflow: ellipsis;
}

.toast-subtitle {
  font-size: 13px;
  font-weight: 400;
  color: #64748B;
  line-height: 1.3;
  letter-spacing: -0.1px;
  white-space: nowrap;
  overflow: hidden;
  text-overflow: ellipsis;
}

/* Close Button */
.toast-close {
  background: transparent;
  border: none;
  padding: 5px;
  color: #94A3B8;
  display: flex;
  align-items: center;
  justify-content: center;
  cursor: pointer;
  border-radius: 50%;
  transition: color 0.15s ease, background-color 0.15s ease;
  flex-shrink: 0;
  margin-left: 2px;
}

.toast-close:hover {
  color: #475569;
  background: rgba(0, 0, 0, 0.05);
}

.toast-close:active {
  transform: scale(0.92);
}

/* Smooth Slide Animations */
.toast-slide-enter-active {
  transition: all 0.34s cubic-bezier(0.16, 1, 0.3, 1);
}

.toast-slide-leave-active {
  transition: all 0.22s ease-in;
}

.toast-slide-enter-from {
  opacity: 0;
  transform: translateY(-24px) scale(0.95);
}

.toast-slide-leave-to {
  opacity: 0;
  transform: translateY(-16px) scale(0.95);
}
</style>
