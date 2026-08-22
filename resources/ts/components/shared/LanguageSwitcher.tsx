import { router, usePage } from '@inertiajs/react'
import { useTranslation } from 'react-i18next'
import type { SharedProps } from '@/types'

export default function LanguageSwitcher() {
    const { t } = useTranslation('common')
    const { locale } = usePage<SharedProps>().props
    const nextLocale = locale === 'ar' ? 'en' : 'ar'

    const switchLocale = () => {
        router.post(
            `/locale/${nextLocale}`,
            {},
            {
                preserveScroll: true,
                onSuccess: () => window.location.reload(),
            },
        )
    }

    return (
        <button
            type="button"
            className="btn btn-outline-primary btn-sm"
            aria-label={t('locale.switchLanguage')}
            onClick={switchLocale}
        >
            {nextLocale === 'ar' ? t('locale.arabic') : t('locale.english')}
        </button>
    )
}
