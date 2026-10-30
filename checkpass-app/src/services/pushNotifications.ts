import { Capacitor } from '@capacitor/core'
import { PushNotifications, type Token, type ActionPerformed, type PushNotificationSchema } from '@capacitor/push-notifications'
import client from '@/api/client'
import router from '@/router'

class PushNotificationService {
  private isInitialized = false

  public async initialize(): Promise<void> {
    if (this.isInitialized) return
    this.isInitialized = true

    // Only run native push listeners on Android / iOS
    if (!Capacitor.isNativePlatform()) {
      console.log('[Push] Running on web/desktop — native push listeners skipped.')
      return
    }

    try {
      // 1. Check permissions
      let permStatus = await PushNotifications.checkPermissions()
      if (permStatus.receive === 'prompt') {
        permStatus = await PushNotifications.requestPermissions()
      }

      if (permStatus.receive !== 'granted') {
        console.warn('[Push] Push notifications permission was not granted:', permStatus.receive)
        return
      }

      // 2. Register with Apple (APNs) or Google (FCM)
      await PushNotifications.register()
      this.attachListeners()
    } catch (err) {
      console.error('[Push] Failed to initialize push notifications:', err)
    }
  }

  private attachListeners(): void {
    // Successfully registered with FCM/APNs
    PushNotifications.addListener('registration', async (token: Token) => {
      console.log('[Push] Device push token received:', token.value)
      localStorage.setItem('cp_push_token', token.value)

      // Sync device push token with Checkpass backend
      try {
        await client.patch('/v1/profile/preferences', {
          pushEnabled: true,
          pushToken: token.value,
        })
        console.log('[Push] Token successfully synced to Checkpass profile.')
      } catch (err) {
        console.warn('[Push] Could not sync push token to server (may retry on next session):', err)
      }
    })

    // Registration failed
    PushNotifications.addListener('registrationError', (error: any) => {
      console.error('[Push] Push registration error:', JSON.stringify(error))
    })

    // Notification arrived while app is in foreground
    PushNotifications.addListener('pushNotificationReceived', (notification: PushNotificationSchema) => {
      console.log('[Push] Foreground push notification received:', notification.title, notification.body)
      
      // Emit custom browser event for in-app alert or notification badges
      window.dispatchEvent(new CustomEvent('checkpass:push-received', { detail: notification }))
    })

    // User tapped on push notification in OS notification shade
    PushNotifications.addListener('pushNotificationActionPerformed', (action: ActionPerformed) => {
      console.log('[Push] Notification action tapped:', action.notification)
      const data = action.notification.data || {}

      if (data.passId) {
        router.push(`/passes/${data.passId}`)
      } else if (data.type === 'emergency') {
        router.push('/tabs/emergency')
      } else if (data.type === 'fee') {
        router.push('/tabs/fees')
      } else {
        router.push('/tabs/home')
      }
    })
  }

  /**
   * Request push permission on demand (e.g. from Settings or Profile view)
   */
  public async requestPermissionOnDemand(): Promise<boolean> {
    if (!Capacitor.isNativePlatform()) return false

    try {
      const permStatus = await PushNotifications.requestPermissions()
      if (permStatus.receive === 'granted') {
        await PushNotifications.register()
        return true
      }
      return false
    } catch (err) {
      console.error('[Push] Error requesting permissions on demand:', err)
      return false
    }
  }
}

export const pushNotifications = new PushNotificationService()
