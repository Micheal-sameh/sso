import { useState } from 'react'
import { Link } from '@inertiajs/react'
import { useTranslation } from 'react-i18next'
import AppLayout from '@/components/layouts/AppLayout'
import InAppLinkModal from '@/components/shared/InAppLinkModal'

export default function Index() {
    const { t, i18n } = useTranslation('liturgy')
    const [openUrl, setOpenUrl] = useState<string | null>(null)
    const dir = i18n.language === 'ar' ? 'rtl' : 'ltr'
    const chevronIcon = i18n.language === 'ar' ? 'bi-chevron-left' : 'bi-chevron-right'

    return (
        <AppLayout title={t('title')}>
            <div className="ava-card" dir={dir}>
                <ul className="list-group list-group-flush">
                    <li className="list-group-item p-0">
                        <Link href="/liturgy/bible" className="d-flex align-items-center gap-3 p-3 text-decoration-none text-reset">
                            <i className="bi bi-book fs-5 text-ava-secondary" />
                            <span className="flex-grow-1">{t('bible')}</span>
                            <i className={`bi ${chevronIcon} text-ava-muted`} />
                        </Link>
                    </li>
                    <li className="list-group-item p-0">
                        <button
                            type="button"
                            className="btn d-flex align-items-center gap-3 p-3 w-100 text-start text-reset"
                            onClick={() => setOpenUrl('https://agpeya.org/')}
                        >
                            <i className="bi bi-clock-history fs-5 text-ava-secondary" />
                            <span className="flex-grow-1">{t('agpeya')}</span>
                            <i className="bi bi-box-arrow-up-right text-ava-muted" />
                        </button>
                    </li>
                </ul>
            </div>

            <InAppLinkModal url={openUrl} onClose={() => setOpenUrl(null)} />
        </AppLayout>
    )
}
