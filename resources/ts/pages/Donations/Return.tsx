import { Link } from '@inertiajs/react'
import AppLayout from '@/components/layouts/AppLayout'
import type { Donation } from '@/types'

interface ReturnProps {
    donation: Donation
}

export default function Return({ donation }: ReturnProps) {
    const success = donation.status === 'paid'

    return (
        <AppLayout title="Donation Status">
            <div className="ava-card p-4 p-md-5 text-center" style={{ maxWidth: 480, margin: '0 auto' }}>
                <i className={`bi ${success ? 'bi-check-circle-fill text-success' : 'bi-x-circle-fill text-danger'} icon-fs-48 d-block mb-3`} />
                <h4 className="mb-2">{success ? 'Thank you for your generosity!' : 'Payment Not Completed'}</h4>
                <p className="text-ava-muted mb-4">
                    {success
                        ? `Your donation of ${donation.amount.toFixed(2)} ${donation.currency} to ${donation.fund?.name} was received.`
                        : 'This payment was not completed. No amount was charged. You can try again from the donate page.'}
                </p>
                <div className="d-flex gap-2 justify-content-center">
                    <Link href="/donations/history" className="btn btn-outline-primary">View History</Link>
                    <Link href="/donations" className="btn btn-primary">
                        {success ? 'Donate Again' : 'Try Again'}
                    </Link>
                </div>
            </div>
        </AppLayout>
    )
}
