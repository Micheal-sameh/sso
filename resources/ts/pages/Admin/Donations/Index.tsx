import type { FormEvent } from 'react'
import { router, useForm } from '@inertiajs/react'
import { useTranslation } from 'react-i18next'
import AdminLayout from '@/components/layouts/AdminLayout'
import Pagination from '@/components/shared/Pagination'
import type { Donation, DonationStatus, DonationTotals, Fund, PaginatedData } from '@/types'

interface IndexProps {
    donations: PaginatedData<Donation>
    totals: DonationTotals
    funds: Fund[]
    filters: { status?: string; fund_id?: string; gateway?: string; from?: string; to?: string }
}

const STATUS_BADGES: Record<DonationStatus, string> = {
    pending: 'bg-warning text-dark',
    paid: 'bg-success',
    failed: 'bg-danger',
    cancelled: 'bg-secondary',
    refunded: 'bg-info text-dark',
}

function formatEgp(amount: number): string {
    return `${amount.toLocaleString('en-EG', { minimumFractionDigits: 2, maximumFractionDigits: 2 })} EGP`
}

export default function Index({ donations, totals, funds, filters }: IndexProps) {
    const { t } = useTranslation('donations')
    const form = useForm({
        status: filters.status ?? '',
        fund_id: filters.fund_id ?? '',
        gateway: filters.gateway ?? '',
        from: filters.from ?? '',
        to: filters.to ?? '',
    })

    const applyFilters = (e: FormEvent) => {
        e.preventDefault()
        router.get('/admin/donations', form.data, { preserveState: true })
    }

    const exportUrl = `/admin/donations/export?${new URLSearchParams(form.data as Record<string, string>).toString()}`

    return (
        <AdminLayout
            title={t('admin.index.title')}
            action={
                <a href={exportUrl} className="btn btn-outline-primary btn-sm">
                    <i className="bi bi-download me-1" />
                    {t('admin.index.exportCsv')}
                </a>
            }
        >
            <div className="row g-3 mb-4">
                <div className="col-12 col-md-4">
                    <div className="ava-card p-3">
                        <div className="text-ava-muted text-tiny">{t('admin.index.totalReceived')}</div>
                        <div className="fs-4 fw-semibold">{formatEgp(totals.total_paid)}</div>
                    </div>
                </div>
                <div className="col-12 col-md-4">
                    <div className="ava-card p-3">
                        <div className="text-ava-muted text-tiny">{t('admin.index.thisMonth')}</div>
                        <div className="fs-4 fw-semibold">{formatEgp(totals.this_month)}</div>
                    </div>
                </div>
                <div className="col-12 col-md-4">
                    <div className="ava-card p-3">
                        <div className="text-ava-muted text-tiny">{t('admin.index.thisYear')}</div>
                        <div className="fs-4 fw-semibold">{formatEgp(totals.this_year)}</div>
                    </div>
                </div>
            </div>

            <form onSubmit={applyFilters} className="ava-card p-3 mb-4">
                <div className="row g-2 align-items-end">
                    <div className="col-6 col-md-2">
                        <label className="form-label text-tiny">{t('admin.index.filters.status')}</label>
                        <select className="form-select form-select-sm" value={form.data.status} onChange={(e) => form.setData('status', e.target.value)}>
                            <option value="">{t('admin.index.filters.all')}</option>
                            <option value="pending">{t('status.pending')}</option>
                            <option value="paid">{t('status.paid')}</option>
                            <option value="failed">{t('status.failed')}</option>
                            <option value="refunded">{t('status.refunded')}</option>
                        </select>
                    </div>
                    <div className="col-6 col-md-2">
                        <label className="form-label text-tiny">{t('admin.index.filters.fund')}</label>
                        <select className="form-select form-select-sm" value={form.data.fund_id} onChange={(e) => form.setData('fund_id', e.target.value)}>
                            <option value="">{t('admin.index.filters.all')}</option>
                            {funds.map((fund) => (
                                <option key={fund.id} value={fund.id}>{fund.name}</option>
                            ))}
                        </select>
                    </div>
                    <div className="col-6 col-md-2">
                        <label className="form-label text-tiny">{t('admin.index.filters.gateway')}</label>
                        <select className="form-select form-select-sm" value={form.data.gateway} onChange={(e) => form.setData('gateway', e.target.value)}>
                            <option value="">{t('admin.index.filters.all')}</option>
                            <option value="nbe">NBE</option>
                            <option value="cib">CIB</option>
                        </select>
                    </div>
                    <div className="col-6 col-md-2">
                        <label className="form-label text-tiny">{t('admin.index.filters.from')}</label>
                        <input type="date" className="form-control form-control-sm" value={form.data.from} onChange={(e) => form.setData('from', e.target.value)} />
                    </div>
                    <div className="col-6 col-md-2">
                        <label className="form-label text-tiny">{t('admin.index.filters.to')}</label>
                        <input type="date" className="form-control form-control-sm" value={form.data.to} onChange={(e) => form.setData('to', e.target.value)} />
                    </div>
                    <div className="col-6 col-md-2">
                        <button type="submit" className="btn btn-primary btn-sm w-100">{t('admin.index.filters.filter')}</button>
                    </div>
                </div>
            </form>

            <div className="ava-card">
                <div className="table-responsive">
                    <table className="table mb-0">
                        <thead>
                            <tr>
                                <th>{t('admin.index.table.date')}</th>
                                <th>{t('admin.index.table.donor')}</th>
                                <th>{t('admin.index.table.fund')}</th>
                                <th>{t('admin.index.table.amount')}</th>
                                <th>{t('admin.index.table.gateway')}</th>
                                <th>{t('admin.index.table.status')}</th>
                            </tr>
                        </thead>
                        <tbody>
                            {donations.data.map((donation) => (
                                <tr key={donation.id}>
                                    <td className="text-ava-muted">{donation.created_at}</td>
                                    <td>{donation.donor?.name ?? '—'}</td>
                                    <td>
                                        {donation.fund?.name}
                                        {donation.is_recurring && <span className="badge bg-light text-dark ms-2">{t('admin.index.monthly')}</span>}
                                    </td>
                                    <td className="fw-semibold">{donation.amount.toFixed(2)} {donation.currency}</td>
                                    <td className="text-uppercase text-ava-muted">{donation.gateway}</td>
                                    <td><span className={`badge ${STATUS_BADGES[donation.status]}`}>{t(`status.${donation.status}`)}</span></td>
                                </tr>
                            ))}
                            {donations.data.length === 0 && (
                                <tr>
                                    <td colSpan={6} className="text-center text-ava-muted py-4">{t('admin.index.empty')}</td>
                                </tr>
                            )}
                        </tbody>
                    </table>
                </div>
            </div>
            <div className="mt-3">
                <Pagination pagination={donations} />
            </div>
        </AdminLayout>
    )
}
