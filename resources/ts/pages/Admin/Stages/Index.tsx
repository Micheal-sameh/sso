import { useState } from 'react'
import type { FormEvent } from 'react'
import { router, useForm } from '@inertiajs/react'
import AdminLayout from '@/components/layouts/AdminLayout'
import { useConfirm } from '@/components/shared/ConfirmDialog'
import type { Stage } from '@/types'

interface IndexProps {
    stages: Stage[]
}

interface StageForm {
    name: string
    code: string
}

const emptyForm: StageForm = { name: '', code: '' }

export default function Index({ stages }: IndexProps) {
    const [showModal, setShowModal] = useState(false)
    const [editingStage, setEditingStage] = useState<Stage | null>(null)
    const form = useForm<StageForm>(emptyForm)
    const confirm = useConfirm()

    const openCreateModal = () => {
        setEditingStage(null)
        form.reset()
        form.clearErrors()
        setShowModal(true)
    }

    const openEditModal = (stage: Stage) => {
        setEditingStage(stage)
        form.setData({ name: stage.name, code: stage.code })
        form.clearErrors()
        setShowModal(true)
    }

    const closeModal = () => {
        setShowModal(false)
        setEditingStage(null)
    }

    const submit = (e: FormEvent) => {
        e.preventDefault()
        if (editingStage) {
            form.put(`/admin/stages/${editingStage.id}`, { onSuccess: closeModal })
        } else {
            form.post('/admin/stages', { onSuccess: closeModal })
        }
    }

    const remove = async (stage: Stage) => {
        if (await confirm(`Delete the "${stage.name}" stage?`, { confirmLabel: 'Delete' })) {
            router.delete(`/admin/stages/${stage.id}`)
        }
    }

    return (
        <AdminLayout
            title="Stages"
            action={
                <button type="button" className="btn btn-primary btn-sm" onClick={openCreateModal}>
                    <i className="bi bi-plus-lg" />
                    <span className="d-none d-sm-inline ms-1">Add Stage</span>
                </button>
            }
        >
            <p className="text-ava-muted mb-4">
                Every user must belong to a stage (e.g. Primary, Preparatory, Secondary, Youth, Adult).
            </p>

            {/* Table view (md and up) */}
            <div className="ava-card d-none d-md-block">
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
                                        <div className="d-flex gap-2">
                                            <button
                                                type="button"
                                                className="btn btn-sm btn-outline-primary"
                                                onClick={() => openEditModal(stage)}
                                            >
                                                Edit
                                            </button>
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
                {stages.map((stage) => (
                    <div key={stage.id} className="record-card">
                        <div className="d-flex align-items-center justify-content-between mb-2">
                            <div className="fw-semibold">{stage.name}</div>
                            <span className="code-chip">{stage.code}</span>
                        </div>

                        <div className="record-card-row">
                            <span className="record-card-label">Users</span>
                            <span className="text-ava-muted">{stage.users_count ?? 0}</span>
                        </div>

                        <div className="record-card-actions">
                            <button type="button" className="btn btn-sm btn-outline-primary" onClick={() => openEditModal(stage)}>
                                Edit
                            </button>
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
                        </div>
                    </div>
                ))}
                {stages.length === 0 && (
                    <div className="ava-card text-center text-ava-muted py-4">No stages yet.</div>
                )}
            </div>

            {showModal && (
                <div className="modal d-block modal-backdrop-custom" tabIndex={-1}>
                    <div className="modal-dialog">
                        <div className="modal-content">
                            <div className="modal-header">
                                <h5 className="modal-title">{editingStage ? `Edit ${editingStage.name}` : 'Add Stage'}</h5>
                                <button type="button" className="btn-close" onClick={closeModal} />
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
                                    <button type="button" className="btn btn-outline-primary" onClick={closeModal}>
                                        Cancel
                                    </button>
                                    <button type="submit" className="btn btn-primary" disabled={form.processing}>
                                        {editingStage ? 'Save Changes' : 'Add Stage'}
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
