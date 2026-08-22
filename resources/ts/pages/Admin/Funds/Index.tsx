import { useState } from 'react'
import type { FormEvent } from 'react'
import { router, useForm } from '@inertiajs/react'
import { useTranslation } from 'react-i18next'
import AdminLayout from '@/components/layouts/AdminLayout'
import { useConfirm } from '@/components/shared/ConfirmDialog'
import type { Fund } from '@/types'

interface IndexProps {
    funds: Fund[]
}

interface FundForm {
    name: string
    description: string
    is_active: boolean
}

const emptyForm: FundForm = { name: '', description: '', is_active: true }

export default function Index({ funds }: IndexProps) {
    const { t } = useTranslation('donations')
    const [showModal, setShowModal] = useState(false)
    const [editingFund, setEditingFund] = useState<Fund | null>(null)
    const form = useForm<FundForm>(emptyForm)
    const confirm = useConfirm()

    const openCreateModal = () => {
        setEditingFund(null)
        form.reset()
        form.clearErrors()
        setShowModal(true)
    }

    const openEditModal = (fund: Fund) => {
        setEditingFund(fund)
        form.setData({ name: fund.name, description: fund.description ?? '', is_active: fund.is_active })
        form.clearErrors()
        setShowModal(true)
    }

    const closeModal = () => {
        setShowModal(false)
        setEditingFund(null)
    }

    const submit = (e: FormEvent) => {
        e.preventDefault()
        if (editingFund) {
            form.put(`/admin/funds/${editingFund.id}`, { onSuccess: closeModal })
        } else {
            form.post('/admin/funds', { onSuccess: closeModal })
        }
    }

    const remove = async (fund: Fund) => {
        if (await confirm(t('funds.confirmDelete', { name: fund.name }), { confirmLabel: t('funds.delete') })) {
            router.delete(`/admin/funds/${fund.id}`)
        }
    }

    return (
        <AdminLayout
            title={t('funds.title')}
            action={
                <button type="button" className="btn btn-primary btn-sm" onClick={openCreateModal}>
                    <i className="bi bi-plus-lg" />
                    <span className="d-none d-sm-inline ms-1">{t('funds.addFund')}</span>
                </button>
            }
        >
            <p className="text-ava-muted mb-4">
                {t('funds.description')}
            </p>

            <div className="ava-card">
                <div className="table-responsive">
                    <table className="table mb-0">
                        <thead>
                            <tr>
                                <th>{t('funds.table.name')}</th>
                                <th>{t('funds.table.description')}</th>
                                <th>{t('funds.table.donations')}</th>
                                <th>{t('funds.table.status')}</th>
                                <th />
                            </tr>
                        </thead>
                        <tbody>
                            {funds.map((fund) => (
                                <tr key={fund.id}>
                                    <td className="fw-semibold">{fund.name}</td>
                                    <td className="text-ava-muted">{fund.description ?? '—'}</td>
                                    <td className="text-ava-muted">{fund.donations_count ?? 0}</td>
                                    <td>
                                        <span className={`badge ${fund.is_active ? 'bg-success' : 'bg-secondary'}`}>
                                            {fund.is_active ? t('funds.active') : t('funds.inactive')}
                                        </span>
                                    </td>
                                    <td className="text-nowrap">
                                        <div className="d-flex gap-2">
                                            <button type="button" className="btn btn-sm btn-outline-primary" onClick={() => openEditModal(fund)}>
                                                {t('funds.edit')}
                                            </button>
                                            <button type="button" className="btn btn-sm btn-outline-primary" onClick={() => remove(fund)}>
                                                {t('funds.delete')}
                                            </button>
                                        </div>
                                    </td>
                                </tr>
                            ))}
                            {funds.length === 0 && (
                                <tr>
                                    <td colSpan={5} className="text-center text-ava-muted py-4">{t('funds.empty')}</td>
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
                                <h5 className="modal-title">{editingFund ? t('funds.modal.editTitle', { name: editingFund.name }) : t('funds.modal.createTitle')}</h5>
                                <button type="button" className="btn-close" onClick={closeModal} />
                            </div>
                            <form onSubmit={submit}>
                                <div className="modal-body">
                                    <div className="mb-3">
                                        <label className="form-label" htmlFor="name">{t('funds.modal.name')}</label>
                                        <input
                                            id="name"
                                            className="form-control"
                                            placeholder={t('funds.modal.namePlaceholder')}
                                            value={form.data.name}
                                            onChange={(e) => form.setData('name', e.target.value)}
                                        />
                                        {form.errors.name && <div className="text-danger small mt-1">{form.errors.name}</div>}
                                    </div>
                                    <div className="mb-3">
                                        <label className="form-label" htmlFor="description">{t('funds.modal.description')}</label>
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
                                    <div className="form-check">
                                        <input
                                            id="is_active"
                                            type="checkbox"
                                            className="form-check-input"
                                            checked={form.data.is_active}
                                            onChange={(e) => form.setData('is_active', e.target.checked)}
                                        />
                                        <label className="form-check-label" htmlFor="is_active">
                                            {t('funds.modal.activeHelp')}
                                        </label>
                                    </div>
                                </div>
                                <div className="modal-footer">
                                    <button type="button" className="btn btn-outline-primary" onClick={closeModal}>
                                        {t('funds.cancel')}
                                    </button>
                                    <button type="submit" className="btn btn-primary" disabled={form.processing}>
                                        {editingFund ? t('funds.saveChanges') : t('funds.addFund')}
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
