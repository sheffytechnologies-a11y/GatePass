import { ref } from 'vue'

export type ToastType = 'success' | 'error' | 'warning' | 'info'

export interface Toast {
  id: number
  title: string
  subtitle?: string
  message: string
  type: ToastType
}

export interface ToastOptions {
  title: string
  subtitle?: string
  message?: string
  type?: ToastType
  duration?: number
}

const toasts = ref<Toast[]>([])
let nextId = 0

function parseToast(input: string | ToastOptions, type: ToastType = 'success'): { title: string; subtitle?: string; message: string; type: ToastType } {
  if (typeof input === 'object' && input !== null) {
    return {
      title: input.title,
      subtitle: input.subtitle,
      message: input.message || input.title,
      type: input.type || 'success',
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
  }

  if (!subtitle) {
    if (type === 'success') {
      subtitle = 'Action completed successfully'
    } else if (type === 'error') {
      subtitle = 'Please try again later'
    } else if (type === 'warning') {
      subtitle = 'Check details and retry'
    }
  }

  return { title, subtitle, message: raw, type }
}

export function useToast() {
  function showToast(input: string | ToastOptions, type: ToastType = 'success', duration = 3500) {
    const parsed = parseToast(input, type)
    const id = nextId++
    toasts.value.push({
      id,
      title: parsed.title,
      subtitle: parsed.subtitle,
      message: parsed.message,
      type: parsed.type,
    })
    setTimeout(() => {
      dismissToast(id)
    }, duration)
  }

  function dismissToast(id: number) {
    toasts.value = toasts.value.filter(t => t.id !== id)
  }

  return { toasts, showToast, dismissToast }
}
