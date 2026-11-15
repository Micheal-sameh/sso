import { router } from '@inertiajs/react'
import { useTranslation } from 'react-i18next'
import AdminLayout from '@/components/layouts/AdminLayout'
import ActionMenu from '@/components/shared/ActionMenu'
import { useConfirm } from '@/components/shared/ConfirmDialog'
import EmptyState from '@/components/shared/EmptyState'
import type { Family } from '@/types'

interface IndexProps {
    families: Family[]
    totalFamilies: number
}

export default function Index({ families, totalFamilies }: IndexProps) {
    const { t } = useTranslation(['admin', 'common'])
    const confirm = useConfirm()

    const createFamily = () => {
        router.post('/admin/families')
    }

    const remove = async (family: Family) => {
        if (await confirm(t('families.confirmDelete'), { confirmLabel: t('common:actions.delete') })) {
            router.delete(`/admin/families/${family.id}`)
        }
    }

    const requireDataUpdateAll = async () => {
        if (await confirm(t('families.confirmRequireDataUpdateAll'), { confirmLabel: t('families.requireDataUpdateAll') })) {
            router.post('/admin/families/require-data-update', {}, { preserveScroll: true })
        }
    }

    return (
        <AdminLayout
            title={t('families.title')}
            action={
                <div className="d-flex gap-2">
                    <button type="button" className="btn btn-outline-danger btn-sm" onClick={requireDataUpdateAll}>
                        <i className="bi bi-exclamation-triangle" />
                        <span className="d-none d-sm-inline ms-1">{t('families.requireDataUpdateAll')}</span>
                    </button>
                    <button type="button" className="btn btn-primary btn-sm" onClick={createFamily}>
                        <i className="bi bi-plus-lg" />
                        <span className="d-none d-sm-inline ms-1">{t('families.addFamily')}</span>
                    </button>
                </div>
            }
        >
            <p className="text-ava-muted mb-3">{t('families.description')}</p>

            <div className="row g-3 mb-4">
                <div className="col-md-4">
                    <div className="stat-tile">
                        <p className="stat-tile-label mb-2">{t('families.stats.total')}</p>
                        <p className="stat-tile-value mb-0">{totalFamilies}</p>
                    </div>
                </div>
            </div>

            {/* Table view (md and up) */}
            <div className="ava-card d-none d-md-block">
                <div className="table-responsive">
                    <table className="table mb-0">
                        <thead>
                            <tr>
                                <th>{t('families.table.father')}</th>
                                <th>{t('families.table.mother')}</th>
                                <th>{t('families.table.children')}</th>
                                <th />
                            </tr>
                        </thead>
                        <tbody>
                            {families.map((family) => (
                                <tr key={family.id}>
                                    <td className="fw-semibold">{family.father?.name ?? t('families.notSet')}</td>
                                    <td className="text-ava-muted">{family.mother?.name ?? t('families.notSet')}</td>
                                    <td className="text-ava-muted">{family.children?.length ?? 0}</td>
                                    <td className="text-nowrap">
                                        <div className="d-flex align-items-center justify-content-end gap-2">
                                            <button
                                                type="button"
                                                className="btn btn-sm btn-primary"
                                                onClick={() => router.visit(`/admin/families/${family.id}`)}
                                            >
                                                <i className="bi bi-people me-1" />
                                                {t('families.manageMembers')}
                                            </button>
                                            <ActionMenu
                                                items={[
                                                    {
                                                        label: t('common:actions.delete'),
                                                        icon: 'bi-trash',
                                                        danger: true,
                                                        onClick: () => remove(family),
                                                    },
                                                ]}
                                            />
                                        </div>
                                    </td>
                                </tr>
                            ))}
                        </tbody>
                    </table>
                    {families.length === 0 && <EmptyState icon="bi-house-heart" message={t('families.empty')} />}
                </div>
            </div>

            {/* Card view (below md) */}
            <div className="d-md-none">
                {families.map((family) => (
                    <div key={family.id} className="record-card">
                        <div className="record-card-row">
                            <span className="record-card-label">{t('families.table.father')}</span>
                            <span className="text-ava-muted">{family.father?.name ?? t('families.notSet')}</span>
                        </div>
                        <div className="record-card-row">
                            <span className="record-card-label">{t('families.table.mother')}</span>
                            <span className="text-ava-muted">{family.mother?.name ?? t('families.notSet')}</span>
                        </div>
                        <div className="record-card-row">
                            <span className="record-card-label">{t('families.table.children')}</span>
                            <span className="text-ava-muted">{family.children?.length ?? 0}</span>
                        </div>

                        <div className="record-card-actions">
                            <button
                                type="button"
                                className="btn btn-sm btn-primary"
                                onClick={() => router.visit(`/admin/families/${family.id}`)}
                            >
                                <i className="bi bi-people me-1" />
                                {t('families.manageMembers')}
                            </button>
                            <button type="button" className="btn btn-sm btn-outline-primary" onClick={() => remove(family)}>
                                {t('common:actions.delete')}
                            </button>
                        </div>
                    </div>
                ))}
                {families.length === 0 && (
                    <div className="ava-card text-center text-ava-muted py-4">{t('families.empty')}</div>
                )}
            </div>
        </AdminLayout>
    )
}
