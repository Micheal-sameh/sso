import { router } from '@inertiajs/react'
import AppLayout from '@/components/layouts/AppLayout'
import { useConfirm } from '@/components/shared/ConfirmDialog'
import type { Token } from '@/types'

interface AppsProps {
    tokens: Token[]
}

export default function Apps({ tokens }: AppsProps) {
    const confirm = useConfirm()

    const revoke = async (token: Token) => {
        if (await confirm(`Revoke access for ${token.client.name}?`, { confirmLabel: 'Revoke' })) {
            router.delete(`/account/apps/${token.id}`)
        }
    }

    if (tokens.length === 0) {
        return (
            <AppLayout title="Connected Apps">
                <div className="text-center py-5">
                    <i className="bi bi-plug text-ava-muted icon-fs-48" />
                    <p className="text-ava-muted mt-3">No connected apps yet</p>
                </div>
            </AppLayout>
        )
    }

    return (
        <AppLayout title="Connected Apps">
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
                                <div className="text-ava-muted small">Expires {token.expires_at}</div>
                            </div>
                            <button type="button" className="btn btn-outline-primary btn-sm" onClick={() => revoke(token)}>
                                Revoke
                            </button>
                        </li>
                    ))}
                </ul>
            </div>
        </AppLayout>
    )
}
