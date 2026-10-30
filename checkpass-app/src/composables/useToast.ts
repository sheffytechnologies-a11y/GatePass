// src/composables/useToast.ts
import { toastController } from '@ionic/vue'

type ToastVariant = 'success' | 'warning' | 'error'

export function useToast() {
  async function showToast(message: string, variant: ToastVariant = 'success', duration = 2500) {
    const toast = await toastController.create({
      message,
      duration,
      position: 'bottom',
      color: variant === 'success' ? 'success' : variant === 'warning' ? 'warning' : 'danger',
      cssClass: 'w-toast',
    })
    await toast.present()
  }

  return { showToast }
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
