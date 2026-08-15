import { useState } from 'react'
import type { FormEvent } from 'react'
import { router, useForm } from '@inertiajs/react'
import AdminLayout from '@/components/layouts/AdminLayout'
import type { Stage } from '@/types'

interface IndexProps {
    stages: Stage[]
}

interface StageForm {
    name: string
    code: string
}

export default function Index({ stages }: IndexProps) {
    const [showModal, setShowModal] = useState(false)
    const form = useForm<StageForm>({ name: '', code: '' })

    const submit = (e: FormEvent) => {
        e.preventDefault()
        form.post('/admin/stages', { onSuccess: () => setShowModal(false) })
    }

    const remove = (stage: Stage) => {
        if (confirm(`Delete the "${stage.name}" stage?`)) {
            router.delete(`/admin/stages/${stage.id}`)
        }
    }

    return (
        <AdminLayout
            title="Stages"
            action={
                <button type="button" className="btn btn-primary btn-sm" onClick={() => setShowModal(true)}>
                    <i className="bi bi-plus-lg" />
                    <span className="d-none d-sm-inline ms-1">Add Stage</span>
                </button>
            }
        >
            <p className="text-ava-muted mb-4">
                Every user must belong to a stage (e.g. Primary, Preparatory, Secondary, Youth, Adult).
            </p>

            <div className="ava-card">
                <div className="table-responsive">
                    <table className="table mb-0">
                        <thead>
                            <tr>
                                <th>Name</th>
                                <th>Code</th>
                                <th>Users</th>
                                <th />
                            </tr>
                        </thead>
                        <tbody>
                            {stages.map((stage) => (
                                <tr key={stage.id}>
                                    <td className="fw-semibold">{stage.name}</td>
                                    <td>
                                        <span className="code-chip">{stage.code}</span>
                                    </td>
                                    <td className="text-ava-muted">{stage.users_count ?? 0}</td>
                                    <td className="text-nowrap">
                                        <button
                                            type="button"
                                            className="btn btn-sm btn-outline-primary"
                                            disabled={(stage.users_count ?? 0) > 0}
                                            title={
                                                (stage.users_count ?? 0) > 0
                                                    ? 'Reassign these users to another stage before deleting'
                                                    : undefined
                                            }
                                            onClick={() => remove(stage)}
                                        >
                                            Delete
                                        </button>
                                    </td>
                                </tr>
                            ))}
                        </tbody>
                    </table>
                </div>
            </div>

            {showModal && (
                <div className="modal d-block modal-backdrop-custom" tabIndex={-1}>
                    <div className="modal-dialog">
                        <div className="modal-content">
                            <div className="modal-header">
                                <h5 className="modal-title">Add Stage</h5>
                                <button type="button" className="btn-close" onClick={() => setShowModal(false)} />
                            </div>
                            <form onSubmit={submit}>
                                <div className="modal-body">
                                    <div className="mb-3">
                                        <label className="form-label" htmlFor="name">Name</label>
                                        <input
                                            id="name"
                                            className="form-control"
                                            placeholder="e.g. Primary"
                                            value={form.data.name}
                                            onChange={(e) => form.setData('name', e.target.value)}
                                        />
                                        {form.errors.name && <div className="text-danger small mt-1">{form.errors.name}</div>}
                                    </div>
                                    <div className="mb-3">
                                        <label className="form-label" htmlFor="code">Code</label>
                                        <input
                                            id="code"
                                            className="form-control"
                                            placeholder="e.g. pri"
                                            value={form.data.code}
                                            onChange={(e) => form.setData('code', e.target.value)}
                                        />
                                        {form.errors.code && <div className="text-danger small mt-1">{form.errors.code}</div>}
                                    </div>
                                </div>
                                <div className="modal-footer">
                                    <button type="button" className="btn btn-outline-primary" onClick={() => setShowModal(false)}>
                                        Cancel
                                    </button>
                                    <button type="submit" className="btn btn-primary" disabled={form.processing}>
                                        Add Stage
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
