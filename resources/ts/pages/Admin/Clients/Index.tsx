import { useState } from 'react'
import type { ChangeEvent, FormEvent } from 'react'
import { router, useForm } from '@inertiajs/react'
import AdminLayout from '@/components/layouts/AdminLayout'
import { useConfirm } from '@/components/shared/ConfirmDialog'
import Pagination from '@/components/shared/Pagination'
import StatusBadge from '@/components/shared/StatusBadge'
import type { ClientMeta, Group, LiteUser, PaginatedData, Stage } from '@/types'

interface IndexProps {
    clients: PaginatedData<ClientMeta>
    stages: Stage[]
    users: LiteUser[]
    groups: Group[]
    newClientSecret: string | null
}

interface ClientForm {
    name: string
    redirect: string
    homepage_url: string
    app_logo: File | null
    is_internal: boolean
    is_public_client: boolean
    grant_type: 'authorization_code' | 'implicit'
    access_mode: 'all' | 'stage' | 'specific' | 'group'
    stage_ids: number[]
    user_ids: number[]
    group_ids: number[]
}

const ACCESS_MODE_LABELS: Record<ClientMeta['access_mode'], string> = {
    all: 'All Users',
    stage: 'Specific Stage(s)',
    specific: 'Specific Users',
    group: 'Specific Group(s)',
}

const emptyForm: ClientForm = {
    name: '',
    redirect: '',
    homepage_url: '',
    app_logo: null,
    is_internal: false,
    is_public_client: false,
    grant_type: 'authorization_code',
    access_mode: 'all',
    stage_ids: [],
    user_ids: [],
    group_ids: [],
}

