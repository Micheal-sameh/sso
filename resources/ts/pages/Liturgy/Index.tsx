import { useState } from 'react'
import { useTranslation } from 'react-i18next'
import AppLayout from '@/components/layouts/AppLayout'
import InAppLinkModal from '@/components/shared/InAppLinkModal'

interface LiturgyLink {
    key: string
    label: string
    icon: string
    url: string
}

export default function Index() {
    const { t, i18n } = useTranslation('liturgy')
    const [openUrl, setOpenUrl] = useState<string | null>(null)
    const dir = i18n.language === 'ar' ? 'rtl' : 'ltr'

    const links: LiturgyLink[] = [
        {
            key: 'bible',
            label: t('bible'),
            icon: 'bi-book',
            url: i18n.language === 'ar' ? 'https://st-takla.org/Holy-Bible_.html' : 'https://st-takla.org/Bibles/Holy-Bible.html',
        },
        {
            key: 'agpeya',
            label: t('agpeya'),
            icon: 'bi-clock-history',
            url: 'https://agpeya.org/',
        },
    ]

    return (
        <AppLayout title={t('title')}>
            <div className="ava-card" dir={dir}>
                <ul className="list-group list-group-flush">
                    {links.map((link) => (
                        <li key={link.key} className="list-group-item p-0">
                            <button
                                type="button"
                                className="btn d-flex align-items-center gap-3 p-3 w-100 text-start text-reset"
                                onClick={() => setOpenUrl(link.url)}
                            >
                                <i className={`bi ${link.icon} fs-5 text-ava-secondary`} />
                                <span className="flex-grow-1">{link.label}</span>
                                <i className="bi bi-box-arrow-up-right text-ava-muted" />
                            </button>
                        </li>
                    ))}
                </ul>
            </div>

            <InAppLinkModal url={openUrl} onClose={() => setOpenUrl(null)} />
        </AppLayout>
    )
}
