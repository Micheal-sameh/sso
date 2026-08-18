import { router } from '@inertiajs/react'
import AdminLayout from '@/components/layouts/AdminLayout'
import { useConfirm } from '@/components/shared/ConfirmDialog'
import Pagination from '@/components/shared/Pagination'
import StatusBadge from '@/components/shared/StatusBadge'
import type { PaginatedData, Token } from '@/types'

interface IndexProps {
    tokens: PaginatedData<Token>
}

export default function Index({ tokens }: IndexProps) {
    const confirm = useConfirm()

    const revoke = async (token: Token) => {
        if (await confirm('Revoke this token?', { confirmLabel: 'Revoke' })) {
            router.delete(`/admin/tokens/${token.id}`)
        }
    }

    return (
        <AdminLayout title="Tokens">
            {/* Table view (md and up) */}
            <div className="ava-card d-none d-md-block">
                <div className="table-responsive">
                    <table className="table mb-0">
                        <thead>
                            <tr>
                                <th>User</th>
                                <th>Client</th>
                                <th>Scopes</th>
                                <th>Created</th>
                                <th>Expires</th>
                                <th>Status</th>
                                <th />
                            </tr>
                        </thead>
                        <tbody>
                            {tokens.data.map((token) => (
                                <tr key={token.id}>
                                    <td>
                                        <div>{token.user?.name}</div>
                                        <div className="text-ava-muted small">{token.user?.email}</div>
                                    </td>
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
                                            Revoke
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
                            <span className="record-card-label">Client</span>
                            <span>{token.client.name}</span>
                        </div>
                        <div className="record-card-row">
                            <span className="record-card-label">Scopes</span>
                            <span>
                                {token.scopes.map((scope) => (
                                    <span key={scope} className="scope-chip ms-1">
                                        {scope}
                                    </span>
                                ))}
                            </span>
                        </div>
                        <div className="record-card-row">
                            <span className="record-card-label">Created</span>
                            <span className="text-ava-muted text-tiny">{token.created_at}</span>
                        </div>
                        <div className="record-card-row">
                            <span className="record-card-label">Expires</span>
                            <span className="text-ava-muted text-tiny">{token.expires_at}</span>
                        </div>
                        <div className="record-card-row">
                            <span className="record-card-label">Status</span>
                            <StatusBadge status={token.revoked ? 'revoked' : 'active'} />
                        </div>

                        <div className="record-card-actions">
                            <button type="button" className="btn btn-sm btn-outline-primary" onClick={() => revoke(token)}>
                                Revoke
                            </button>
                        </div>
                    </div>
                ))}
            </div>

            <div className="mt-3">
                <Pagination links={tokens.links} />
            </div>
        </AdminLayout>
    )
}
