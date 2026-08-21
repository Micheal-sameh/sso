import AppLayout from '@/components/layouts/AppLayout'
import Pagination from '@/components/shared/Pagination'
import type { Donation, DonationStatus, PaginatedData } from '@/types'

interface HistoryProps {
    donations: PaginatedData<Donation>
}

const STATUS_BADGES: Record<DonationStatus, string> = {
    pending: 'bg-warning text-dark',
    paid: 'bg-success',
    failed: 'bg-danger',
    cancelled: 'bg-secondary',
    refunded: 'bg-info text-dark',
}

export default function History({ donations }: HistoryProps) {
    return (
        <AppLayout title="My Donations">
            <div className="ava-card">
                <div className="table-responsive">
                    <table className="table mb-0">
                        <thead>
                            <tr>
                                <th>Date</th>
                                <th>Fund</th>
                                <th>Amount</th>
                                <th>Gateway</th>
                                <th>Status</th>
                            </tr>
                        </thead>
                        <tbody>
                            {donations.data.map((donation) => (
                                <tr key={donation.id}>
                                    <td className="text-ava-muted">{donation.created_at}</td>
                                    <td>
                                        {donation.fund?.name}
                                        {donation.is_recurring && <span className="badge bg-light text-dark ms-2">Monthly</span>}
                                    </td>
                                    <td className="fw-semibold">{donation.amount.toFixed(2)} {donation.currency}</td>
                                    <td className="text-uppercase text-ava-muted">{donation.gateway}</td>
                                    <td><span className={`badge ${STATUS_BADGES[donation.status]}`}>{donation.status}</span></td>
                                </tr>
                            ))}
                            {donations.data.length === 0 && (
                                <tr>
                                    <td colSpan={5} className="text-center text-ava-muted py-4">
                                        No donations yet.
                                    </td>
                                </tr>
                            )}
                        </tbody>
                    </table>
                </div>
            </div>
            <div className="mt-3">
                <Pagination links={donations.links} />
            </div>
        </AppLayout>
    )
}
