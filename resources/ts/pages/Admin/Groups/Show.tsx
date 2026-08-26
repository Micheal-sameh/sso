import { useState } from 'react'
import { router } from '@inertiajs/react'
import { useTranslation } from 'react-i18next'
import AdminLayout from '@/components/layouts/AdminLayout'
import type { Group, GroupMemberOption } from '@/types'

interface ShowProps {
    group: Group
    users: GroupMemberOption[]
}

export default function Show({ group, users }: ShowProps) {
    const { t } = useTranslation(['admin', 'common'])
    const [search, setSearch] = useState('')

    const toggleMembership = (user: GroupMemberOption) => {
        if (user.is_member) {
            router.delete(`/admin/users/${user.id}/groups/${group.id}`, { preserveScroll: true })
        } else {
            router.post(`/admin/users/${user.id}/groups/${group.id}`, {}, { preserveScroll: true })
        }
    }

    const filteredUsers = users.filter(
        (u) =>
            u.name.toLowerCase().includes(search.toLowerCase()) ||
            (u.email ?? '').toLowerCase().includes(search.toLowerCase()) ||
            u.membership_code.toLowerCase().includes(search.toLowerCase()),
    )

    const memberCount = users.filter((u) => u.is_member).length

    return (
        <AdminLayout
            title={group.name}
            action={
                <button type="button" className="btn btn-outline-primary btn-sm" onClick={() => router.visit('/admin/groups')}>
                    <i className="bi bi-arrow-left me-1" />
                    {t('groupShow.backToGroups')}
                </button>
            }
        >
            <div className="ava-card mb-4">
                <div className="card-accent card-accent-primary" />
                <div className="p-4">
                    <h3 className="mb-1">{group.name}</h3>
                    {group.description && <p className="text-ava-muted mb-2">{group.description}</p>}
                    <span className="role-chip">{t('groupShow.memberCount', { count: memberCount })}</span>
                </div>
            </div>

            <div className="d-flex justify-content-between align-items-center mb-3">
                <h5 className="mb-0">{t('table.users')}</h5>
            </div>

            <input
                type="text"
                className="form-control mb-3"
                placeholder={t('groupShow.searchPlaceholder')}
                value={search}
                onChange={(e) => setSearch(e.target.value)}
            />

            <div className="ava-card">
                <ul className="list-group list-group-flush">
                    {filteredUsers.map((user) => (
                        <li
                            key={user.id}
                            className="list-group-item d-flex justify-content-between align-items-center p-3 gap-3"
                        >
                            <div className="min-w-0">
                                <div className="fw-semibold">{user.name}</div>
                                <div className="text-ava-muted text-tiny">
                                    {user.email} &middot; <span className="text-uppercase">{user.membership_code}</span>
                                </div>
                            </div>
                            <button
                                type="button"
                                className={`btn btn-sm text-nowrap ${user.is_member ? 'btn-outline-primary' : 'btn-primary'}`}
                                onClick={() => toggleMembership(user)}
                            >
                                {user.is_member ? t('actions.remove') : t('actions.add')}
                            </button>
                        </li>
                    ))}
                    {filteredUsers.length === 0 && (
                        <li className="list-group-item text-center text-ava-muted py-4">{t('search.noUsersMatch')}</li>
                    )}
                </ul>
            </div>
        </AdminLayout>
    )
}
