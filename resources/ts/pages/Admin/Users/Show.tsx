import { useState } from 'react'
import { router, usePage } from '@inertiajs/react'
import AdminLayout from '@/components/layouts/AdminLayout'
import AvatarInitials from '@/components/shared/AvatarInitials'
import { useConfirm } from '@/components/shared/ConfirmDialog'
import RoleChip from '@/components/shared/RoleChip'
import StatusBadge from '@/components/shared/StatusBadge'
import { calculateAge, formatDateDMY } from '@/lib/date'
import type { SharedProps, SpecificClientAccess, Token, User, UserGroupOption } from '@/types'

interface ShowProps {
    viewedUser: User
    tokens: Token[]
    specificClients: SpecificClientAccess[]
    groups: UserGroupOption[]
}

export default function Show({ viewedUser, tokens, specificClients, groups }: ShowProps) {
    const { csrf_token } = usePage<SharedProps>().props
    const confirm = useConfirm()
    const dobAge = calculateAge(viewedUser.date_of_birth)
    const [resetLinkBusy, setResetLinkBusy] = useState(false)
    const [resetLinkCopied, setResetLinkCopied] = useState(false)
    const [avatarLinkBusy, setAvatarLinkBusy] = useState(false)
    const [avatarLinkCopied, setAvatarLinkCopied] = useState(false)

    const fetchLink = async (endpoint: string): Promise<string | null> => {
        const response = await fetch(endpoint, {
            method: 'POST',
            headers: { 'X-CSRF-TOKEN': csrf_token, Accept: 'application/json' },
        })

        if (!response.ok) {
            return null
        }

        const data = (await response.json()) as { link: string }

        return data.link
    }

    const copyResetLink = async () => {
        setResetLinkBusy(true)
        const link = await fetchLink(`/admin/users/${viewedUser.id}/reset-link`)
        setResetLinkBusy(false)

        if (!link) {
            return
        }

        await navigator.clipboard.writeText(link)
        setResetLinkCopied(true)
        setTimeout(() => setResetLinkCopied(false), 2000)
    }

    const sendResetLinkViaWhatsApp = async () => {
        if (!viewedUser.phone) {
            return
        }

        setResetLinkBusy(true)
        const link = await fetchLink(`/admin/users/${viewedUser.id}/reset-link`)
        setResetLinkBusy(false)

        if (!link) {
            return
        }

        const message = `Hi ${viewedUser.name}, use this link to reset your Avarewase password: ${link}`
        const phoneDigits = viewedUser.phone.replace(/\D/g, '')
        window.open(`https://wa.me/${phoneDigits}?text=${encodeURIComponent(message)}`, '_blank', 'noopener,noreferrer')
    }

    const copyAvatarUploadLink = async () => {
        setAvatarLinkBusy(true)
        const link = await fetchLink(`/admin/users/${viewedUser.id}/avatar-upload-link`)
        setAvatarLinkBusy(false)

        if (!link) {
            return
        }

        await navigator.clipboard.writeText(link)
        setAvatarLinkCopied(true)
        setTimeout(() => setAvatarLinkCopied(false), 2000)
    }

    const sendAvatarUploadLinkViaWhatsApp = async () => {
        if (!viewedUser.phone) {
            return
        }

        setAvatarLinkBusy(true)
        const link = await fetchLink(`/admin/users/${viewedUser.id}/avatar-upload-link`)
        setAvatarLinkBusy(false)

        if (!link) {
            return
        }

        const message = `Hi ${viewedUser.name}, use this link to update your Avarewase profile picture: ${link}`
        const phoneDigits = viewedUser.phone.replace(/\D/g, '')
        window.open(`https://wa.me/${phoneDigits}?text=${encodeURIComponent(message)}`, '_blank', 'noopener,noreferrer')
    }

    const toggleClientAccess = (client: SpecificClientAccess) => {
        if (client.has_access) {
            router.delete(`/admin/users/${viewedUser.id}/client-access/${client.id}`, { preserveScroll: true })
        } else {
            router.post(`/admin/users/${viewedUser.id}/client-access/${client.id}`, {}, { preserveScroll: true })
        }
    }

    const toggleGroup = (group: UserGroupOption) => {
        if (group.is_member) {
            router.delete(`/admin/users/${viewedUser.id}/groups/${group.id}`, { preserveScroll: true })
        } else {
            router.post(`/admin/users/${viewedUser.id}/groups/${group.id}`, {}, { preserveScroll: true })
        }
    }

    const forceLogout = async (token: Token) => {
        if (await confirm(`Force ${viewedUser.name} to log out of ${token.client.name}?`, { confirmLabel: 'Log out' })) {
            router.delete(`/admin/tokens/${token.id}`, { preserveScroll: true })
        }
    }

    const forceLogoutAll = async () => {
        if (tokens.length === 0) {
            return
        }

        if (await confirm(`Force ${viewedUser.name} to log out of all ${tokens.length} apps?`, { confirmLabel: 'Log out all' })) {
            tokens.forEach((token) => router.delete(`/admin/tokens/${token.id}`, { preserveScroll: true }))
        }
    }

    const toggleActive = async () => {
        const action = viewedUser.is_active ? 'deactivate' : 'activate'

        if (await confirm(`Are you sure you want to ${action} ${viewedUser.name}?`, { confirmLabel: action === 'deactivate' ? 'Deactivate' : 'Activate' })) {
            router.patch(`/admin/users/${viewedUser.id}/toggle`, {}, { preserveScroll: true })
        }
    }

    const resetMfa = async () => {
        if (
            await confirm(`Reset 2FA for ${viewedUser.name}? They'll be locked out until they set it up again.`, {
                confirmLabel: 'Reset 2FA',
            })
        ) {
            router.post(`/admin/users/${viewedUser.id}/mfa/reset`, {}, { preserveScroll: true })
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
                    <button
                        type="button"
                        className="btn btn-outline-primary btn-sm"
                        onClick={toggleActive}
                    >
                        <i className={`bi ${viewedUser.is_active ? 'bi-slash-circle' : 'bi-check-circle'} me-1`} />
                        {viewedUser.is_active ? 'Deactivate' : 'Activate'}
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
                        <img src={viewedUser.avatar} alt={viewedUser.name} width={80} height={80} className="rounded-circle object-fit-cover" />
                    ) : (
                        <AvatarInitials name={viewedUser.name} size={80} />
                    )}
                    <div className="flex-grow-1 min-w-0">
                        <h3 className="mb-2">{viewedUser.name}</h3>
                        <div className="text-ava-muted fs-6 mb-3 d-flex flex-wrap align-items-center gap-3">
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
                            <span className="d-inline-flex align-items-center gap-1">
                                <i className="bi bi-cake2" />
                                {formatDateDMY(viewedUser.date_of_birth) ?? 'Date of birth not set'}
                                {dobAge !== null && <span>&nbsp;({dobAge} yrs)</span>}
                            </span>
                        </div>
                        <div className="d-flex flex-wrap gap-2 chips-lg">
                            <span className="code-chip text-uppercase">{viewedUser.membership_code}</span>
                            <span className="role-chip">{viewedUser.stage.name}</span>
                            <RoleChip role={viewedUser.role} />
                            <StatusBadge status={viewedUser.is_active ? 'active' : 'revoked'} />
                            <StatusBadge status={viewedUser.is_admin ? 'enabled' : 'disabled'} />
                            {viewedUser.groups.map((group) => (
                                <span key={group.id} className="scope-chip">
                                    {group.name}
                                </span>
                            ))}
                        </div>
                    </div>
                </div>
            </div>

            <h5 className="mb-3">Admin Actions</h5>
            <div className="ava-card mb-4">
                <ul className="list-group list-group-flush">
                    <li className="list-group-item d-flex flex-wrap align-items-center gap-3 p-3">
                        <div className="icon-tile text-ava-primary flex-shrink-0">
                            <i className="bi bi-key" />
                        </div>
                        <div className="min-w-0 flex-grow-1">
                            <div className="fw-semibold">Password Reset</div>
                            <div className="text-ava-muted text-tiny">Link works once and expires in 5 minutes.</div>
                        </div>
                        <div className="d-flex flex-wrap gap-2">
                            <button
                                type="button"
                                className="btn btn-outline-primary btn-sm text-nowrap"
                                disabled={resetLinkBusy}
                                onClick={copyResetLink}
                            >
                                <i className="bi bi-link-45deg me-1" />
                                {resetLinkCopied ? 'Copied!' : 'Copy Link'}
                            </button>
                            {viewedUser.phone && (
                                <button
                                    type="button"
                                    className="btn btn-success btn-sm text-nowrap"
                                    disabled={resetLinkBusy}
                                    onClick={sendResetLinkViaWhatsApp}
                                >
                                    <i className="bi bi-whatsapp me-1" />
                                    WhatsApp
                                </button>
                            )}
                        </div>
                    </li>
                    <li className="list-group-item d-flex flex-wrap align-items-center gap-3 p-3">
                        <div className="icon-tile text-ava-primary flex-shrink-0">
                            <i className="bi bi-shield-lock" />
                        </div>
                        <div className="min-w-0 flex-grow-1">
                            <div className="fw-semibold d-flex align-items-center gap-2">
                                Two-Factor Authentication
                                <StatusBadge status={viewedUser.mfa_enabled ? 'enabled' : 'disabled'} />
                            </div>
                            <div className="text-ava-muted text-tiny">
                                {viewedUser.mfa_enabled
                                    ? 'Clears their 2FA enrollment, e.g. if they lost their device. They must set it up again to sign in.'
                                    : "Not enrolled yet — they'll be prompted to set it up on next sign-in."}
                            </div>
                        </div>
                        <div className="d-flex flex-wrap gap-2">
                            <button
                                type="button"
                                className="btn btn-outline-danger btn-sm text-nowrap"
                                disabled={!viewedUser.mfa_enabled}
                                onClick={resetMfa}
                            >
                                <i className="bi bi-shield-x me-1" />
                                Reset 2FA
                            </button>
                        </div>
                    </li>
                    <li className="list-group-item d-flex flex-wrap align-items-center gap-3 p-3">
                        <div className="icon-tile text-ava-primary flex-shrink-0">
                            <i className="bi bi-image" />
                        </div>
                        <div className="min-w-0 flex-grow-1">
                            <div className="fw-semibold">Profile Picture</div>
                            <div className="text-ava-muted text-tiny">One-time upload link, no login required.</div>
                        </div>
                        <div className="d-flex flex-wrap gap-2">
                            <button
                                type="button"
                                className="btn btn-outline-primary btn-sm text-nowrap"
                                disabled={avatarLinkBusy}
                                onClick={copyAvatarUploadLink}
                            >
                                <i className="bi bi-link-45deg me-1" />
                                {avatarLinkCopied ? 'Copied!' : 'Copy Link'}
                            </button>
                            {viewedUser.phone && (
                                <button
                                    type="button"
                                    className="btn btn-success btn-sm text-nowrap"
                                    disabled={avatarLinkBusy}
                                    onClick={sendAvatarUploadLinkViaWhatsApp}
                                >
                                    <i className="bi bi-whatsapp me-1" />
                                    WhatsApp
                                </button>
                            )}
                        </div>
                    </li>
                </ul>
            </div>

            <div className="row g-4">
                <div className="col-lg-6">
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
                                    <li
                                        key={token.id}
                                        className="list-group-item d-flex justify-content-between align-items-center p-3 gap-3"
                                    >
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
                </div>

                <div className="col-lg-6">
                    <h5 className="mb-3">Specific-User App Access</h5>

                    {specificClients.length === 0 ? (
                        <div className="ava-card">
                            <div className="text-center py-5">
                                <i className="bi bi-shield-lock text-ava-muted icon-fs-48" />
                                <p className="text-ava-muted mt-3 mb-0 px-3">
                                    No clients are restricted to specific users yet. Set a client's access to "Specific Users"
                                    on the Clients page to manage access here.
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
                </div>

                <div className="col-lg-6">
                    <h5 className="mb-3">Groups</h5>

                    {groups.length === 0 ? (
                        <div className="ava-card">
                            <div className="text-center py-5">
                                <i className="bi bi-collection text-ava-muted icon-fs-48" />
                                <p className="text-ava-muted mt-3 mb-0 px-3">
                                    No groups exist yet. Create one on the Groups page first.
                                </p>
                            </div>
                        </div>
                    ) : (
                        <div className="ava-card">
                            <ul className="list-group list-group-flush">
                                {groups.map((group) => (
                                    <li
                                        key={group.id}
                                        className="list-group-item d-flex justify-content-between align-items-center p-3 gap-3"
                                    >
                                        <div className="min-w-0">
                                            <div className="fw-semibold">{group.name}</div>
                                            <StatusBadge status={group.is_member ? 'active' : 'disabled'} />
                                        </div>
                                        <button
                                            type="button"
                                            className={`btn btn-sm text-nowrap ${group.is_member ? 'btn-outline-primary' : 'btn-primary'}`}
                                            onClick={() => toggleGroup(group)}
                                        >
                                            {group.is_member ? 'Remove' : 'Add'}
                                        </button>
                                    </li>
                                ))}
                            </ul>
                        </div>
                    )}
                </div>
            </div>
        </AdminLayout>
    )
}
