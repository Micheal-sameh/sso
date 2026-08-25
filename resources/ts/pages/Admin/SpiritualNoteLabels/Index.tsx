import { useState } from 'react'
import type { FormEvent } from 'react'
import { router, useForm } from '@inertiajs/react'
import { useTranslation } from 'react-i18next'
import AdminLayout from '@/components/layouts/AdminLayout'
import { useConfirm } from '@/components/shared/ConfirmDialog'
import type { SpiritualNoteLabel } from '@/types'

interface IndexProps {
    labels: SpiritualNoteLabel[]
}

interface LabelForm {
    name: string
    sort_order: number
    is_active: boolean
}

const emptyForm: LabelForm = { name: '', sort_order: 0, is_active: true }

export default function Index({ labels }: IndexProps) {
    const { t } = useTranslation('admin')
    const [showModal, setShowModal] = useState(false)
    const [editingLabel, setEditingLabel] = useState<SpiritualNoteLabel | null>(null)
    const form = useForm<LabelForm>(emptyForm)
    const confirm = useConfirm()

    const openCreateModal = () => {
        setEditingLabel(null)
        form.reset()
        form.clearErrors()
        setShowModal(true)
    }

    const openEditModal = (label: SpiritualNoteLabel) => {
        setEditingLabel(label)
        form.setData({ name: label.name, sort_order: label.sort_order, is_active: label.is_active })
        form.clearErrors()
        setShowModal(true)
    }

    const closeModal = () => {
        setShowModal(false)
        setEditingLabel(null)
    }

    const submit = (e: FormEvent) => {
        e.preventDefault()
        if (editingLabel) {
            form.put(`/admin/spiritual-note-labels/${editingLabel.id}`, { onSuccess: closeModal })
        } else {
            form.post('/admin/spiritual-note-labels', { onSuccess: closeModal })
        }
    }

    const remove = async (label: SpiritualNoteLabel) => {
        if (await confirm(t('spiritualNoteLabels.confirmDelete', { name: label.name }), { confirmLabel: t('spiritualNoteLabels.delete') })) {
            router.delete(`/admin/spiritual-note-labels/${label.id}`)
        }
    }

    return (
        <AdminLayout
            title={t('spiritualNoteLabels.title')}
            action={
                <button type="button" className="btn btn-primary btn-sm" onClick={openCreateModal}>
                    <i className="bi bi-plus-lg" />
                    <span className="d-none d-sm-inline ms-1">{t('spiritualNoteLabels.addLabel')}</span>
                </button>
            }
        >
            <p className="text-ava-muted mb-4">
                {t('spiritualNoteLabels.description')}
            </p>

            <div className="ava-card">
                <div className="table-responsive">
                    <table className="table mb-0">
                        <thead>
                            <tr>
                                <th>{t('spiritualNoteLabels.table.name')}</th>
                                <th>{t('spiritualNoteLabels.table.sortOrder')}</th>
                                <th>{t('spiritualNoteLabels.table.status')}</th>
                                <th />
                            </tr>
                        </thead>
                        <tbody>
                            {labels.map((label) => (
                                <tr key={label.id}>
                                    <td className="fw-semibold">{label.name}</td>
                                    <td className="text-ava-muted">{label.sort_order}</td>
                                    <td>
                                        <span className={`badge ${label.is_active ? 'bg-success' : 'bg-secondary'}`}>
                                            {label.is_active ? t('spiritualNoteLabels.active') : t('spiritualNoteLabels.inactive')}
                                        </span>
                                    </td>
                                    <td className="text-nowrap">
                                        <div className="d-flex gap-2">
                                            <button type="button" className="btn btn-sm btn-outline-primary" onClick={() => openEditModal(label)}>
                                                {t('spiritualNoteLabels.edit')}
                                            </button>
                                            <button type="button" className="btn btn-sm btn-outline-primary" onClick={() => remove(label)}>
                                                {t('spiritualNoteLabels.delete')}
                                            </button>
                                        </div>
                                    </td>
                                </tr>
                            ))}
                            {labels.length === 0 && (
                                <tr>
                                    <td colSpan={4} className="text-center text-ava-muted py-4">{t('spiritualNoteLabels.empty')}</td>
                                </tr>
                            )}
                        </tbody>
                    </table>
                </div>
            </div>

            {showModal && (
                <div className="modal d-block modal-backdrop-custom" tabIndex={-1}>
                    <div className="modal-dialog">
                        <div className="modal-content">
                            <div className="modal-header">
                                <h5 className="modal-title">
                                    {editingLabel
                                        ? t('spiritualNoteLabels.modal.editTitle', { name: editingLabel.name })
                                        : t('spiritualNoteLabels.modal.createTitle')}
                                </h5>
                                <button type="button" className="btn-close" onClick={closeModal} />
                            </div>
                            <form onSubmit={submit}>
                                <div className="modal-body">
                                    <div className="mb-3">
                                        <label className="form-label" htmlFor="name">{t('spiritualNoteLabels.modal.name')}</label>
                                        <input
                                            id="name"
                                            className="form-control"
                                            placeholder={t('spiritualNoteLabels.modal.namePlaceholder')}
                                            value={form.data.name}
                                            onChange={(e) => form.setData('name', e.target.value)}
                                        />
                                        {form.errors.name && <div className="text-danger small mt-1">{form.errors.name}</div>}
                                    </div>
                                    <div className="mb-3">
                                        <label className="form-label" htmlFor="sort_order">{t('spiritualNoteLabels.modal.sortOrder')}</label>
                                        <input
                                            id="sort_order"
                                            type="number"
                                            min={0}
                                            className="form-control"
                                            value={form.data.sort_order}
                                            onChange={(e) => form.setData('sort_order', Number(e.target.value))}
                                        />
                                        {form.errors.sort_order && (
                                            <div className="text-danger small mt-1">{form.errors.sort_order}</div>
                                        )}
                                    </div>
                                    <div className="form-check">
                                        <input
                                            id="is_active"
                                            type="checkbox"
                                            className="form-check-input"
                                            checked={form.data.is_active}
                                            onChange={(e) => form.setData('is_active', e.target.checked)}
                                        />
                                        <label className="form-check-label" htmlFor="is_active">
                                            {t('spiritualNoteLabels.modal.activeHelp')}
                                        </label>
                                    </div>
                                </div>
                                <div className="modal-footer">
                                    <button type="button" className="btn btn-outline-primary" onClick={closeModal}>
                                        {t('spiritualNoteLabels.cancel')}
                                    </button>
                                    <button type="submit" className="btn btn-primary" disabled={form.processing}>
                                        {editingLabel ? t('spiritualNoteLabels.saveChanges') : t('spiritualNoteLabels.addLabel')}
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
