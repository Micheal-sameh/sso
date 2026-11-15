import { useState } from 'react'
import type { ChangeEvent, FormEvent } from 'react'
import { router, useForm } from '@inertiajs/react'
import { useTranslation } from 'react-i18next'
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
    syncClientIds: string[]
    canManageUserSync: boolean
    newClientSecret: string | null
    newLogoutSecret: string | null
}

interface ClientForm {
    name: string
    redirect: string
    homepage_url: string
    logout_uri: string
    app_logo: File | null
    is_internal: boolean
    allow_user_sync: boolean
    is_public_client: boolean
    grant_type: 'authorization_code' | 'implicit'
    access_mode: 'all' | 'stage' | 'specific' | 'group' | 'all_except_users'
    stage_ids: number[]
    user_ids: number[]
    group_ids: number[]
    excluded_user_ids: number[]
}

const emptyForm: ClientForm = {
    name: '',
    redirect: '',
    homepage_url: '',
    logout_uri: '',
    app_logo: null,
    is_internal: false,
    allow_user_sync: false,
    is_public_client: false,
    grant_type: 'authorization_code',
    access_mode: 'all',
    stage_ids: [],
    user_ids: [],
    group_ids: [],
    excluded_user_ids: [],
}

export default function Index({ clients, stages, users, groups, syncClientIds, canManageUserSync, newClientSecret, newLogoutSecret }: IndexProps) {
    const { t } = useTranslation(['admin', 'common'])
    const ACCESS_MODE_LABELS: Record<ClientMeta['access_mode'], string> = {
        all: t('targeting.all'),
        stage: t('targeting.stage'),
        specific: t('targeting.specific'),
        group: t('targeting.group'),
        all_except_users: t('targeting.allExceptUsers'),
    }
    const [showModal, setShowModal] = useState(false)
    const [editingClient, setEditingClient] = useState<ClientMeta | null>(null)
    const [userSearch, setUserSearch] = useState('')
    const [logoPreview, setLogoPreview] = useState<string | null>(null)
    const [revealedSecret, setRevealedSecret] = useState(newClientSecret)
    const [revealedLogoutSecret, setRevealedLogoutSecret] = useState(newLogoutSecret)
    const [secretCopied, setSecretCopied] = useState(false)
    const [logoutSecretCopied, setLogoutSecretCopied] = useState(false)
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

    const copyLogoutSecret = async () => {
        if (!revealedLogoutSecret) {
            return
        }

        await navigator.clipboard.writeText(revealedLogoutSecret)
        setLogoutSecretCopied(true)
        setTimeout(() => setLogoutSecretCopied(false), 2000)
    }

    const openCreateModal = () => {
        setEditingClient(null)
        form.reset()
        form.clearErrors()
        setUserSearch('')
        setLogoPreview(null)
        setRevealedLogoutSecret(null)
        setShowModal(true)
    }

    const openEditModal = (client: ClientMeta) => {
        setEditingClient(client)
        form.setData({
            name: client.app_name,
            redirect: client.oauth_client.redirect_uris[0] ?? '',
            homepage_url: client.homepage_url ?? '',
            logout_uri: client.logout_uri ?? '',
            app_logo: null,
            is_internal: client.is_internal,
            allow_user_sync: syncClientIds.includes(client.oauth_client_id),
            is_public_client: false,
            grant_type: client.oauth_client.grant_types?.includes('implicit') ? 'implicit' : 'authorization_code',
            access_mode: client.access_mode,
            stage_ids: (client.stages ?? []).map((s) => s.id),
            user_ids: (client.allowed_users ?? []).map((u) => u.id),
            group_ids: (client.groups ?? []).map((g) => g.id),
            excluded_user_ids: (client.excluded_users ?? []).map((u) => u.id),
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
        if (await confirm(t('clients.confirmRevoke', { name: client.app_name }), { confirmLabel: t('actions.revoke') })) {
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

    const toggleExcludedUser = (userId: number) => {
        form.setData(
            'excluded_user_ids',
            form.data.excluded_user_ids.includes(userId)
                ? form.data.excluded_user_ids.filter((id) => id !== userId)
                : [...form.data.excluded_user_ids, userId],
        )
    }

    const filteredUsers = users.filter(
        (u) =>
            u.name.toLowerCase().includes(userSearch.toLowerCase()) ||
            (u.email ?? '').toLowerCase().includes(userSearch.toLowerCase()),
    )

    const internalCount = clients.data.filter((c) => c.is_internal).length
    const externalCount = clients.data.length - internalCount

    return (
        <AdminLayout
            title={t('clients.title')}
            action={
                <button type="button" className="btn btn-primary btn-sm" onClick={openCreateModal}>
                    <i className="bi bi-plus-lg" />
                    <span className="d-none d-sm-inline ms-1">{t('clients.registerNew')}</span>
                </button>
            }
        >
            <div className="row g-3 mb-4">
                <div className="col-md-4">
                    <div className="stat-tile">
                        <p className="stat-tile-label mb-2">{t('clients.stats.total')}</p>
                        <p className="stat-tile-value mb-0">{clients.total}</p>
                    </div>
                </div>
                <div className="col-md-4">
                    <div className="stat-tile">
                        <p className="stat-tile-label mb-2">{t('clients.stats.internalPage')}</p>
                        <p className="stat-tile-value mb-0">{internalCount}</p>
                    </div>
                </div>
                <div className="col-md-4">
                    <div className="stat-tile">
                        <p className="stat-tile-label mb-2">{t('clients.stats.externalPage')}</p>
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
                                <th>{t('clients.table.appName')}</th>
                                <th>{t('clients.table.clientId')}</th>
                                <th>{t('clients.table.type')}</th>
                                <th>{t('clients.table.access')}</th>
                                <th>{t('table.status')}</th>
                                <th>{t('clients.table.redirectUri')}</th>
                                <th />
                            </tr>
                        </thead>
                        <tbody>
                            {clients.data.map((client) => (
                                <tr key={client.id}>
                                    <td>
                                        <div className="d-flex align-items-center gap-2">
                                            {client.app_logo_url && (
                                                <img
                                                    src={client.app_logo_url}
                                                    alt={client.app_name}
                                                    width={32}
                                                    height={32}
                                                    className="rounded"
                                                    onError={(e) => {
                                                        e.currentTarget.style.display = 'none';
                                                        e.currentTarget.nextElementSibling?.classList.remove('d-none');
                                                    }}
                                                />
                                            )}
                                            <div
                                                className={`icon-tile ${client.is_internal ? 'text-ava-secondary' : 'text-ava-muted'} ${client.app_logo_url ? 'd-none' : ''}`}
                                            >
                                                <i className={`bi ${client.is_internal ? 'bi-building' : 'bi-cloud'}`} />
                                            </div>
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
                                    <td className="text-truncate cell-truncate-220">{client.oauth_client.redirect_uris[0]}</td>
                                    <td>
                                        <div className="d-flex gap-2">
                                            <button
                                                type="button"
                                                className="btn btn-sm btn-outline-primary"
                                                onClick={() => openEditModal(client)}
                                            >
                                                {t('common:actions.edit')}
                                            </button>
                                            <button type="button" className="btn btn-sm btn-outline-primary" onClick={() => revoke(client)}>
                                                {t('actions.revoke')}
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
                            {client.app_logo_url && (
                                <img
                                    src={client.app_logo_url}
                                    alt={client.app_name}
                                    width={40}
                                    height={40}
                                    className="rounded"
                                    onError={(e) => {
                                        e.currentTarget.style.display = 'none';
                                        e.currentTarget.nextElementSibling?.classList.remove('d-none');
                                    }}
                                />
                            )}
                            <div
                                className={`icon-tile ${client.is_internal ? 'text-ava-secondary' : 'text-ava-muted'} ${client.app_logo_url ? 'd-none' : ''}`}
                            >
                                <i className={`bi ${client.is_internal ? 'bi-building' : 'bi-cloud'}`} />
                            </div>
                            <div className="min-w-0">
                                <div className="fw-semibold text-truncate">{client.app_name}</div>
                                {client.homepage_url && (
                                    <div className="text-ava-muted text-tiny text-truncate">{client.homepage_url}</div>
                                )}
                            </div>
                        </div>

                        <div className="record-card-row">
                            <span className="record-card-label">{t('clients.table.clientId')}</span>
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
                            <span className="record-card-label">{t('clients.table.type')}</span>
                            <StatusBadge status={client.is_internal ? 'internal' : 'external'} />
                        </div>
                        <div className="record-card-row">
                            <span className="record-card-label">{t('clients.table.access')}</span>
                            <span className="role-chip">{ACCESS_MODE_LABELS[client.access_mode]}</span>
                        </div>
                        <div className="record-card-row">
                            <span className="record-card-label">{t('table.status')}</span>
                            <StatusBadge status={client.oauth_client.revoked ? 'revoked' : 'active'} />
                        </div>
                        <div className="record-card-row">
                            <span className="record-card-label">{t('clients.table.redirectUri')}</span>
                            <span className="text-ava-muted text-tiny text-truncate cell-truncate-220">
                                {client.oauth_client.redirect_uris[0]}
                            </span>
                        </div>

                        <div className="record-card-actions">
                            <button type="button" className="btn btn-sm btn-outline-primary" onClick={() => openEditModal(client)}>
                                {t('common:actions.edit')}
                            </button>
                            <button type="button" className="btn btn-sm btn-outline-primary" onClick={() => revoke(client)}>
                                {t('actions.revoke')}
                            </button>
                        </div>
                    </div>
                ))}
            </div>

            <div className="mt-3">
                <Pagination pagination={clients} />
            </div>

            {(revealedSecret || revealedLogoutSecret) && (
                <div className="modal d-block modal-backdrop-custom" tabIndex={-1}>
                    <div className="modal-dialog">
                        <div className="modal-content">
                            <div className="modal-header">
                                <h5 className="modal-title">
                                    <i className="bi bi-key-fill text-ava-secondary me-2" />
                                    {t('clients.secretModal.title')}
                                </h5>
                            </div>
                            <div className="modal-body">
                                {revealedSecret && (
                                    <>
                                        <p className="text-ava-muted small">{t('clients.secretModal.body')}</p>
                                        <div className="input-group mb-3">
                                            <input type="text" readOnly className="form-control font-monospace small" value={revealedSecret} />
                                            <button type="button" className="btn btn-outline-primary" onClick={copySecret}>
                                                <i className={`bi ${secretCopied ? 'bi-check-lg' : 'bi-clipboard'}`} />
                                            </button>
                                        </div>
                                    </>
                                )}
                                {revealedLogoutSecret && (
                                    <>
                                        <p className="text-ava-muted small">{t('clients.secretModal.logoutSecretBody')}</p>
                                        <div className="input-group">
                                            <input
                                                type="text"
                                                readOnly
                                                className="form-control font-monospace small"
                                                value={revealedLogoutSecret}
                                            />
                                            <button type="button" className="btn btn-outline-primary" onClick={copyLogoutSecret}>
                                                <i className={`bi ${logoutSecretCopied ? 'bi-check-lg' : 'bi-clipboard'}`} />
                                            </button>
                                        </div>
                                    </>
                                )}
                            </div>
                            <div className="modal-footer">
                                <button
                                    type="button"
                                    className="btn btn-primary"
                                    onClick={() => {
                                        setRevealedSecret(null)
                                        setRevealedLogoutSecret(null)
                                    }}
                                >
                                    {t('clients.secretModal.saved')}
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
                                <h5 className="modal-title">
                                    {editingClient ? t('clients.modal.editTitle', { name: editingClient.app_name }) : t('clients.modal.createTitle')}
                                </h5>
                                <button type="button" className="btn-close" onClick={closeModal} />
                            </div>
                            <form onSubmit={submit}>
                                <div className="modal-body">
                                    <div className="mb-3">
                                        <label className="form-label" htmlFor="name">{t('clients.modal.appName')}</label>
                                        <input
                                            id="name"
                                            className="form-control"
                                            value={form.data.name}
                                            onChange={(e) => form.setData('name', e.target.value)}
                                        />
                                        {form.errors.name && <div className="text-danger small mt-1">{form.errors.name}</div>}
                                    </div>
                                    <div className="mb-3">
                                        <label className="form-label" htmlFor="redirect">{t('clients.modal.redirectUri')}</label>
                                        <input
                                            id="redirect"
                                            className="form-control"
                                            value={form.data.redirect}
                                            onChange={(e) => form.setData('redirect', e.target.value)}
                                        />
                                        {form.errors.redirect && <div className="text-danger small mt-1">{form.errors.redirect}</div>}
                                    </div>
                                    <div className="mb-3">
                                        <label className="form-label" htmlFor="homepage_url">{t('clients.modal.homepageUrl')}</label>
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
                                        <label className="form-label" htmlFor="logout_uri">{t('clients.modal.logoutUri')}</label>
                                        <input
                                            id="logout_uri"
                                            className="form-control"
                                            value={form.data.logout_uri}
                                            onChange={(e) => form.setData('logout_uri', e.target.value)}
                                        />
                                        <div className="form-text">{t('clients.modal.logoutUriHelp')}</div>
                                        {form.errors.logout_uri && (
                                            <div className="text-danger small mt-1">{form.errors.logout_uri}</div>
                                        )}
                                    </div>
                                    <div className="mb-3">
                                        <label className="form-label" htmlFor="app_logo">{t('clients.modal.logo')}</label>
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
                                        <label className="form-check-label" htmlFor="is_internal">{t('clients.modal.internalApp')}</label>
                                    </div>
                                    {canManageUserSync && (
                                        <div className="mb-3">
                                            <div className="form-check form-switch">
                                                <input
                                                    id="allow_user_sync"
                                                    type="checkbox"
                                                    className="form-check-input"
                                                    checked={form.data.allow_user_sync}
                                                    onChange={(e) => form.setData('allow_user_sync', e.target.checked)}
                                                />
                                                <label className="form-check-label" htmlFor="allow_user_sync">{t('clients.modal.allowUserSync')}</label>
                                            </div>
                                            <div className="form-text">{t('clients.modal.allowUserSyncHelp')}</div>
                                        </div>
                                    )}

                                    {!editingClient && (
                                        <div className="mb-3">
                                            <label className="form-label" htmlFor="grant_type">{t('clients.modal.grantType')}</label>
                                            <select
                                                id="grant_type"
                                                className="form-select"
                                                value={form.data.grant_type}
                                                onChange={(e) => form.setData('grant_type', e.target.value as ClientForm['grant_type'])}
                                            >
                                                <option value="authorization_code">{t('clients.modal.grantTypeAuthCode')}</option>
                                                <option value="implicit">{t('clients.modal.grantTypeImplicit')}</option>
                                            </select>
                                            <p className="text-ava-muted text-tiny mt-1 mb-0">{t('clients.modal.grantTypeHelp')}</p>
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
                                                {t('clients.modal.publicClient')}
                                            </label>
                                            <p className="text-ava-muted text-tiny mt-1 mb-0">{t('clients.modal.publicClientHelp')}</p>
                                        </div>
                                    )}

                                    <div className="mb-3">
                                        <label className="form-label" htmlFor="access_mode">{t('clients.modal.whoCanAuthenticate')}</label>
                                        <select
                                            id="access_mode"
                                            className="form-select"
                                            value={form.data.access_mode}
                                            onChange={(e) => form.setData('access_mode', e.target.value as ClientForm['access_mode'])}
                                        >
                                            <option value="all">{t('targeting.all')}</option>
                                            <option value="stage">{t('targeting.stage')}</option>
                                            <option value="specific">{t('targeting.specific')}</option>
                                            <option value="group">{t('targeting.group')}</option>
                                            <option value="all_except_users">{t('targeting.allExceptUsers')}</option>
                                        </select>
                                        {form.errors.access_mode && (
                                            <div className="text-danger small mt-1">{form.errors.access_mode}</div>
                                        )}
                                    </div>

                                    {form.data.access_mode === 'stage' && (
                                        <div className="mb-3">
                                            <label className="form-label">{t('clients.modal.allowedStages')}</label>
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
                                            <label className="form-label">{t('clients.modal.allowedUsers')}</label>
                                            <input
                                                type="text"
                                                className="form-control mb-2"
                                                placeholder={t('search.users')}
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
                                                    <p className="text-ava-muted text-tiny mb-0">{t('search.noUsersMatch')}</p>
                                                )}
                                            </div>
                                            {form.errors.user_ids && (
                                                <div className="text-danger small mt-1">{form.errors.user_ids}</div>
                                            )}
                                        </div>
                                    )}

                                    {form.data.access_mode === 'group' && (
                                        <div className="mb-3">
                                            <label className="form-label">{t('clients.modal.allowedGroups')}</label>
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
                                                        {t('search.noGroupsCreateFirst')}
                                                    </p>
                                                )}
                                            </div>
                                            {form.errors.group_ids && (
                                                <div className="text-danger small mt-1">{form.errors.group_ids}</div>
                                            )}
                                        </div>
                                    )}

                                    {form.data.access_mode === 'all_except_users' && (
                                        <div className="mb-3">
                                            <label className="form-label">{t('clients.modal.excludedUsers')}</label>
                                            <input
                                                type="text"
                                                className="form-control mb-2"
                                                placeholder={t('search.users')}
                                                value={userSearch}
                                                onChange={(e) => setUserSearch(e.target.value)}
                                            />
                                            <div className="permission-list scroll-picker">
                                                {filteredUsers.map((u) => (
                                                    <div key={u.id} className="form-check">
                                                        <input
                                                            id={`excluded-user-${u.id}`}
                                                            type="checkbox"
                                                            className="form-check-input"
                                                            checked={form.data.excluded_user_ids.includes(u.id)}
                                                            onChange={() => toggleExcludedUser(u.id)}
                                                        />
                                                        <label className="form-check-label" htmlFor={`excluded-user-${u.id}`}>
                                                            {u.name}
                                                            <span className="text-ava-muted text-tiny ms-1">{u.email}</span>
                                                        </label>
                                                    </div>
                                                ))}
                                                {filteredUsers.length === 0 && (
                                                    <p className="text-ava-muted text-tiny mb-0">{t('search.noUsersMatch')}</p>
                                                )}
                                            </div>
                                            {form.errors.excluded_user_ids && (
                                                <div className="text-danger small mt-1">{form.errors.excluded_user_ids}</div>
                                            )}
                                        </div>
                                    )}
                                </div>
                                <div className="modal-footer">
                                    <button type="button" className="btn btn-outline-primary" onClick={closeModal}>
                                        {t('common:actions.cancel')}
                                    </button>
                                    <button type="submit" className="btn btn-primary" disabled={form.processing}>
                                        {editingClient ? t('actions.saveChanges') : t('actions.register')}
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
