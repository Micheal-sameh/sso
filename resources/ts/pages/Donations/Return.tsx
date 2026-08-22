import { Link } from '@inertiajs/react'
import { useTranslation } from 'react-i18next'
import AppLayout from '@/components/layouts/AppLayout'
import type { Donation } from '@/types'

interface ReturnProps {
    donation: Donation
}

export default function Return({ donation }: ReturnProps) {
    const { t } = useTranslation('donations')
    const success = donation.status === 'paid'

    return (
        <AppLayout title={t('return.title')}>
            <div className="ava-card p-4 p-md-5 text-center" style={{ maxWidth: 480, margin: '0 auto' }}>
                <i className={`bi ${success ? 'bi-check-circle-fill text-success' : 'bi-x-circle-fill text-danger'} icon-fs-48 d-block mb-3`} />
                <h4 className="mb-2">{success ? t('return.thankYou') : t('return.notCompleted')}</h4>
                <p className="text-ava-muted mb-4">
                    {success
                        ? t('return.successMessage', { amount: donation.amount.toFixed(2), currency: donation.currency, fund: donation.fund?.name })
                        : t('return.failureMessage')}
                </p>
                <div className="d-flex gap-2 justify-content-center">
                    <Link href="/donations/history" className="btn btn-outline-primary">{t('return.viewHistory')}</Link>
                    <Link href="/donations" className="btn btn-primary">
                        {success ? t('return.donateAgain') : t('return.tryAgain')}
                    </Link>
                </div>
            </div>
        </AppLayout>
    )
}
