import { useState } from 'react'
import type { FormEvent } from 'react'
import { router, useForm, usePage } from '@inertiajs/react'
import { useTranslation } from 'react-i18next'
import AppLayout from '@/components/layouts/AppLayout'
import type { DonationGateway, Fund, SharedProps } from '@/types'

interface CreateProps {
    funds: Fund[]
}

interface DonationForm {
    fund_id: string
    amount: string
    gateway: DonationGateway
}

const QUICK_AMOUNTS = [100, 250, 500, 1000]
const GATEWAY_VALUES: DonationGateway[] = ['nbe', 'cib']

export default function Create({ funds }: CreateProps) {
    const { t } = useTranslation('donations')
    const { auth } = usePage<SharedProps>().props
    const [reminderProcessing, setReminderProcessing] = useState(false)
    const wantsDonationReminders = auth.user?.wants_donation_reminders ?? false
    const form = useForm<DonationForm>({
        fund_id: funds[0] ? String(funds[0].id) : '',
        amount: '',
        gateway: 'nbe',
    })

    const submit = (e: FormEvent) => {
        e.preventDefault()
        form.post('/donations')
    }

    const toggleDonationReminders = () => {
        setReminderProcessing(true)
        router.post(
            wantsDonationReminders ? '/donations/reminders/disable' : '/donations/reminders/enable',
            {},
            { preserveScroll: true, onFinish: () => setReminderProcessing(false) },
        )
    }

    return (
        <AppLayout title={t('create.title')}>
            <div
                className="ava-card p-3 mb-3 d-flex align-items-center justify-content-between gap-3 flex-wrap"
                style={{ maxWidth: 520 }}
            >
                <div className="d-flex align-items-center gap-2">
                    <i className="bi bi-heart text-ava-primary fs-4" />
                    <div>
                        <div className="fw-semibold">{t('reminder.title')}</div>
                        <div className="text-ava-muted text-tiny">
                            {wantsDonationReminders ? t('reminder.enabledHelp') : t('reminder.disabledHelp')}
                        </div>
                    </div>
                </div>
                <button
                    type="button"
                    className={`btn btn-sm ${wantsDonationReminders ? 'btn-outline-secondary' : 'btn-primary'}`}
                    disabled={reminderProcessing}
                    onClick={toggleDonationReminders}
                >
                    {wantsDonationReminders ? t('reminder.disable') : t('reminder.enable')}
                </button>
            </div>

            <div className="ava-card p-3 p-md-4" style={{ maxWidth: 520 }}>
                <p className="text-ava-muted mb-4">
                    {t('create.intro')}
                </p>

                <form onSubmit={submit}>
                    <div className="mb-3">
                        <label className="form-label" htmlFor="fund_id">{t('create.giveTo')}</label>
                        <select
                            id="fund_id"
                            className="form-select"
                            value={form.data.fund_id}
                            onChange={(e) => form.setData('fund_id', e.target.value)}
                        >
                            {funds.map((fund) => (
                                <option key={fund.id} value={fund.id}>{fund.name}</option>
                            ))}
                        </select>
                        {form.errors.fund_id && <div className="text-danger small mt-1">{form.errors.fund_id}</div>}
                    </div>

                    <div className="mb-3">
                        <label className="form-label" htmlFor="amount">{t('create.amountEgp')}</label>
                        <div className="d-flex gap-2 mb-2 flex-wrap">
                            {QUICK_AMOUNTS.map((amount) => (
                                <button
                                    key={amount}
                                    type="button"
                                    className={`btn btn-sm ${form.data.amount === String(amount) ? 'btn-primary' : 'btn-outline-primary'}`}
                                    onClick={() => form.setData('amount', String(amount))}
                                >
                                    {amount}
                                </button>
                            ))}
                        </div>
                        <input
                            id="amount"
                            type="number"
                            min={10}
                            step="0.01"
                            className="form-control"
                            placeholder={t('create.otherAmount')}
                            value={form.data.amount}
                            onChange={(e) => form.setData('amount', e.target.value)}
                        />
                        {form.errors.amount && <div className="text-danger small mt-1">{form.errors.amount}</div>}
                    </div>

                    <div className="mb-3">
                        <label className="form-label">{t('create.payVia')}</label>
                        {GATEWAY_VALUES.map((gateway) => (
                            <div key={gateway} className="form-check mb-2">
                                <input
                                    id={`gateway-${gateway}`}
                                    type="radio"
                                    className="form-check-input"
                                    name="gateway"
                                    checked={form.data.gateway === gateway}
                                    onChange={() => form.setData('gateway', gateway)}
                                />
                                <label className="form-check-label" htmlFor={`gateway-${gateway}`}>
                                    <span className="fw-semibold">{t(`gateways.${gateway}.label`)}</span>
                                    <div className="text-ava-muted text-tiny">{t(`gateways.${gateway}.description`)}</div>
                                </label>
                            </div>
                        ))}
                        {form.errors.gateway && <div className="text-danger small mt-1">{form.errors.gateway}</div>}
                    </div>

                    <button type="submit" className="btn btn-primary w-100 mt-4" disabled={form.processing}>
                        {form.processing ? t('create.redirecting') : t('create.donateNow')}
                    </button>
                </form>
            </div>
        </AppLayout>
    )
}
