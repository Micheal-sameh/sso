import { Link } from '@inertiajs/react'
import { useTranslation } from 'react-i18next'
import AppLayout from '@/components/layouts/AppLayout'

interface MoreLink {
    href: string
    label: string
    icon: string
}

export default function Index() {
    const { t, i18n } = useTranslation('more')
    const chevronIcon = i18n.language === 'ar' ? 'bi-chevron-left' : 'bi-chevron-right'

    const links: MoreLink[] = [
        { href: '/coptic', label: t('readings'), icon: 'bi-calendar-event' },
        { href: '/liturgy', label: t('liturgy'), icon: 'bi-book' },
    ]

    return (
        <AppLayout title={t('title')}>
            <div className="ava-card" dir={i18n.language === 'ar' ? 'rtl' : 'ltr'}>
                <ul className="list-group list-group-flush">
                    {links.map((link) => (
                        <li key={link.href} className="list-group-item p-0">
                            <Link href={link.href} className="d-flex align-items-center gap-3 p-3 text-decoration-none text-reset">
                                <i className={`bi ${link.icon} fs-5 text-ava-secondary`} />
                                <span className="flex-grow-1">{link.label}</span>
                                <i className={`bi ${chevronIcon} text-ava-muted`} />
                            </Link>
                        </li>
                    ))}
                </ul>
            </div>
        </AppLayout>
    )
}
