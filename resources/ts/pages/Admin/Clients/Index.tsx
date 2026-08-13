import { useState } from 'react'
import type { FormEvent } from 'react'
import { router, useForm } from '@inertiajs/react'
import AdminLayout from '@/components/layouts/AdminLayout'
import Pagination from '@/components/shared/Pagination'
import StatusBadge from '@/components/shared/StatusBadge'
import type { ClientMeta, PaginatedData } from '@/types'

interface IndexProps {
    clients: PaginatedData<ClientMeta>
}

interface ClientForm {
    name: string
    redirect: string
    homepage_url: string
    app_logo_url: string
    is_internal: boolean
}

export default function Index({ clients }: IndexProps) {
    const [showModal, setShowModal] = useState(false)
    const form = useForm<ClientForm>({
        name: '',
        redirect: '',
        homepage_url: '',
        app_logo_url: '',
        is_internal: false,
    })

    const submit = (e: FormEvent) => {
        e.preventDefault()
        form.post('/admin/clients', { onSuccess: () => setShowModal(false) })
    }

    const copyId = (id: string) => {
        navigator.clipboard.writeText(id)
    }

    const revoke = (client: ClientMeta) => {
        if (confirm(`Revoke client ${client.app_name}?`)) {
            router.delete(`/admin/clients/${client.id}`)
        }
    }

    const internalCount = clients.data.filter((c) => c.is_internal).length
    const externalCount = clients.data.length - internalCount

    return (
        <AdminLayout
            title="OAuth Clients"
            action={
                <button type="button" className="btn btn-primary" onClick={() => setShowModal(true)}>
                    <i className="bi bi-plus-lg me-1" />
                    Register New Client
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

            <div className="ava-card">
                <table className="table mb-0">
                    <thead>
                        <tr>
                            <th>App Name</th>
                            <th>Client ID</th>
                            <th>Type</th>
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
                                            <div className={`icon-tile ${client.is_internal ? 'text-ava-gold' : 'text-ava-muted'}`}>
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
                                    <StatusBadge status={client.oauth_client.revoked ? 'revoked' : 'active'} />
                                </td>
                                <td className="text-truncate cell-truncate-220">{client.oauth_client.redirect}</td>
                                <td>
                                    <button type="button" className="btn btn-sm btn-outline-primary" onClick={() => revoke(client)}>
                                        Revoke
                                    </button>
                                </td>
                            </tr>
                        ))}
                    </tbody>
                </table>
            </div>

            <div className="mt-3">
                <Pagination links={clients.links} />
            </div>

            {showModal && (
                <div className="modal d-block modal-backdrop-custom" tabIndex={-1}>
                    <div className="modal-dialog">
                        <div className="modal-content">
                            <div className="modal-header">
                                <h5 className="modal-title">Register New Client</h5>
                                <button type="button" className="btn-close" onClick={() => setShowModal(false)} />
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
                                        <label className="form-label" htmlFor="app_logo_url">Logo URL</label>
                                        <input
                                            id="app_logo_url"
                                            className="form-control"
                                            value={form.data.app_logo_url}
                                            onChange={(e) => form.setData('app_logo_url', e.target.value)}
                                        />
                                        {form.errors.app_logo_url && (
                                            <div className="text-danger small mt-1">{form.errors.app_logo_url}</div>
                                        )}
                                    </div>
                                    <div className="form-check form-switch">
                                        <input
                                            id="is_internal"
                                            type="checkbox"
                                            className="form-check-input"
                                            checked={form.data.is_internal}
                                            onChange={(e) => form.setData('is_internal', e.target.checked)}
                                        />
                                        <label className="form-check-label" htmlFor="is_internal">Internal application</label>
                                    </div>
                                </div>
                                <div className="modal-footer">
                                    <button type="button" className="btn btn-outline-primary" onClick={() => setShowModal(false)}>
                                        Cancel
                                    </button>
                                    <button type="submit" className="btn btn-primary" disabled={form.processing}>
                                        Register
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
