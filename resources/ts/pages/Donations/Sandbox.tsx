import { router } from '@inertiajs/react'
import { useTranslation } from 'react-i18next'
import AuthLayout from '@/components/layouts/AuthLayout'
import type { Donation } from '@/types'

interface SandboxProps {
    donation: Donation
}

export default function Sandbox({ donation }: SandboxProps) {
    const { t } = useTranslation('donations')
    const gatewayLabel = t(`gateways.${donation.gateway}.label`)

    const pay = (success: boolean) => {
        router.post(`/donations/sandbox/${donation.uuid}/pay`, { success })
    }

    return (
        <AuthLayout>
            <div className="alert alert-warning text-center mb-4">
                <strong>{t('sandbox.sandboxMode')}</strong> {t('sandbox.sandboxNotice', { gateway: gatewayLabel })}
            </div>

            <h4 className="text-center mb-1">{gatewayLabel}</h4>
            <p className="text-ava-muted text-center mb-4">{t('sandbox.securePaymentSimulated')}</p>

            <div className="d-flex justify-content-between border-bottom py-2">
                <span className="text-ava-muted">{t('sandbox.fund')}</span>
                <span className="fw-semibold">{donation.fund?.name}</span>
            </div>
            <div className="d-flex justify-content-between border-bottom py-2 mb-4">
                <span className="text-ava-muted">{t('sandbox.amount')}</span>
                <span className="fw-semibold">{donation.amount.toFixed(2)} {donation.currency}</span>
            </div>

            <button type="button" className="btn btn-primary w-100 mb-2" onClick={() => pay(true)}>
                {t('sandbox.payNow')}
            </button>
            <button type="button" className="btn btn-outline-secondary w-100" onClick={() => pay(false)}>
                {t('sandbox.simulateFailedPayment')}
            </button>
        </AuthLayout>
    )
}
