declare global {
  interface Window {
    PaystackPop: any
  }
}

function loadPaystackScript(): Promise<void> {
  return new Promise((resolve) => {
    if (window.PaystackPop) { resolve(); return }
    const script = document.createElement('script')
    script.src = 'https://js.paystack.co/v2/inline.js'
    script.onload = () => resolve()
    document.head.appendChild(script)
  })
}

interface PaystackCheckoutArgs {
  email: string
  amount: number
  reference: string
  onSuccess: (reference: string) => void | Promise<void>
  onCancel?: () => void
}

export function usePaystack() {
  async function openCheckout({ email, amount, reference, onSuccess, onCancel }: PaystackCheckoutArgs) {
    await loadPaystackScript()

    const paystack = new window.PaystackPop()
    paystack.newTransaction({
      key: import.meta.env.VITE_PAYSTACK_PUBLIC_KEY,
      email,
      amount,
      reference,
      onSuccess: (transaction: { reference: string }) => onSuccess(transaction.reference),
      onCancel: () => onCancel?.(),
    })
  }

  return { openCheckout }
}
