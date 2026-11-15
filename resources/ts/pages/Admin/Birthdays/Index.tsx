import { router } from '@inertiajs/react'
import { useState } from 'react'
import { useTranslation } from 'react-i18next'
import AdminLayout from '@/components/layouts/AdminLayout'
import EmptyState from '@/components/shared/EmptyState'
import UserCell from '@/components/shared/UserCell'
import { calculateAge, formatDateDMY } from '@/lib/date'
import type { User } from '@/types'

interface IndexProps {
    users: User[]
    date: string
    to: string | null
    today: string
}

export default function Index({ users, date, to, today }: IndexProps) {
    const { t } = useTranslation('admin')
    const [rangeMode, setRangeMode] = useState(to !== null)
    const isRange = to !== null
    const isToday = !isRange && date === today

    const visit = (from: string, until: string | null) => {
        const params: Record<string, string> = {}

        if (from !== today || until) {
            params.date = from
        }

        if (until) {
            params.to = until
        }

        router.get('/admin/birthdays', params, { preserveState: true, preserveScroll: true })
    }

    const goToDate = (newDate: string) => {
        // Keep the end of the range only while it is still after the new start.
        visit(newDate, rangeMode && to && to > newDate ? to : null)
    }

    const toggleRange = (enabled: boolean) => {
        setRangeMode(enabled)

        if (!enabled && to) {
            visit(date, null)
        }
    }

    /** The next occurrence of the user's birthday on or after the range start. */
    const birthdayInRange = (dob: string | null): string | null => {
        if (!dob) {
            return null
        }

        const [, month, day] = dob.split('-')
        const startYear = Number(date.split('-')[0])
        const candidate = `${startYear}-${month}-${day}`

        return candidate >= date ? candidate : `${startYear + 1}-${month}-${day}`
    }

    const wishHappyBirthday = (user: User) => {
        if (!user.phone) {
            return
        }

        const message = t('birthdays.wishWhatsAppMessage', { name: user.name })
        const phoneDigits = user.phone.replace(/\D/g, '')
        window.open(`https://wa.me/${phoneDigits}?text=${encodeURIComponent(message)}`, '_blank', 'noopener,noreferrer')
    }

    return (
        <AdminLayout title={t('birthdays.title')}>
            <div className="ava-card p-3 mb-4">
                <div className="row g-3 align-items-end">
                    <div className="col-12 col-sm-4">
                        <label className="form-label text-ava-muted text-tiny mb-1" htmlFor="birthday-date-picker">
                            {rangeMode ? t('birthdays.from') : t('birthdays.date')}
                        </label>
                        <input
                            id="birthday-date-picker"
                            type="date"
                            className="form-control"
                            value={date}
                            onChange={(e) => e.target.value && goToDate(e.target.value)}
                        />
                    </div>
                    {rangeMode && (
                        <div className="col-12 col-sm-4">
                            <label className="form-label text-ava-muted text-tiny mb-1" htmlFor="birthday-to-picker">
                                {t('birthdays.to')}
                            </label>
                            <input
                                id="birthday-to-picker"
                                type="date"
                                className="form-control"
                                value={to ?? ''}
                                min={date}
                                onChange={(e) => visit(date, e.target.value && e.target.value > date ? e.target.value : null)}
                            />
                        </div>
                    )}
                    <div className="col-12 col-sm-auto">
                        <div className="form-check form-switch mb-2">
                            <input
                                id="birthday-range-toggle"
                                type="checkbox"
                                className="form-check-input"
                                checked={rangeMode}
                                onChange={(e) => toggleRange(e.target.checked)}
                            />
                            <label className="form-check-label" htmlFor="birthday-range-toggle">
                                {t('birthdays.range')}
                            </label>
                        </div>
                    </div>
                    <div className="col-12 col-sm-auto">
                        {(date !== today || isRange) && (
                            <button
                                type="button"
                                className="btn btn-outline-primary"
                                onClick={() => {
                                    setRangeMode(false)
                                    visit(today, null)
                                }}
                            >
                                {t('birthdays.today')}
                            </button>
                        )}
                    </div>
                </div>
            </div>

            <p className="text-ava-muted mb-3">
                {isRange
                    ? t('birthdays.rangeDescription', { from: date, to })
                    : isToday
                      ? t('birthdays.todayDescription')
                      : t('birthdays.onDateDescription', { date })}
            </p>

            <div className="ava-card">
                <div className="table-responsive">
                    <table className="table mb-0">
                        <thead>
                            <tr>
                                <th>{t('table.name')}</th>
                                <th>{t('table.stage')}</th>
                                {isRange && <th>{t('birthdays.table.birthday')}</th>}
                                <th>{t('birthdays.table.dob')}</th>
                                <th>{t('birthdays.table.age')}</th>
                                <th />
                            </tr>
                        </thead>
                        <tbody>
                            {users.map((user) => (
                                <tr
                                    key={user.id}
                                    role="button"
                                    className="cursor-pointer"
                                    onClick={() => router.visit(`/admin/users/${user.id}`)}
                                >
                                    <td>
                                        <UserCell user={user} />
                                        {isToday && <span className="ms-1">🎉</span>}
                                    </td>
                                    <td>{user.stage.name}</td>
                                    {isRange && <td>{formatDateDMY(birthdayInRange(user.date_of_birth))}</td>}
                                    <td className="text-ava-muted">{formatDateDMY(user.date_of_birth)}</td>
                                    <td className="text-ava-muted">
                                        {calculateAge(user.date_of_birth, birthdayInRange(user.date_of_birth) ?? date)}
                                    </td>
                                    <td className="text-nowrap" onClick={(e) => e.stopPropagation()}>
                                        {user.phone && (
                                            <button
                                                type="button"
                                                className="btn btn-sm btn-outline-primary"
                                                onClick={() => wishHappyBirthday(user)}
                                            >
                                                <i className="bi bi-whatsapp me-1" />
                                                {t('birthdays.wish')}
                                            </button>
                                        )}
                                    </td>
                                </tr>
                            ))}
                        </tbody>
                    </table>
                    {users.length === 0 && <EmptyState icon="bi-cake2" message={t('birthdays.empty')} />}
                </div>
            </div>
        </AdminLayout>
    )
}
