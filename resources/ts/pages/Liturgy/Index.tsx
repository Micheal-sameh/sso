import { Link } from '@inertiajs/react'
import { useTranslation } from 'react-i18next'
import AppLayout from '@/components/layouts/AppLayout'

export default function Index() {
    const { t, i18n } = useTranslation('liturgy')
    const dir = i18n.language === 'ar' ? 'rtl' : 'ltr'
    const chevronIcon = i18n.language === 'ar' ? 'bi-chevron-left' : 'bi-chevron-right'

    return (
        <AppLayout title={t('title')}>
            <div className="ava-card" dir={dir}>
                <ul className="list-group list-group-flush">
                    <li className="list-group-item p-0">
                        <Link href="/coptic" className="d-flex align-items-center gap-3 p-3 text-decoration-none text-reset">
                            <i className="bi bi-calendar-event fs-5 text-ava-secondary" />
                            <span className="flex-grow-1">{t('katamaros')}</span>
                            <i className={`bi ${chevronIcon} text-ava-muted`} />
                        </Link>
                    </li>
                    <li className="list-group-item p-0">
                        <Link href="/liturgy/bible" className="d-flex align-items-center gap-3 p-3 text-decoration-none text-reset">
                            <i className="bi bi-book fs-5 text-ava-secondary" />
                            <span className="flex-grow-1">{t('bible')}</span>
                            <i className={`bi ${chevronIcon} text-ava-muted`} />
                        </Link>
                    </li>
                    <li className="list-group-item p-0">
                        <Link href="/liturgy/agpeya" className="d-flex align-items-center gap-3 p-3 text-decoration-none text-reset">
                            <i className="bi bi-clock-history fs-5 text-ava-secondary" />
                            <span className="flex-grow-1">{t('agpeya')}</span>
                            <i className={`bi ${chevronIcon} text-ava-muted`} />
                        </Link>
                    </li>
                </ul>
            </div>
        </AppLayout>
    )
}
