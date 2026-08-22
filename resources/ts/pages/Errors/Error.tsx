import { Link } from '@inertiajs/react'
import { useTranslation } from 'react-i18next'
import AuthLayout from '@/components/layouts/AuthLayout'
import AvaLogo from '@/components/shared/AvaLogo'

interface ErrorProps {
    status: number
    message?: string
    variant?: 'database'
}

const STATUS_KEYS: Record<number, { icon: string; titleKey: string; descriptionKey: string }> = {
    401: {
        icon: 'bi-shield-lock',
        titleKey: 'error.status401.title',
        descriptionKey: 'error.status401.description',
    },
    403: {
        icon: 'bi-slash-circle',
        titleKey: 'error.status403.title',
        descriptionKey: 'error.status403.description',
    },
    404: {
        icon: 'bi-signpost-split',
        titleKey: 'error.status404.title',
        descriptionKey: 'error.status404.description',
    },
    419: {
        icon: 'bi-hourglass-split',
        titleKey: 'error.status419.title',
        descriptionKey: 'error.status419.description',
    },
    500: {
        icon: 'bi-exclamation-octagon',
        titleKey: 'error.status500.title',
        descriptionKey: 'error.status500.description',
    },
    502: {
        icon: 'bi-hdd-network',
        titleKey: 'error.status502.title',
        descriptionKey: 'error.status502.description',
    },
    503: {
        icon: 'bi-tools',
        titleKey: 'error.status503.title',
        descriptionKey: 'error.status503.description',
    },
}

const DEFAULT_KEYS = {
    icon: 'bi-exclamation-triangle',
    titleKey: 'error.defaultTitle',
    descriptionKey: 'error.defaultDescription',
}

const VARIANT_KEYS: Record<string, { icon: string; titleKey: string; descriptionKey: string }> = {
    database: {
        icon: 'bi-hdd-network',
        titleKey: 'error.databaseTitle',
        descriptionKey: 'error.databaseDescription',
    },
}

export default function Error({ status, message, variant }: ErrorProps) {
    const { t } = useTranslation('errors')
    const content = (variant && VARIANT_KEYS[variant]) || STATUS_KEYS[status] || DEFAULT_KEYS

    return (
        <AuthLayout>
            <div className="text-center py-3">
                <div className="d-flex justify-content-center mb-4">
                    <AvaLogo size={40} showText />
                </div>

                <div className="icon-tile text-ava-secondary mx-auto mb-4" style={{ width: 72, height: 72 }}>
                    <i className={`bi ${content.icon}`} style={{ fontSize: 32 }} />
                </div>

                <p className="text-ava-muted text-tiny fw-semibold mb-1 text-uppercase">{t('error.errorLabel', { status })}</p>
                <h3 className="mb-2">{t(content.titleKey)}</h3>
                <p className="text-ava-muted mb-4">{message || t(content.descriptionKey)}</p>

                <div className="d-flex justify-content-center gap-2">
                    <Link href="/" className="btn btn-primary">
                        <i className="bi bi-house me-1" />
                        {t('error.goToHomepage')}
                    </Link>
                    <button type="button" className="btn btn-outline-primary" onClick={() => window.history.back()}>
                        <i className="bi bi-arrow-left me-1" />
                        {t('error.goBack')}
                    </button>
                </div>
            </div>
        </AuthLayout>
    )
}
