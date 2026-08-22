import { router } from '@inertiajs/react'
import { useTranslation } from 'react-i18next'
import AppLayout from '@/components/layouts/AppLayout'
import { useConfirm } from '@/components/shared/ConfirmDialog'
import type { Token } from '@/types'

interface AppsProps {
    tokens: Token[]
}

export default function Apps({ tokens }: AppsProps) {
    const { t } = useTranslation('account')
    const confirm = useConfirm()

    const revoke = async (token: Token) => {
        if (await confirm(t('apps.revokeConfirm', { name: token.client.name }), { confirmLabel: t('apps.revoke') })) {
            router.delete(`/account/apps/${token.id}`)
        }
    }

    if (tokens.length === 0) {
        return (
            <AppLayout title={t('apps.title')}>
                <div className="text-center py-5">
                    <i className="bi bi-plug text-ava-muted icon-fs-48" />
                    <p className="text-ava-muted mt-3">{t('apps.noAppsYet')}</p>
                </div>
            </AppLayout>
        )
    }

    return (
        <AppLayout title={t('apps.title')}>
            <div className="ava-card">
                <ul className="list-group list-group-flush">
                    {tokens.map((token) => (
                        <li key={token.id} className="list-group-item d-flex justify-content-between align-items-center p-3">
                            <div>
                                <div className="fw-semibold">{token.client.name}</div>
                                <div className="mb-1">
                                    {token.scopes.map((scope) => (
                                        <span key={scope} className="scope-chip me-1">
                                            {scope}
                                        </span>
                                    ))}
                                </div>
                                <div className="text-ava-muted small">{t('apps.expires', { date: token.expires_at })}</div>
                            </div>
                            <button type="button" className="btn btn-outline-primary btn-sm" onClick={() => revoke(token)}>
                                {t('apps.revoke')}
                            </button>
                        </li>
                    ))}
                </ul>
            </div>
        </AppLayout>
    )
}
