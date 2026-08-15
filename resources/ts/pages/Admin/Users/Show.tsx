import { router } from '@inertiajs/react'
import AdminLayout from '@/components/layouts/AdminLayout'
import AvatarInitials from '@/components/shared/AvatarInitials'
import RoleChip from '@/components/shared/RoleChip'
import StatusBadge from '@/components/shared/StatusBadge'
import type { SpecificClientAccess, Token, User } from '@/types'

interface ShowProps {
    viewedUser: User
    tokens: Token[]
    specificClients: SpecificClientAccess[]
}

export default function Show({ viewedUser, tokens, specificClients }: ShowProps) {
    const toggleClientAccess = (client: SpecificClientAccess) => {
        if (client.has_access) {
            router.delete(`/admin/users/${viewedUser.id}/client-access/${client.id}`, { preserveScroll: true })
        } else {
            router.post(`/admin/users/${viewedUser.id}/client-access/${client.id}`, {}, { preserveScroll: true })
        }
    }

    const forceLogout = (token: Token) => {
        if (confirm(`Force ${viewedUser.name} to log out of ${token.client.name}?`)) {
            router.delete(`/admin/tokens/${token.id}`, { preserveScroll: true })
        }
    }

    const forceLogoutAll = () => {
        if (tokens.length === 0) {
            return
        }

        if (confirm(`Force ${viewedUser.name} to log out of all ${tokens.length} apps?`)) {
            tokens.forEach((token) => router.delete(`/admin/tokens/${token.id}`, { preserveScroll: true }))
        }
    }

    return (
        <AdminLayout
            title="User Detail"
            action={
                <div className="d-flex gap-2">
                    <button
                        type="button"
                        className="btn btn-primary btn-sm"
                        onClick={() => router.visit(`/admin/users/${viewedUser.id}/edit`)}
                    >
                        <i className="bi bi-pencil me-1" />
                        Edit
                    </button>
                    <button type="button" className="btn btn-outline-primary btn-sm" onClick={() => router.visit('/admin/users')}>
                        <i className="bi bi-arrow-left me-1" />
                        Back to Users
                    </button>
                </div>
            }
        >
            <div className="ava-card mb-4">
                <div className="card-accent card-accent-primary" />
                <div className="p-4 d-flex flex-column flex-sm-row align-items-sm-center gap-3">
                    {viewedUser.avatar ? (
                        <img src={viewedUser.avatar} alt={viewedUser.name} width={56} height={56} className="rounded-circle" />
                    ) : (
                        <AvatarInitials name={viewedUser.name} size={56} />
                    )}
                    <div className="flex-grow-1 min-w-0">
                        <h5 className="mb-1">{viewedUser.name}</h5>
                        <div className="text-ava-muted text-tiny mb-2 d-flex flex-wrap align-items-center gap-3">
                            <span className="d-inline-flex align-items-center gap-1">
                                {viewedUser.email}
                                <a
                                    href={`mailto:${viewedUser.email}`}
                                    className="text-ava-muted"
                                    title={`Email ${viewedUser.name}`}
                                >
                                    <i className="bi bi-envelope" />
                                </a>
                            </span>
                            {viewedUser.phone && (
                                <span className="d-inline-flex align-items-center gap-1">
                                    {viewedUser.phone}
                                    <a
                                        href={`https://wa.me/${viewedUser.phone.replace(/\D/g, '')}`}
                                        target="_blank"
                                        rel="noopener noreferrer"
                                        className="text-success"
                                        title={`WhatsApp ${viewedUser.name}`}
                                    >
                                        <i className="bi bi-whatsapp" />
                                    </a>
                                </span>
                            )}
                        </div>
                        <div className="d-flex flex-wrap gap-2">
                            <span className="code-chip">{viewedUser.membership_code}</span>
                            <span className="role-chip">{viewedUser.stage.name}</span>
                            <RoleChip role={viewedUser.role} />
                            <StatusBadge status={viewedUser.is_active ? 'active' : 'revoked'} />
                            <StatusBadge status={viewedUser.is_admin ? 'enabled' : 'disabled'} />
                        </div>
                    </div>
                </div>
            </div>

            <div className="d-flex justify-content-between align-items-center mb-3">
                <h5 className="mb-0">Logged-In Apps</h5>
                {tokens.length > 0 && (
                    <button type="button" className="btn btn-sm btn-outline-primary" onClick={forceLogoutAll}>
                        <i className="bi bi-box-arrow-right me-1" />
                        Force Logout All
                    </button>
                )}
            </div>

            {tokens.length === 0 ? (
                <div className="ava-card">
                    <div className="text-center py-5">
                        <i className="bi bi-plug text-ava-muted icon-fs-48" />
                        <p className="text-ava-muted mt-3 mb-0">Not logged into any apps right now.</p>
                    </div>
                </div>
            ) : (
                <div className="ava-card">
                    <ul className="list-group list-group-flush">
                        {tokens.map((token) => (
                            <li key={token.id} className="list-group-item d-flex justify-content-between align-items-center p-3 gap-3">
                                <div className="min-w-0">
                                    <div className="fw-semibold">{token.client.name}</div>
                                    <div className="mb-1">
                                        {token.scopes.map((scope) => (
                                            <span key={scope} className="scope-chip me-1">
                                                {scope}
                                            </span>
                                        ))}
                                    </div>
                                    <div className="text-ava-muted text-tiny">
                                        Signed in {token.created_at} &middot; Expires {token.expires_at}
                                    </div>
                                </div>
                                <button
                                    type="button"
                                    className="btn btn-outline-primary btn-sm text-nowrap"
                                    onClick={() => forceLogout(token)}
                                >
                                    <i className="bi bi-box-arrow-right me-1" />
                                    Force Logout
                                </button>
                            </li>
                        ))}
                    </ul>
                </div>
            )}

            <h5 className="mb-3 mt-4">Specific-User App Access</h5>
            {specificClients.length === 0 ? (
                <div className="ava-card">
                    <div className="text-center py-5">
                        <i className="bi bi-shield-lock text-ava-muted icon-fs-48" />
                        <p className="text-ava-muted mt-3 mb-0">
                            No clients are restricted to specific users yet. Set a client's access to "Specific Users" on the
                            Clients page to manage access here.
                        </p>
                    </div>
                </div>
            ) : (
                <div className="ava-card">
                    <ul className="list-group list-group-flush">
                        {specificClients.map((client) => (
                            <li
                                key={client.id}
                                className="list-group-item d-flex justify-content-between align-items-center p-3 gap-3"
                            >
                                <div className="min-w-0">
                                    <div className="fw-semibold">{client.app_name}</div>
                                    <StatusBadge status={client.has_access ? 'active' : 'disabled'} />
                                </div>
                                <button
                                    type="button"
                                    className={`btn btn-sm text-nowrap ${client.has_access ? 'btn-outline-primary' : 'btn-primary'}`}
                                    onClick={() => toggleClientAccess(client)}
                                >
                                    {client.has_access ? 'Revoke Access' : 'Grant Access'}
                                </button>
                            </li>
                        ))}
                    </ul>
                </div>
            )}
        </AdminLayout>
    )
}
