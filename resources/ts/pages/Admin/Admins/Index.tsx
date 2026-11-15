import { useEffect, useState } from 'react'
import { Link, router, usePage } from '@inertiajs/react'
import { useTranslation } from 'react-i18next'
import AdminLayout from '@/components/layouts/AdminLayout'
import ActionMenu from '@/components/shared/ActionMenu'
import AvatarInitials from '@/components/shared/AvatarInitials'
import { useConfirm } from '@/components/shared/ConfirmDialog'
import EmptyState from '@/components/shared/EmptyState'
import UserCell from '@/components/shared/UserCell'
import Pagination from '@/components/shared/Pagination'
import RoleChip from '@/components/shared/RoleChip'
import StatusBadge from '@/components/shared/StatusBadge'
import type { PaginatedData, SharedProps, User } from '@/types'

interface IndexProps {
    admins: PaginatedData<User>
    filters: { search?: string }
}

export default function Index({ admins, filters }: IndexProps) {
    const { t } = useTranslation('admin')
    const [search, setSearch] = useState(filters.search ?? '')
    const { auth } = usePage<SharedProps>().props
    const confirm = useConfirm()

    useEffect(() => {
        const timer = setTimeout(() => {
            router.get('/admin/admins', { search }, { preserveState: true, replace: true })
        }, 300)

        return () => clearTimeout(timer)
        // eslint-disable-next-line react-hooks/exhaustive-deps
    }, [search])

    const toggle = (user: User) => {
        router.patch(`/admin/users/${user.id}/toggle`, {}, { preserveScroll: true })
    }

    const revokeAdminAccess = async (user: User) => {
        if (await confirm(t('admins.confirmRevoke', { name: user.name }), { confirmLabel: t('actions.revoke') })) {
            router.patch(`/admin/users/${user.id}/toggle-admin-access`, {}, { preserveScroll: true })
        }
    }

    const superAdminCount = admins.data.filter((u) => u.role === 'superadmin').length
    const adminCount = admins.data.filter((u) => u.role === 'admin').length

    return (
        <AdminLayout title={t('admins.title')}>
            <p className="text-ava-muted mb-4">{t('admins.description')}</p>

            <div className="row g-3 mb-4">
                <div className="col-6 col-md-4">
                    <div className="stat-tile">
                        <p className="stat-tile-label mb-2">{t('admins.stats.total')}</p>
                        <p className="stat-tile-value mb-0">{admins.total}</p>
                    </div>
                </div>
                <div className="col-6 col-md-4">
                    <div className="stat-tile">
                        <p className="stat-tile-label mb-2">{t('admins.stats.superAdminsPage')}</p>
                        <p className="stat-tile-value mb-0">{superAdminCount}</p>
                    </div>
                </div>
                <div className="col-6 col-md-4">
                    <div className="stat-tile">
                        <p className="stat-tile-label mb-2">{t('admins.stats.adminsPage')}</p>
                        <p className="stat-tile-value mb-0">{adminCount}</p>
                    </div>
                </div>
            </div>

            <div className="mb-3">
                <input
                    className="form-control"
                    placeholder={t('search.byNameEmailMembership')}
                    value={search}
                    onChange={(e) => setSearch(e.target.value)}
                />
            </div>

            {admins.data.length === 0 ? (
                <EmptyState icon="bi-shield-check" message={t('admins.empty')} />
            ) : (
                <>
                    {/* Table view (md and up) */}
                    <div className="ava-card d-none d-md-block">
                        <div className="table-responsive">
                            <table className="table mb-0">
                                <thead>
                                    <tr>
                                        <th>{t('table.name')}</th>
                                        <th>{t('table.membership')}</th>
                                        <th>{t('table.stage')}</th>
                                        <th>{t('table.role')}</th>
                                        <th>{t('table.status')}</th>
                                        <th>{t('admins.table.granted')}</th>
                                        <th />
                                    </tr>
                                </thead>
                                <tbody>
                                    {admins.data.map((user) => (
                                        <tr
                                            key={user.id}
                                            role="button"
                                            className="cursor-pointer"
                                            onClick={() => router.visit(`/admin/users/${user.id}`)}
                                        >
                                            <td>
                                                <Link
                                                    href={`/admin/users/${user.id}`}
                                                    className="text-reset text-decoration-none"
                                                    onClick={(e) => e.stopPropagation()}
                                                >
                                                    <UserCell user={user} />
                                                </Link>
                                            </td>
                                            <td>
                                                <span className="code-chip text-uppercase">{user.membership_code}</span>
                                            </td>
                                            <td>{user.stage.name}</td>
                                            <td>
                                                <RoleChip role={user.role} />
                                            </td>
                                            <td>
                                                <StatusBadge status={user.is_active ? 'active' : 'revoked'} />
                                            </td>
                                            <td className="text-ava-muted text-tiny">{user.email_verified_at}</td>
                                            <td className="text-end" onClick={(e) => e.stopPropagation()}>
                                                <ActionMenu
                                                    items={[
                                                        {
                                                            label: t('actions.view'),
                                                            icon: 'bi-eye',
                                                            onClick: () => router.visit(`/admin/users/${user.id}`),
                                                        },
                                                        {
                                                            label: t('actions.toggleActive'),
                                                            icon: user.is_active ? 'bi-toggle2-on' : 'bi-toggle2-off',
                                                            onClick: () => toggle(user),
                                                        },
                                                        ...(auth.user?.can_manage_admin_access && user.role !== 'superadmin'
                                                            ? [
                                                                  {
                                                                      label: t('actions.revokeAdmin'),
                                                                      icon: 'bi-shield-x',
                                                                      danger: true,
                                                                      disabled: user.id === auth.user?.id,
                                                                      onClick: () => revokeAdminAccess(user),
                                                                  },
                                                              ]
                                                            : []),
                                                    ]}
                                                />
                                            </td>
                                        </tr>
                                    ))}
                                </tbody>
                            </table>
                        </div>
                    </div>

                    {/* Card view (below md) */}
                    <div className="d-md-none">
                        {admins.data.map((user) => (
                            <div
                                key={user.id}
                                className="record-card cursor-pointer"
                                role="button"
                                onClick={() => router.visit(`/admin/users/${user.id}`)}
                            >
                                <div className="d-flex align-items-center gap-3 mb-2">
                                    {user.avatar ? (
                                        <img src={user.avatar} alt={user.name} width={40} height={40} className="rounded-circle" />
                                    ) : (
                                        <AvatarInitials name={user.name} size={40} />
                                    )}
                                    <div className="min-w-0">
                                        <div className="fw-semibold text-truncate">{user.name}</div>
                                        <div className="text-ava-muted text-tiny text-truncate">{user.email}</div>
                                    </div>
                                </div>

                                <div className="record-card-row">
                                    <span className="record-card-label">{t('table.membership')}</span>
                                    <span className="code-chip text-uppercase">{user.membership_code}</span>
                                </div>
                                <div className="record-card-row">
                                    <span className="record-card-label">{t('table.stage')}</span>
                                    <span>{user.stage.name}</span>
                                </div>
                                <div className="record-card-row">
                                    <span className="record-card-label">{t('table.role')}</span>
                                    <RoleChip role={user.role} />
                                </div>
                                <div className="record-card-row">
                                    <span className="record-card-label">{t('table.status')}</span>
                                    <StatusBadge status={user.is_active ? 'active' : 'revoked'} />
                                </div>

                                <div className="record-card-actions" onClick={(e) => e.stopPropagation()}>
                                    <button
                                        type="button"
                                        className="btn btn-sm btn-outline-primary"
                                        onClick={() => router.visit(`/admin/users/${user.id}`)}
                                    >
                                        {t('actions.view')}
                                    </button>
                                    <button type="button" className="btn btn-sm btn-outline-primary" onClick={() => toggle(user)}>
                                        {t('actions.toggleActive')}
                                    </button>
                                    {auth.user?.can_manage_admin_access && user.role !== 'superadmin' && (
                                        <button
                                            type="button"
                                            className="btn btn-sm btn-outline-primary"
                                            disabled={user.id === auth.user?.id}
                                            onClick={() => revokeAdminAccess(user)}
                                        >
                                            {t('actions.revokeAdmin')}
                                        </button>
                                    )}
                                </div>
                            </div>
                        ))}
                    </div>
                </>
            )}

            <div className="mt-3">
                <Pagination pagination={admins} />
            </div>
        </AdminLayout>
    )
}
