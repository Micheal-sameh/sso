import { router } from '@inertiajs/react'
import AuthLayout from '@/components/layouts/AuthLayout'
import type { Donation } from '@/types'

interface SandboxProps {
    donation: Donation
}

const GATEWAY_LABELS: Record<string, string> = {
    nbe: 'National Bank of Egypt',
    cib: 'CIB',
}

export default function Sandbox({ donation }: SandboxProps) {
    const pay = (success: boolean) => {
        router.post(`/donations/sandbox/${donation.uuid}/pay`, { success })
    }

    return (
        <AuthLayout>
            <div className="alert alert-warning text-center mb-4">
                <strong>Sandbox mode</strong> — this stands in for {GATEWAY_LABELS[donation.gateway]}’s real
                payment page while bank merchant onboarding is pending. No real money moves here.
            </div>

            <h4 className="text-center mb-1">{GATEWAY_LABELS[donation.gateway]}</h4>
            <p className="text-ava-muted text-center mb-4">Secure Payment (Simulated)</p>

            <div className="d-flex justify-content-between border-bottom py-2">
                <span className="text-ava-muted">Fund</span>
                <span className="fw-semibold">{donation.fund?.name}</span>
            </div>
            <div className="d-flex justify-content-between border-bottom py-2 mb-4">
                <span className="text-ava-muted">Amount</span>
                <span className="fw-semibold">{donation.amount.toFixed(2)} {donation.currency}</span>
            </div>

            <button type="button" className="btn btn-primary w-100 mb-2" onClick={() => pay(true)}>
                Pay Now
            </button>
            <button type="button" className="btn btn-outline-secondary w-100" onClick={() => pay(false)}>
                Simulate Failed Payment
            </button>
        </AuthLayout>
    )
}
