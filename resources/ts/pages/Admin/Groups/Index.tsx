import { useState } from 'react'
import type { FormEvent } from 'react'
import { router, useForm } from '@inertiajs/react'
import { useTranslation } from 'react-i18next'
import AdminLayout from '@/components/layouts/AdminLayout'
import ActionMenu from '@/components/shared/ActionMenu'
import { useConfirm } from '@/components/shared/ConfirmDialog'
import EmptyState from '@/components/shared/EmptyState'
import type { Group } from '@/types'

interface IndexProps {
    groups: Group[]
}

interface GroupForm {
    name: string
    description: string
}

const emptyForm: GroupForm = { name: '', description: '' }

export default function Index({ groups }: IndexProps) {
    const { t } = useTranslation(['admin', 'common'])
    const [showModal, setShowModal] = useState(false)
    const [editingGroup, setEditingGroup] = useState<Group | null>(null)
    const form = useForm<GroupForm>(emptyForm)
    const confirm = useConfirm()

    const openCreateModal = () => {
        setEditingGroup(null)
        form.reset()
        form.clearErrors()
        setShowModal(true)
    }

    const openEditModal = (group: Group) => {
        setEditingGroup(group)
        form.setData({ name: group.name, description: group.description ?? '' })
        form.clearErrors()
        setShowModal(true)
    }

    const closeModal = () => {
        setShowModal(false)
        setEditingGroup(null)
    }

    const submit = (e: FormEvent) => {
        e.preventDefault()
        if (editingGroup) {
            form.put(`/admin/groups/${editingGroup.id}`, { onSuccess: closeModal })
        } else {
            form.post('/admin/groups', { onSuccess: closeModal })
        }
    }

    const remove = async (group: Group) => {
        if (await confirm(t('groups.confirmDelete', { name: group.name }), { confirmLabel: t('common:actions.delete') })) {
            router.delete(`/admin/groups/${group.id}`)
        }
    }

    return (
        <AdminLayout
            title={t('groups.title')}
            action={
                <button type="button" className="btn btn-primary btn-sm" onClick={openCreateModal}>
                    <i className="bi bi-plus-lg" />
                    <span className="d-none d-sm-inline ms-1">{t('groups.addGroup')}</span>
                </button>
            }
        >
            <p className="text-ava-muted mb-4">{t('groups.description')}</p>

            {/* Table view (md and up) */}
            <div className="ava-card d-none d-md-block">
                <div className="table-responsive">
                    <table className="table mb-0">
                        <thead>
                            <tr>
                                <th>{t('table.name')}</th>
                                <th>{t('table.description')}</th>
                                <th>{t('table.users')}</th>
                                <th />
                            </tr>
                        </thead>
                        <tbody>
                            {groups.map((group) => (
                                <tr key={group.id}>
                                    <td className="fw-semibold">{group.name}</td>
                                    <td className="text-ava-muted">{group.description ?? '—'}</td>
                                    <td className="text-ava-muted">{group.users_count ?? 0}</td>
                                    <td className="text-nowrap">
                                        <div className="d-flex align-items-center justify-content-end gap-2">
                                            <button
                                                type="button"
                                                className="btn btn-sm btn-primary"
                                                onClick={() => router.visit(`/admin/groups/${group.id}`)}
                                            >
                                                <i className="bi bi-people me-1" />
                                                {t('groups.manageUsers')}
                                            </button>
                                            <ActionMenu
                                                items={[
                                                    { label: t('common:actions.edit'), icon: 'bi-pencil', onClick: () => openEditModal(group) },
                                                    {
                                                        label: t('common:actions.delete'),
                                                        icon: 'bi-trash',
                                                        danger: true,
                                                        onClick: () => remove(group),
                                                    },
                                                ]}
                                            />
                                        </div>
                                    </td>
                                </tr>
                            ))}
                        </tbody>
                    </table>
                    {groups.length === 0 && <EmptyState icon="bi-collection" message={t('groups.empty')} />}
                </div>
            </div>

            {/* Card view (below md) */}
            <div className="d-md-none">
                {groups.map((group) => (
                    <div key={group.id} className="record-card">
                        <div className="fw-semibold mb-1">{group.name}</div>
                        <div className="text-ava-muted text-tiny mb-2">{group.description ?? '—'}</div>

                        <div className="record-card-row">
                            <span className="record-card-label">{t('table.users')}</span>
                            <span className="text-ava-muted">{group.users_count ?? 0}</span>
                        </div>

                        <div className="record-card-actions">
                            <button
                                type="button"
                                className="btn btn-sm btn-primary"
                                onClick={() => router.visit(`/admin/groups/${group.id}`)}
                            >
                                <i className="bi bi-people me-1" />
                                {t('groups.manageUsers')}
                            </button>
                            <button type="button" className="btn btn-sm btn-outline-primary" onClick={() => openEditModal(group)}>
                                {t('common:actions.edit')}
                            </button>
                            <button type="button" className="btn btn-sm btn-outline-primary" onClick={() => remove(group)}>
                                {t('common:actions.delete')}
                            </button>
                        </div>
                    </div>
                ))}
                {groups.length === 0 && (
                    <div className="ava-card text-center text-ava-muted py-4">{t('groups.empty')}</div>
                )}
            </div>

            {showModal && (
                <div className="modal d-block modal-backdrop-custom" tabIndex={-1}>
                    <div className="modal-dialog">
                        <div className="modal-content">
                            <div className="modal-header">
                                <h5 className="modal-title">
                                    {editingGroup ? t('groups.modal.editTitle', { name: editingGroup.name }) : t('groups.modal.createTitle')}
                                </h5>
                                <button type="button" className="btn-close" onClick={closeModal} />
                            </div>
                            <form onSubmit={submit}>
                                <div className="modal-body">
                                    <div className="mb-3">
                                        <label className="form-label" htmlFor="name">{t('groups.modal.name')}</label>
                                        <input
                                            id="name"
                                            className="form-control"
                                            placeholder={t('groups.modal.namePlaceholder')}
                                            value={form.data.name}
                                            onChange={(e) => form.setData('name', e.target.value)}
                                        />
                                        {form.errors.name && <div className="text-danger small mt-1">{form.errors.name}</div>}
                                    </div>
                                    <div className="mb-3">
                                        <label className="form-label" htmlFor="description">{t('groups.modal.description')}</label>
                                        <textarea
                                            id="description"
                                            className="form-control"
                                            rows={3}
                                            value={form.data.description}
                                            onChange={(e) => form.setData('description', e.target.value)}
                                        />
                                        {form.errors.description && (
                                            <div className="text-danger small mt-1">{form.errors.description}</div>
                                        )}
                                    </div>
                                </div>
                                <div className="modal-footer">
                                    <button type="button" className="btn btn-outline-primary" onClick={closeModal}>
                                        {t('common:actions.cancel')}
                                    </button>
                                    <button type="submit" className="btn btn-primary" disabled={form.processing}>
                                        {editingGroup ? t('actions.saveChanges') : t('groups.addGroup')}
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
