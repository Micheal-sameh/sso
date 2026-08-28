import { useEffect, useState } from 'react'
import { router, usePage } from '@inertiajs/react'
import { useTranslation } from 'react-i18next'
import AdminLayout from '@/components/layouts/AdminLayout'
import AvatarInitials from '@/components/shared/AvatarInitials'
import { useConfirm } from '@/components/shared/ConfirmDialog'
import Pagination from '@/components/shared/Pagination'
import RoleChip from '@/components/shared/RoleChip'
import StatusBadge from '@/components/shared/StatusBadge'
import type { PaginatedData, SharedProps, Stage, User } from '@/types'

interface IndexProps {
    users: PaginatedData<User>
    stages: Stage[]
    filters: { search?: string; role?: string; stage_id?: string }
}

export default function Index({ users, stages, filters }: IndexProps) {
    const { t } = useTranslation('admin')
    const [search, setSearch] = useState(filters.search ?? '')
    const [role, setRole] = useState(filters.role ?? '')
    const [stageId, setStageId] = useState(filters.stage_id ?? '')
    const { auth } = usePage<SharedProps>().props
    const confirm = useConfirm()

    useEffect(() => {
        const timer = setTimeout(() => {
            router.get('/admin/users', { search, role, stage_id: stageId }, { preserveState: true, replace: true })
        }, 300)

        return () => clearTimeout(timer)
        // eslint-disable-next-line react-hooks/exhaustive-deps
    }, [search, role, stageId])

    const toggle = (user: User) => {
        router.patch(`/admin/users/${user.id}/toggle`, {}, { preserveScroll: true })
    }

    const toggleAdminAccess = async (user: User) => {
        const message = user.is_admin
            ? t('users.confirmRevokeAdminAccess', { name: user.name })
            : t('users.confirmGrantAdminAccess', { name: user.name })

        if (await confirm(message, { confirmLabel: user.is_admin ? t('actions.revoke') : t('actions.grant') })) {
            router.patch(`/admin/users/${user.id}/toggle-admin-access`, {}, { preserveScroll: true })
        }
    }

    const activeCount = users.data.filter((u) => u.is_active).length
    const superAdminCount = users.data.filter((u) => u.role === 'superadmin').length
    const adminCount = users.data.filter((u) => u.role === 'admin').length

    return (
        <AdminLayout
            title={t('users.indexTitle')}
            action={
                <button type="button" className="btn btn-primary btn-sm" onClick={() => router.visit('/admin/users/create')}>
                    <i className="bi bi-plus-lg" />
                    <span className="d-none d-sm-inline ms-1">{t('users.createUser')}</span>
                </button>
            }
        >
            <div className="row g-3 mb-4">
                <div className="col-6 col-md-3">
                    <div className="stat-tile">
                        <p className="stat-tile-label mb-2">{t('users.stats.totalUsers')}</p>
                        <p className="stat-tile-value mb-0">{users.total}</p>
                    </div>
                </div>
                <div className="col-6 col-md-3">
                    <div className="stat-tile">
                        <p className="stat-tile-label mb-2">{t('users.stats.activePage')}</p>
                        <p className="stat-tile-value mb-0">{activeCount}</p>
                    </div>
                </div>
                <div className="col-6 col-md-3">
                    <div className="stat-tile">
                        <p className="stat-tile-label mb-2">{t('users.stats.superAdminsPage')}</p>
                        <p className="stat-tile-value mb-0">{superAdminCount}</p>
                    </div>
                </div>
                <div className="col-6 col-md-3">
                    <div className="stat-tile">
                        <p className="stat-tile-label mb-2">{t('users.stats.adminsPage')}</p>
                        <p className="stat-tile-value mb-0">{adminCount}</p>
                    </div>
                </div>
            </div>

            <div className="d-flex flex-column flex-sm-row gap-2 mb-3">
                <input
                    className="form-control"
                    placeholder={t('search.byNameEmailMembership')}
                    value={search}
                    onChange={(e) => setSearch(e.target.value)}
                />
                <select className="form-select max-w-200-sm" value={role} onChange={(e) => setRole(e.target.value)}>
                    <option value="">{t('users.allRoles')}</option>
                    <option value="superadmin">{t('roles.superadmin')}</option>
                    <option value="admin">{t('roles.admin')}</option>
                    <option value="secretary">{t('roles.secretary')}</option>
                    <option value="user">{t('roles.user')}</option>
                </select>
                <select className="form-select max-w-200-sm" value={stageId} onChange={(e) => setStageId(e.target.value)}>
                    <option value="">{t('users.allStages')}</option>
                    {stages.map((stage) => (
                        <option key={stage.id} value={stage.id}>
                            {stage.name}
                        </option>
                    ))}
                </select>
            </div>

            {/* Table view (md and up) */}
            <div className="ava-card d-none d-md-block">
                <div className="table-responsive">
                    <table className="table mb-0">
                        <thead>
                            <tr>
                                <th />
                                <th>{t('table.name')}</th>
                                <th>{t('table.email')}</th>
                                <th>{t('table.membership')}</th>
                                <th>{t('table.stage')}</th>
                                <th>{t('table.role')}</th>
                                <th>{t('table.status')}</th>
                                <th>{t('users.table.adminAccess')}</th>
                                <th />
                            </tr>
                        </thead>
                        <tbody>
                            {users.data.map((user) => (
                                <tr
                                    key={user.id}
                                    role="button"
                                    className="cursor-pointer"
                                    onClick={() => router.visit(`/admin/users/${user.id}`)}
                                >
                                    <td>
                                        {user.avatar ? (
                                            <img
                                                src={user.avatar}
                                                alt={user.name}
                                                width={32}
                                                height={32}
                                                className="rounded-circle"
                                            />
                                        ) : (
                                            <AvatarInitials name={user.name} />
                                        )}
                                    </td>
                                    <td className="fw-semibold">{user.name}</td>
                                    <td className="text-ava-muted">{user.email}</td>
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
                                    <td>
                                        <StatusBadge status={user.is_admin ? 'enabled' : 'disabled'} />
                                    </td>
                                    <td className="text-nowrap" onClick={(e) => e.stopPropagation()}>
                                        <button
                                            type="button"
                                            className="btn btn-sm btn-outline-primary me-2"
                                            title={t('actions.editUser')}
                                            onClick={() => router.visit(`/admin/users/${user.id}/edit`)}
                                        >
                                            <i className="bi bi-pencil" />
                                        </button>
                                        <button type="button" className="btn btn-sm btn-outline-primary me-2" onClick={() => toggle(user)}>
                                            {t('actions.toggleActive')}
                                        </button>
                                        {auth.user?.can_manage_admin_access && (
                                            <button
                                                type="button"
                                                className="btn btn-sm btn-outline-primary"
                                                disabled={user.id === auth.user?.id}
                                                title={user.id === auth.user?.id ? t('users.cantChangeOwnAdminAccess') : undefined}
                                                onClick={() => toggleAdminAccess(user)}
                                            >
                                                {user.is_admin ? t('actions.revokeAdmin') : t('actions.grantAdmin')}
                                            </button>
                                        )}
                                    </td>
                                </tr>
                            ))}
                        </tbody>
                    </table>
                </div>
            </div>

            {/* Card view (below md) */}
            <div className="d-md-none">
                {users.data.map((user) => (
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
                        <div className="record-card-row">
                            <span className="record-card-label">{t('users.table.adminAccess')}</span>
                            <StatusBadge status={user.is_admin ? 'enabled' : 'disabled'} />
                        </div>

                        <div className="record-card-actions" onClick={(e) => e.stopPropagation()}>
                            <button
                                type="button"
                                className="btn btn-sm btn-outline-primary"
                                title={t('actions.editUser')}
                                onClick={() => router.visit(`/admin/users/${user.id}/edit`)}
                            >
                                <i className="bi bi-pencil" />
                            </button>
                            <button type="button" className="btn btn-sm btn-outline-primary" onClick={() => toggle(user)}>
                                {t('actions.toggleActive')}
                            </button>
                            {auth.user?.can_manage_admin_access && (
                                <button
                                    type="button"
                                    className="btn btn-sm btn-outline-primary"
                                    disabled={user.id === auth.user?.id}
                                    onClick={() => toggleAdminAccess(user)}
                                >
                                    {user.is_admin ? t('actions.revokeAdmin') : t('actions.grantAdmin')}
                                </button>
                            )}
                        </div>
                    </div>
                ))}
            </div>

            <div className="mt-3">
                <Pagination pagination={users} />
            </div>
        </AdminLayout>
    )
}
