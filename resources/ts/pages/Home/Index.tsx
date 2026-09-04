import { usePage } from '@inertiajs/react'
import { useTranslation } from 'react-i18next'
import AppLayout from '@/components/layouts/AppLayout'
import { isBirthdayToday } from '@/lib/date'
import type { SharedProps } from '@/types'

export default function Index() {
    const { t } = useTranslation('home')
    const { auth } = usePage<SharedProps>().props
    const isBirthday = isBirthdayToday(auth.user?.date_of_birth)

    return (
        <AppLayout>
            {isBirthday && (
                <div className="ava-card mb-4 p-3 d-flex align-items-center gap-2">
                    <i className="bi bi-balloon-heart-fill text-ava-primary fs-4" />
                    <span className="fw-semibold">🎉 {t('home.happyBirthday', { name: auth.user?.name })}</span>
                </div>
            )}

            <h3 className="mb-4">{t('home.welcome', { name: auth.user?.name })}</h3>
        </AppLayout>
    )
}
