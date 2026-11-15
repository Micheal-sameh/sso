import { router } from '@inertiajs/react'
import { useTranslation } from 'react-i18next'
import AdminLayout from '@/components/layouts/AdminLayout'
import EmptyState from '@/components/shared/EmptyState'
import UserCell from '@/components/shared/UserCell'
import { calculateAge, formatDateDMY } from '@/lib/date'
import type { User } from '@/types'

interface IndexProps {
    users: User[]
    date: string
    today: string
}

export default function Index({ users, date, today }: IndexProps) {
    const { t } = useTranslation('admin')
    const isToday = date === today

    const goToDate = (newDate: string) => {
        router.get('/admin/birthdays', newDate === today ? {} : { date: newDate }, { preserveState: true, preserveScroll: true })
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
                    <div className="col-12 col-sm-6">
                        <label className="form-label text-ava-muted text-tiny mb-1" htmlFor="birthday-date-picker">
                            {t('birthdays.date')}
                        </label>
                        <input
                            id="birthday-date-picker"
                            type="date"
                            className="form-control"
                            value={date}
                            onChange={(e) => e.target.value && goToDate(e.target.value)}
                        />
                    </div>
                    <div className="col-12 col-sm-auto">
                        {!isToday && (
                            <button type="button" className="btn btn-outline-primary" onClick={() => goToDate(today)}>
                                {t('birthdays.today')}
                            </button>
                        )}
                    </div>
                </div>
            </div>

            <p className="text-ava-muted mb-3">
                {isToday ? t('birthdays.todayDescription') : t('birthdays.onDateDescription', { date })}
            </p>

            <div className="ava-card">
                <div className="table-responsive">
                    <table className="table mb-0">
                        <thead>
                            <tr>
                                <th>{t('table.name')}</th>
                                <th>{t('table.stage')}</th>
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
                                    <td className="text-ava-muted">{formatDateDMY(user.date_of_birth)}</td>
                                    <td className="text-ava-muted">{calculateAge(user.date_of_birth, date)}</td>
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
