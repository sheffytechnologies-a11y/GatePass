import { ref } from 'vue'

interface Toast { id: number; message: string; type: 'success' | 'error' | 'warning' }

const toasts = ref<Toast[]>([])
let nextId = 0

export function useToast() {
  function showToast(message: string, type: Toast['type'] = 'success') {
    const id = nextId++
    toasts.value.push({ id, message, type })
    setTimeout(() => {
      toasts.value = toasts.value.filter(t => t.id !== id)
    }, 3500)
  }
  return { toasts, showToast }
}
