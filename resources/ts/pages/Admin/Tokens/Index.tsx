import { router } from '@inertiajs/react'
import AdminLayout from '@/components/layouts/AdminLayout'
import Pagination from '@/components/shared/Pagination'
import StatusBadge from '@/components/shared/StatusBadge'
import type { PaginatedData, Token } from '@/types'

interface IndexProps {
    tokens: PaginatedData<Token>
}

export default function Index({ tokens }: IndexProps) {
    const revoke = (token: Token) => {
        if (confirm('Revoke this token?')) {
            router.delete(`/admin/tokens/${token.id}`)
        }
    }

    return (
        <AdminLayout title="Tokens">
            <div className="ava-card">
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
                                        <span key={scope} className="badge bg-secondary-subtle text-ava-muted me-1">
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

            <div className="mt-3">
                <Pagination links={tokens.links} />
            </div>
        </AdminLayout>
    )
}
