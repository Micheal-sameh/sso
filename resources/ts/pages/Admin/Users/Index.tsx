import { useEffect, useState } from 'react'
import { router, usePage } from '@inertiajs/react'
import AdminLayout from '@/components/layouts/AdminLayout'
import Pagination from '@/components/shared/Pagination'
import StatusBadge from '@/components/shared/StatusBadge'
import type { PaginatedData, SharedProps, User } from '@/types'

interface IndexProps {
    users: PaginatedData<User>
    filters: { search?: string; role?: string }
}

export default function Index({ users, filters }: IndexProps) {
    const [search, setSearch] = useState(filters.search ?? '')
    const [role, setRole] = useState(filters.role ?? '')
    const { auth } = usePage<SharedProps>().props

    useEffect(() => {
        const timer = setTimeout(() => {
            router.get('/admin/users', { search, role }, { preserveState: true, replace: true })
        }, 300)

        return () => clearTimeout(timer)
        // eslint-disable-next-line react-hooks/exhaustive-deps
    }, [search, role])

    const toggle = (user: User) => {
        router.patch(`/admin/users/${user.id}/toggle`, {}, { preserveScroll: true })
    }

    const toggleAdminAccess = (user: User) => {
        const action = user.is_admin ? 'revoke' : 'grant'

        if (confirm(`Are you sure you want to ${action} SSO admin access for ${user.name}?`)) {
            router.patch(`/admin/users/${user.id}/toggle-admin-access`, {}, { preserveScroll: true })
        }
    }

    return (
        <AdminLayout
            title="Users"
            action={
                <button type="button" className="btn btn-primary" onClick={() => router.visit('/admin/users/create')}>
                    Create User
                </button>
            }
        >
            <div className="d-flex gap-2 mb-3">
                <input
                    className="form-control"
                    placeholder="Search by name, email, or membership code"
                    value={search}
                    onChange={(e) => setSearch(e.target.value)}
                />
                <select className="form-select max-w-200" value={role} onChange={(e) => setRole(e.target.value)}>
                    <option value="">All roles</option>
                    <option value="superadmin">Super Admin</option>
                    <option value="user">User</option>
                </select>
            </div>

            <div className="ava-card">
                <table className="table mb-0">
                    <thead>
                        <tr>
                            <th />
                            <th>Name</th>
                            <th>Email</th>
                            <th>Membership Code</th>
                            <th>Role</th>
                            <th>Status</th>
                            <th>SSO Admin Access</th>
                            <th>Created</th>
                            <th />
                        </tr>
                    </thead>
                    <tbody>
                        {users.data.map((user) => (
                            <tr key={user.id}>
                                <td>
                                    <img
                                        src={user.avatar ?? undefined}
                                        alt={user.name}
                                        width={32}
                                        height={32}
                                        className="rounded-circle"
                                    />
                                </td>
                                <td>{user.name}</td>
                                <td>{user.email}</td>
                                <td>
                                    <code>{user.membership_code}</code>
                                </td>
                                <td>
                                    <span className="badge bg-secondary-subtle text-ava-primary">{user.role}</span>
                                </td>
                                <td>
                                    <StatusBadge status={user.is_active ? 'active' : 'revoked'} />
                                </td>
                                <td>
                                    <StatusBadge status={user.is_admin ? 'enabled' : 'disabled'} />
                                </td>
                                <td>{user.email_verified_at}</td>
                                <td className="text-nowrap">
                                    <button type="button" className="btn btn-sm btn-outline-primary me-2" onClick={() => toggle(user)}>
                                        Toggle Active
                                    </button>
                                    <button
                                        type="button"
                                        className="btn btn-sm btn-outline-primary"
                                        disabled={user.id === auth.user?.id}
                                        title={user.id === auth.user?.id ? "You can't change your own admin access" : undefined}
                                        onClick={() => toggleAdminAccess(user)}
                                    >
                                        {user.is_admin ? 'Revoke Admin' : 'Grant Admin'}
                                    </button>
                                </td>
                            </tr>
                        ))}
                    </tbody>
                </table>
            </div>

            <div className="mt-3">
                <Pagination links={users.links} />
            </div>
        </AdminLayout>
    )
}