export default function Index({ clients, stages, users, groups, newClientSecret }: IndexProps) {
    const [showModal, setShowModal] = useState(false)
    const [editingClient, setEditingClient] = useState<ClientMeta | null>(null)
    const [userSearch, setUserSearch] = useState('')
    const [logoPreview, setLogoPreview] = useState<string | null>(null)
    const [revealedSecret, setRevealedSecret] = useState(newClientSecret)
    const [secretCopied, setSecretCopied] = useState(false)
    const confirm = useConfirm()
    const form = useForm<ClientForm>(emptyForm)

    const copySecret = async () => {
        if (!revealedSecret) {
            return
        }

        await navigator.clipboard.writeText(revealedSecret)
        setSecretCopied(true)
        setTimeout(() => setSecretCopied(false), 2000)
    }

    const openCreateModal = () => {
        setEditingClient(null)
        form.reset()
        form.clearErrors()
        setUserSearch('')
        setLogoPreview(null)
        setShowModal(true)
    }

    const openEditModal = (client: ClientMeta) => {
        setEditingClient(client)
        form.setData({
            name: client.app_name,
            redirect: client.oauth_client.redirect,
            homepage_url: client.homepage_url ?? '',
            app_logo: null,
            is_internal: client.is_internal,
            is_public_client: false,
            grant_type: client.oauth_client.grant_types?.includes('implicit') ? 'implicit' : 'authorization_code',
            access_mode: client.access_mode,
            stage_ids: (client.stages ?? []).map((s) => s.id),
            user_ids: (client.allowed_users ?? []).map((u) => u.id),
            group_ids: (client.groups ?? []).map((g) => g.id),
        })
        form.clearErrors()
        setUserSearch('')
        setLogoPreview(client.app_logo_url)
        setShowModal(true)
    }

    const closeModal = () => {
        setShowModal(false)
        setEditingClient(null)
    }

    const onLogoChange = (e: ChangeEvent<HTMLInputElement>) => {
        const file = e.target.files?.[0] ?? null
        form.setData('app_logo', file)

        if (file) {
            const reader = new FileReader()
            reader.onload = () => setLogoPreview(reader.result as string)
            reader.readAsDataURL(file)
        }
    }

    const submit = (e: FormEvent) => {
        e.preventDefault()
        if (editingClient) {
            // PHP never parses multipart bodies on a genuine PUT request (only POST), and
            // Inertia's usual auto-fix for this only triggers when a File is actually present
            // in the payload. Since the logo is optional here, force POST + _method spoofing
            // ourselves so the request parses correctly whether or not a new logo was chosen.
            form.transform((data) => ({ ...data, _method: 'put' }))
            form.post(`/admin/clients/${editingClient.oauth_client_id}`, { forceFormData: true, onSuccess: closeModal })
        } else {
            form.transform((data) => data)
            form.post('/admin/clients', { forceFormData: true, onSuccess: closeModal })
        }
    }

    const copyId = (id: string) => {
        navigator.clipboard.writeText(id)
    }

    const revoke = async (client: ClientMeta) => {
        if (await confirm(`Revoke client ${client.app_name}?`, { confirmLabel: 'Revoke' })) {
            router.delete(`/admin/clients/${client.oauth_client_id}`)
        }
    }

    const toggleStage = (stageId: number) => {
        form.setData(
            'stage_ids',
            form.data.stage_ids.includes(stageId)
                ? form.data.stage_ids.filter((id) => id !== stageId)
                : [...form.data.stage_ids, stageId],
        )
    }

    const toggleUser = (userId: number) => {
        form.setData(
            'user_ids',
            form.data.user_ids.includes(userId)
                ? form.data.user_ids.filter((id) => id !== userId)
                : [...form.data.user_ids, userId],
        )
    }

    const toggleGroup = (groupId: number) => {
        form.setData(
            'group_ids',
            form.data.group_ids.includes(groupId)
                ? form.data.group_ids.filter((id) => id !== groupId)
                : [...form.data.group_ids, groupId],
        )
    }

    const filteredUsers = users.filter(
        (u) =>
            u.name.toLowerCase().includes(userSearch.toLowerCase()) ||
            u.email.toLowerCase().includes(userSearch.toLowerCase()),
    )

    const internalCount = clients.data.filter((c) => c.is_internal).length
    const externalCount = clients.data.length - internalCount

    return (
        <AdminLayout
            title="OAuth Clients"
            action={
                <button type="button" className="btn btn-primary btn-sm" onClick={openCreateModal}>
                    <i className="bi bi-plus-lg" />
                    <span className="d-none d-sm-inline ms-1">Register New Client</span>
                </button>
            }
        >
            <div className="row g-3 mb-4">
                <div className="col-md-4">
                    <div className="stat-tile">
                        <p className="stat-tile-label mb-2">Total Clients</p>
                        <p className="stat-tile-value mb-0">{clients.total}</p>
                    </div>
                </div>
                <div className="col-md-4">
                    <div className="stat-tile">
                        <p className="stat-tile-label mb-2">Internal (this page)</p>
                        <p className="stat-tile-value mb-0">{internalCount}</p>
                    </div>
                </div>
                <div className="col-md-4">
                    <div className="stat-tile">
                        <p className="stat-tile-label mb-2">External (this page)</p>
                        <p className="stat-tile-value mb-0">{externalCount}</p>
                    </div>
                </div>
            </div>

            {/* Table view (md and up) */}
            <div className="ava-card d-none d-md-block">
                <div className="table-responsive">
                    <table className="table mb-0">
                        <thead>
                            <tr>
                                <th>App Name</th>
                                <th>Client ID</th>
                                <th>Type</th>
                                <th>Access</th>
                                <th>Status</th>
                                <th>Redirect URI</th>
                                <th />
                            </tr>
                        </thead>
                        <tbody>
                            {clients.data.map((client) => (
                                <tr key={client.id}>
                                    <td>
                                        <div className="d-flex align-items-center gap-2">
                                            {client.app_logo_url ? (
                                                <img
                                                    src={client.app_logo_url}
                                                    alt={client.app_name}
                                                    width={32}
                                                    height={32}
                                                    className="rounded"
                                                />
                                            ) : (
                                                <div className={`icon-tile ${client.is_internal ? 'text-ava-secondary' : 'text-ava-muted'}`}>
                                                    <i className={`bi ${client.is_internal ? 'bi-building' : 'bi-cloud'}`} />
                                                </div>
                                            )}
                                            <div>
                                                <div>{client.app_name}</div>
                                                {client.homepage_url && (
                                                    <div className="text-ava-muted text-tiny">{client.homepage_url}</div>
                                                )}
                                            </div>
                                        </div>
                                    </td>
                                    <td>
                                        <code>{String(client.oauth_client.id).slice(0, 8)}...</code>
                                        <button
                                            type="button"
                                            className="btn btn-sm btn-link"
                                            onClick={() => copyId(client.oauth_client.id)}
                                        >
                                            <i className="bi bi-clipboard" />
                                        </button>
                                    </td>
                                    <td>
                                        <StatusBadge status={client.is_internal ? 'internal' : 'external'} />
                                    </td>
                                    <td>
                                        <span className="role-chip">{ACCESS_MODE_LABELS[client.access_mode]}</span>
                                    </td>
                                    <td>
                                        <StatusBadge status={client.oauth_client.revoked ? 'revoked' : 'active'} />
                                    </td>
                                    <td className="text-truncate cell-truncate-220">{client.oauth_client.redirect}</td>
                                    <td>
                                        <div className="d-flex gap-2">
                                            <button
                                                type="button"
                                                className="btn btn-sm btn-outline-primary"
                                                onClick={() => openEditModal(client)}
                                            >
                                                Edit
                                            </button>
                                            <button type="button" className="btn btn-sm btn-outline-primary" onClick={() => revoke(client)}>
                                                Revoke
                                            </button>
                                        </div>
                                    </td>
                                </tr>
                            ))}
                        </tbody>
                    </table>
                </div>
            </div>

            {/* Card view (below md) */}
            <div className="d-md-none">
                {clients.data.map((client) => (
                    <div key={client.id} className="record-card">
                        <div className="d-flex align-items-center gap-3 mb-2">
                            {client.app_logo_url ? (
                                <img src={client.app_logo_url} alt={client.app_name} width={40} height={40} className="rounded" />
                            ) : (
                                <div className={`icon-tile ${client.is_internal ? 'text-ava-secondary' : 'text-ava-muted'}`}>
                                    <i className={`bi ${client.is_internal ? 'bi-building' : 'bi-cloud'}`} />
                                </div>
                            )}
                            <div className="min-w-0">
                                <div className="fw-semibold text-truncate">{client.app_name}</div>
                                {client.homepage_url && (
                                    <div className="text-ava-muted text-tiny text-truncate">{client.homepage_url}</div>
                                )}
                            </div>
                        </div>

                        <div className="record-card-row">
                            <span className="record-card-label">Client ID</span>
                            <span className="d-flex align-items-center gap-1">
                                <code>{String(client.oauth_client.id).slice(0, 8)}...</code>
                                <button
                                    type="button"
                                    className="btn btn-sm btn-link p-0"
                                    onClick={() => copyId(client.oauth_client.id)}
                                >
                                    <i className="bi bi-clipboard" />
                                </button>
                            </span>
                        </div>
                        <div className="record-card-row">
                            <span className="record-card-label">Type</span>
                            <StatusBadge status={client.is_internal ? 'internal' : 'external'} />
                        </div>
                        <div className="record-card-row">
                            <span className="record-card-label">Access</span>
                            <span className="role-chip">{ACCESS_MODE_LABELS[client.access_mode]}</span>
                        </div>
                        <div className="record-card-row">
                            <span className="record-card-label">Status</span>
                            <StatusBadge status={client.oauth_client.revoked ? 'revoked' : 'active'} />
                        </div>
                        <div className="record-card-row">
                            <span className="record-card-label">Redirect</span>
                            <span className="text-ava-muted text-tiny text-truncate cell-truncate-220">
                                {client.oauth_client.redirect}
                            </span>
                        </div>

                        <div className="record-card-actions">
                            <button type="button" className="btn btn-sm btn-outline-primary" onClick={() => openEditModal(client)}>
                                Edit
                            </button>
                            <button type="button" className="btn btn-sm btn-outline-primary" onClick={() => revoke(client)}>
                                Revoke
                            </button>
                        </div>
                    </div>
                ))}
            </div>

            <div className="mt-3">
                <Pagination links={clients.links} />
            </div>

            {revealedSecret && (
                <div className="modal d-block modal-backdrop-custom" tabIndex={-1}>
                    <div className="modal-dialog">
                        <div className="modal-content">
                            <div className="modal-header">
                                <h5 className="modal-title">
                                    <i className="bi bi-key-fill text-ava-secondary me-2" />
                                    Client Secret
                                </h5>
                            </div>
                            <div className="modal-body">
                                <p className="text-ava-muted small">
                                    Copy this secret now and store it securely &mdash; it will not be shown again. If
                                    you lose it, you&apos;ll need to revoke this client and register a new one.
                                </p>
                                <div className="input-group">
                                    <input type="text" readOnly className="form-control font-monospace small" value={revealedSecret} />
                                    <button type="button" className="btn btn-outline-primary" onClick={copySecret}>
                                        <i className={`bi ${secretCopied ? 'bi-check-lg' : 'bi-clipboard'}`} />
                                    </button>
                                </div>
                            </div>
                            <div className="modal-footer">
                                <button type="button" className="btn btn-primary" onClick={() => setRevealedSecret(null)}>
                                    I&apos;ve saved it
                                </button>
                            </div>
                        </div>
                    </div>
                </div>
            )}

            {showModal && (
                <div className="modal d-block modal-backdrop-custom" tabIndex={-1}>
                    <div className="modal-dialog">
                        <div className="modal-content">
                            <div className="modal-header">
                                <h5 className="modal-title">{editingClient ? `Edit ${editingClient.app_name}` : 'Register New Client'}</h5>
                                <button type="button" className="btn-close" onClick={closeModal} />
                            </div>
                            <form onSubmit={submit}>
                                <div className="modal-body">
                                    <div className="mb-3">
                                        <label className="form-label" htmlFor="name">App Name</label>
                                        <input
                                            id="name"
                                            className="form-control"
                                            value={form.data.name}
                                            onChange={(e) => form.setData('name', e.target.value)}
                                        />
                                        {form.errors.name && <div className="text-danger small mt-1">{form.errors.name}</div>}
                                    </div>
                                    <div className="mb-3">
                                        <label className="form-label" htmlFor="redirect">Redirect URI</label>
                                        <input
                                            id="redirect"
                                            className="form-control"
                                            value={form.data.redirect}
                                            onChange={(e) => form.setData('redirect', e.target.value)}
                                        />
                                        {form.errors.redirect && <div className="text-danger small mt-1">{form.errors.redirect}</div>}
                                    </div>
                                    <div className="mb-3">
                                        <label className="form-label" htmlFor="homepage_url">Homepage URL</label>
                                        <input
                                            id="homepage_url"
                                            className="form-control"
                                            value={form.data.homepage_url}
                                            onChange={(e) => form.setData('homepage_url', e.target.value)}
                                        />
                                        {form.errors.homepage_url && (
                                            <div className="text-danger small mt-1">{form.errors.homepage_url}</div>
                                        )}
                                    </div>
                                    <div className="mb-3">
                                        <label className="form-label" htmlFor="app_logo">Logo</label>
                                        <div className="d-flex align-items-center gap-3">
                                            {logoPreview ? (
                                                <img
                                                    src={logoPreview}
                                                    alt="Logo preview"
                                                    width={48}
                                                    height={48}
                                                    className="rounded object-fit-cover"
                                                />
                                            ) : (
                                                <div className="icon-tile text-ava-muted">
                                                    <i className="bi bi-image" />
                                                </div>
                                            )}
                                            <input
                                                id="app_logo"
                                                type="file"
                                                accept="image/*"
                                                className="form-control"
                                                onChange={onLogoChange}
                                            />
                                        </div>
                                        {form.errors.app_logo && (
                                            <div className="text-danger small mt-1">{form.errors.app_logo}</div>
                                        )}
                                    </div>
                                    <div className="form-check form-switch mb-3">
                                        <input
                                            id="is_internal"
                                            type="checkbox"
                                            className="form-check-input"
                                            checked={form.data.is_internal}
                                            onChange={(e) => form.setData('is_internal', e.target.checked)}
                                        />
                                        <label className="form-check-label" htmlFor="is_internal">Internal application</label>
                                    </div>

                                    {!editingClient && (
                                        <div className="mb-3">
                                            <label className="form-label" htmlFor="grant_type">Grant Type</label>
                                            <select
                                                id="grant_type"
                                                className="form-select"
                                                value={form.data.grant_type}
                                                onChange={(e) => form.setData('grant_type', e.target.value as ClientForm['grant_type'])}
                                            >
                                                <option value="authorization_code">Authorization Code (PKCE)</option>
                                                <option value="implicit">Implicit (legacy, e.g. Odoo)</option>
                                            </select>
                                            <p className="text-ava-muted text-tiny mt-1 mb-0">
                                                Use Implicit only for legacy consumers that require it (e.g. Odoo&apos;s
                                                built-in OAuth login). It always issues tokens without a client secret.
                                                This cannot be changed after the client is created.
                                            </p>
                                        </div>
                                    )}

                                    {!editingClient && form.data.grant_type === 'authorization_code' && (
                                        <div className="form-check form-switch mb-3">
                                            <input
                                                id="is_public_client"
                                                type="checkbox"
                                                className="form-check-input"
                                                checked={form.data.is_public_client}
                                                onChange={(e) => form.setData('is_public_client', e.target.checked)}
                                            />
                                            <label className="form-check-label" htmlFor="is_public_client">
                                                Public client (mobile / native app)
                                            </label>
                                            <p className="text-ava-muted text-tiny mt-1 mb-0">
                                                No client secret is issued — the app must use PKCE. Leave unchecked for
                                                server-side apps (web dashboards, backends), which get a secret to keep
                                                confidential. This cannot be changed after the client is created.
                                            </p>
                                        </div>
                                    )}

                                    <div className="mb-3">
                                        <label className="form-label" htmlFor="access_mode">Who can authenticate</label>
                                        <select
                                            id="access_mode"
                                            className="form-select"
                                            value={form.data.access_mode}
                                            onChange={(e) => form.setData('access_mode', e.target.value as ClientForm['access_mode'])}
                                        >
                                            <option value="all">All Users</option>
                                            <option value="stage">Specific Stage(s)</option>
                                            <option value="specific">Specific Users</option>
                                            <option value="group">Specific Group(s)</option>
                                        </select>
                                        {form.errors.access_mode && (
                                            <div className="text-danger small mt-1">{form.errors.access_mode}</div>
                                        )}
                                    </div>

                                    {form.data.access_mode === 'stage' && (
                                        <div className="mb-3">
                                            <label className="form-label">Allowed Stages</label>
                                            <div className="permission-list">
                                                {stages.map((stage) => (
                                                    <div key={stage.id} className="form-check">
                                                        <input
                                                            id={`stage-${stage.id}`}
                                                            type="checkbox"
                                                            className="form-check-input"
                                                            checked={form.data.stage_ids.includes(stage.id)}
                                                            onChange={() => toggleStage(stage.id)}
                                                        />
                                                        <label className="form-check-label" htmlFor={`stage-${stage.id}`}>
                                                            {stage.name}
                                                        </label>
                                                    </div>
                                                ))}
                                            </div>
                                            {form.errors.stage_ids && (
                                                <div className="text-danger small mt-1">{form.errors.stage_ids}</div>
                                            )}
                                        </div>
                                    )}

                                    {form.data.access_mode === 'specific' && (
                                        <div className="mb-3">
                                            <label className="form-label">Allowed Users</label>
                                            <input
                                                type="text"
                                                className="form-control mb-2"
                                                placeholder="Search users..."
                                                value={userSearch}
                                                onChange={(e) => setUserSearch(e.target.value)}
                                            />
                                            <div className="permission-list scroll-picker">
                                                {filteredUsers.map((u) => (
                                                    <div key={u.id} className="form-check">
                                                        <input
                                                            id={`user-${u.id}`}
                                                            type="checkbox"
                                                            className="form-check-input"
                                                            checked={form.data.user_ids.includes(u.id)}
                                                            onChange={() => toggleUser(u.id)}
                                                        />
                                                        <label className="form-check-label" htmlFor={`user-${u.id}`}>
                                                            {u.name}
                                                            <span className="text-ava-muted text-tiny ms-1">{u.email}</span>
                                                        </label>
                                                    </div>
                                                ))}
                                                {filteredUsers.length === 0 && (
                                                    <p className="text-ava-muted text-tiny mb-0">No users match your search.</p>
                                                )}
                                            </div>
                                            {form.errors.user_ids && (
                                                <div className="text-danger small mt-1">{form.errors.user_ids}</div>
                                            )}
                                        </div>
                                    )}

                                    {form.data.access_mode === 'group' && (
                                        <div className="mb-3">
                                            <label className="form-label">Allowed Groups</label>
                                            <div className="permission-list">
                                                {groups.map((group) => (
                                                    <div key={group.id} className="form-check">
                                                        <input
                                                            id={`group-${group.id}`}
                                                            type="checkbox"
                                                            className="form-check-input"
                                                            checked={form.data.group_ids.includes(group.id)}
                                                            onChange={() => toggleGroup(group.id)}
                                                        />
                                                        <label className="form-check-label" htmlFor={`group-${group.id}`}>
                                                            {group.name}
                                                        </label>
                                                    </div>
                                                ))}
                                                {groups.length === 0 && (
                                                    <p className="text-ava-muted text-tiny mb-0">
                                                        No groups exist yet. Create one on the Groups page first.
                                                    </p>
                                                )}
                                            </div>
                                            {form.errors.group_ids && (
                                                <div className="text-danger small mt-1">{form.errors.group_ids}</div>
                                            )}
                                        </div>
                                    )}
                                </div>
                                <div className="modal-footer">
                                    <button type="button" className="btn btn-outline-primary" onClick={closeModal}>
                                        Cancel
                                    </button>
                                    <button type="submit" className="btn btn-primary" disabled={form.processing}>
                                        {editingClient ? 'Save Changes' : 'Register'}
                                    </button>
                                </div>
                            </form>
                        </div>
                    </div>
                </div>
            )}
        </AdminLayout>
    )
}
