// src/composables/useToast.ts
import { ref } from 'vue'

export type ToastVariant = 'success' | 'warning' | 'info' | 'error'

export interface ToastItem {
  id: number
  title: string
  subtitle?: string
  variant: ToastVariant
}

export interface ToastOptions {
  title: string
  subtitle?: string
  variant?: ToastVariant
  duration?: number
}

const toasts = ref<ToastItem[]>([])
let nextId = 0

function parseToastInput(
  input: string | ToastOptions,
  variant: ToastVariant = 'success',
  duration = 3200
): { title: string; subtitle?: string; variant: ToastVariant; duration: number } {
  if (typeof input === 'object' && input !== null) {
    return {
      title: input.title,
      subtitle: input.subtitle,
      variant: input.variant || 'success',
      duration: input.duration ?? 3200,
    }
  }

  const raw = String(input || '').trim()
  let title = raw
  let subtitle: string | undefined = undefined

  if (raw.includes(' — ')) {
    const parts = raw.split(' — ')
    title = parts[0].trim()
    subtitle = parts.slice(1).join(' — ').trim()
  } else if (raw.includes(' - ')) {
    const parts = raw.split(' - ')
    title = parts[0].trim()
    subtitle = parts.slice(1).join(' - ').trim()
  } else if (raw.includes('. ') && !raw.endsWith('.ng') && !raw.endsWith('.com')) {
    const firstDot = raw.indexOf('. ')
    title = raw.substring(0, firstDot).trim()
    subtitle = raw.substring(firstDot + 2).trim()
  } else if (raw.toLowerCase().includes('generating invite card')) {
    title = 'Generating invite card'
    subtitle = 'Preparing pass image for sharing...'
  } else if (raw.toLowerCase().includes('downloaded successfully')) {
    title = 'Invite card downloaded'
    subtitle = 'Ready to share with your visitor'
  }

  // Provide helpful contextual subtitle if none found
  if (!subtitle) {
    if (variant === 'success') {
      subtitle = 'Action completed successfully'
    } else if (variant === 'error') {
      subtitle = 'Please try again later'
    } else if (variant === 'warning') {
      subtitle = 'Check the details and retry'
    }
  }

  return { title, subtitle, variant, duration }
}

export function useToast() {
  function showToast(input: string | ToastOptions, variant: ToastVariant = 'success', duration = 3200) {
    const parsed = parseToastInput(input, variant, duration)
    const id = nextId++
    toasts.value.push({
      id,
      title: parsed.title,
      subtitle: parsed.subtitle,
      variant: parsed.variant,
    })

    setTimeout(() => {
      dismissToast(id)
    }, parsed.duration)
  }

  function dismissToast(id: number) {
    toasts.value = toasts.value.filter(t => t.id !== id)
  }

  return { toasts, showToast, dismissToast }
}

// ─────────────────────────────────────────────────────────────

// src/composables/useCamera.ts
import { Camera, CameraResultType, CameraSource } from '@capacitor/camera'

export function useCamera() {
  async function capturePhoto(): Promise<{ base64: string; dataUrl: string } | null> {
    try {
      const perm = await Camera.checkPermissions()

      if (perm.camera === 'denied') {
        // Permanently denied — caller must show settings guidance
        return null
      }

      if (perm.camera !== 'granted') {
        const req = await Camera.requestPermissions({ permissions: ['camera'] })
        if (req.camera !== 'granted') return null
      }

      const photo = await Camera.getPhoto({
        quality: 70,
        resultType: CameraResultType.DataUrl,
        source: CameraSource.Camera,
        allowEditing: false,
      })

      if (!photo.dataUrl) return null
      const base64 = photo.dataUrl.split(',')[1]
      return { base64, dataUrl: photo.dataUrl }
    } catch {
      return null
    }
  }

  async function pickFromGallery(): Promise<{ base64: string; dataUrl: string } | null> {
    try {
      const photo = await Camera.getPhoto({
        quality: 70,
        resultType: CameraResultType.DataUrl,
        source: CameraSource.Photos,
      })
      if (!photo.dataUrl) return null
      const base64 = photo.dataUrl.split(',')[1]
      return { base64, dataUrl: photo.dataUrl }
    } catch {
      return null
    }
  }

  async function getPermissionStatus(): Promise<'granted' | 'denied' | 'prompt'> {
    try {
      const perm = await Camera.checkPermissions()
      if (perm.camera === 'granted') return 'granted'
      if (perm.camera === 'denied') return 'denied'
      return 'prompt'
    } catch {
      return 'prompt'
    }
  }

  return { capturePhoto, pickFromGallery, getPermissionStatus }
}

// ─────────────────────────────────────────────────────────────

// src/composables/usePushPermission.ts
import { PushNotifications } from '@capacitor/push-notifications'

export function usePushPermission() {
  async function requestPermission(): Promise<boolean> {
    try {
      const perm = await PushNotifications.checkPermissions()
      if (perm.receive === 'granted') return true
      if (perm.receive === 'denied') return false

      const result = await PushNotifications.requestPermissions()
      if (result.receive !== 'granted') return false

      await PushNotifications.register()
      return true
    } catch {
      return false
    }
  }

  async function isGranted(): Promise<boolean> {
    try {
      const perm = await PushNotifications.checkPermissions()
      return perm.receive === 'granted'
    } catch {
      return false
    }
  }

  return { requestPermission, isGranted }
}
