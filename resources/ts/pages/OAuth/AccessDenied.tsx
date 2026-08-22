import { router } from '@inertiajs/react'
import { Trans, useTranslation } from 'react-i18next'
import AuthLayout from '@/components/layouts/AuthLayout'

interface AccessDeniedProps {
    client: { name: string }
}

export default function AccessDenied({ client }: AccessDeniedProps) {
    const { t } = useTranslation('oauth')

    return (
        <AuthLayout>
            <div className="text-center">
                <div className="icon-circle text-ava-primary mx-auto mb-3">
                    <i className="bi bi-shield-lock" />
                </div>
                <h4 className="mb-2">{t('accessDenied.title')}</h4>
                <p className="text-ava-muted mb-4">
                    <Trans
                        t={t}
                        i18nKey="accessDenied.description"
                        values={{ clientName: client.name }}
                        components={{ strong: <strong /> }}
                    />
                </p>
                <button type="button" className="btn btn-primary w-100" onClick={() => router.visit('/account')}>
                    {t('accessDenied.backToAccount')}
                </button>
            </div>
        </AuthLayout>
    )
}
