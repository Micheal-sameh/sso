import { router } from '@inertiajs/react'
import { useTranslation } from 'react-i18next'
import AdminLayout from '@/components/layouts/AdminLayout'
import { useConfirm } from '@/components/shared/ConfirmDialog'
import Pagination from '@/components/shared/Pagination'
import StatusBadge from '@/components/shared/StatusBadge'
import UserCell from '@/components/shared/UserCell'
import type { PaginatedData, Token } from '@/types'

interface IndexProps {
    tokens: PaginatedData<Token>
}

export default function Index({ tokens }: IndexProps) {
    const { t } = useTranslation('admin')
    const confirm = useConfirm()

    const revoke = async (token: Token) => {
        if (await confirm(t('tokens.confirmRevoke'), { confirmLabel: t('actions.revoke') })) {
            router.delete(`/admin/tokens/${token.id}`)
        }
    }

    return (
        <AdminLayout title={t('tokens.title')}>
            {/* Table view (md and up) */}
            <div className="ava-card d-none d-md-block">
                <div className="table-responsive">
                    <table className="table mb-0">
                        <thead>
                            <tr>
                                <th>{t('tokens.table.user')}</th>
                                <th>{t('tokens.table.client')}</th>
                                <th>{t('tokens.table.scopes')}</th>
                                <th>{t('tokens.table.created')}</th>
                                <th>{t('tokens.table.expires')}</th>
                                <th>{t('table.status')}</th>
                                <th />
                            </tr>
                        </thead>
                        <tbody>
                            {tokens.data.map((token) => (
                                <tr key={token.id}>
                                    <td>{token.user ? <UserCell user={token.user} /> : '—'}</td>
                                    <td>{token.client.name}</td>
                                    <td>
                                        {token.scopes.map((scope) => (
                                            <span key={scope} className="scope-chip me-1">
                                                {scope}
                                            </span>
                                        ))}
                                    </td>
                                    <td>{token.created_at}</td>
                                    <td>{token.expires_at}</td>
                                    <td>
                                        <StatusBadge status={token.revoked ? 'revoked' : 'active'} />
                                    </td>
                                    <td>
                                        <button type="button" className="btn btn-sm btn-outline-primary" onClick={() => revoke(token)}>
                                            {t('actions.revoke')}
                                        </button>
                                    </td>
                                </tr>
                            ))}
                        </tbody>
                    </table>
                </div>
            </div>

            {/* Card view (below md) */}
            <div className="d-md-none">
                {tokens.data.map((token) => (
                    <div key={token.id} className="record-card">
                        <div className="mb-2">
                            <div className="fw-semibold">{token.user?.name}</div>
                            <div className="text-ava-muted text-tiny">{token.user?.email}</div>
                        </div>

                        <div className="record-card-row">
                            <span className="record-card-label">{t('tokens.table.client')}</span>
                            <span>{token.client.name}</span>
                        </div>
                        <div className="record-card-row">
                            <span className="record-card-label">{t('tokens.table.scopes')}</span>
                            <span>
                                {token.scopes.map((scope) => (
                                    <span key={scope} className="scope-chip ms-1">
                                        {scope}
                                    </span>
                                ))}
                            </span>
                        </div>
                        <div className="record-card-row">
                            <span className="record-card-label">{t('tokens.table.created')}</span>
                            <span className="text-ava-muted text-tiny">{token.created_at}</span>
                        </div>
                        <div className="record-card-row">
                            <span className="record-card-label">{t('tokens.table.expires')}</span>
                            <span className="text-ava-muted text-tiny">{token.expires_at}</span>
                        </div>
                        <div className="record-card-row">
                            <span className="record-card-label">{t('table.status')}</span>
                            <StatusBadge status={token.revoked ? 'revoked' : 'active'} />
                        </div>

                        <div className="record-card-actions">
                            <button type="button" className="btn btn-sm btn-outline-primary" onClick={() => revoke(token)}>
                                {t('actions.revoke')}
                            </button>
                        </div>
                    </div>
                ))}
            </div>

            <div className="mt-3">
                <Pagination pagination={tokens} />
            </div>
        </AdminLayout>
    )
}
