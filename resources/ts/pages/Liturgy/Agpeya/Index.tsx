import { useState } from 'react'
import { Link } from '@inertiajs/react'
import { useTranslation } from 'react-i18next'
import AppLayout from '@/components/layouts/AppLayout'
import InAppLinkModal from '@/components/shared/InAppLinkModal'

const HOURS = [
    { key: 'prime', file: 'Agbeya_01_Prime' },
    { key: 'third', file: 'Agbeya_03_Terce' },
    { key: 'sixth', file: 'Agbeya_06_Sext' },
    { key: 'ninth', file: 'Agbeya_09_None' },
    { key: 'vespers', file: 'Agbeya_11_Vespers' },
    { key: 'compline', file: 'Agbeya_12_Compline' },
    { key: 'midnight', file: 'Agbeya_Midnight' },
    { key: 'veil', file: 'Agbeya_Viel' },
] as const

export default function Index() {
    const { t, i18n } = useTranslation('liturgy')
    const [openUrl, setOpenUrl] = useState<string | null>(null)
    const isArabic = i18n.language === 'ar'
    const dir = isArabic ? 'rtl' : 'ltr'
    const chevronIcon = isArabic ? 'bi-chevron-left' : 'bi-chevron-right'

    return (
        <AppLayout title={t('agpeya')}>
            <div className="ava-card" dir={dir}>
                <ul className="list-group list-group-flush">
                    {HOURS.map((hour) => (
                        <li key={hour.key} className="list-group-item p-0">
                            <button
                                type="button"
                                className="btn d-flex align-items-center gap-3 p-3 w-100 text-start text-reset"
                                onClick={() => setOpenUrl(`https://st-takla.org/Agpeya/${hour.file}${isArabic ? '_' : ''}.html`)}
                            >
                                <span className="flex-grow-1">{t(`agpeyaHours.${hour.key}`)}</span>
                                <i className={`bi ${chevronIcon} text-ava-muted`} />
                            </button>
                        </li>
                    ))}
                </ul>
            </div>

            <Link href="/liturgy" className="btn btn-link p-0 mt-3">
                <i className={`bi ${dir === 'rtl' ? 'bi-arrow-right' : 'bi-arrow-left'} me-1`} />
                {t('title')}
            </Link>

            <InAppLinkModal url={openUrl} onClose={() => setOpenUrl(null)} />
        </AppLayout>
    )
}
