import type { FormEvent } from 'react'
import { router, useForm, usePage } from '@inertiajs/react'
import AdminLayout from '@/components/layouts/AdminLayout'
import type { SharedProps, Stage } from '@/types'

interface CreateProps {
    stages: Stage[]
}

interface CreateForm {
    name: string
    email: string
    phone: string
    date_of_birth: string
    membership_code: string
    password: string
    password_confirmation: string
    role: string
    stage_id: string
}

export default function Create({ stages }: CreateProps) {
    const { auth } = usePage<SharedProps>().props
    const canManageAdminAccess = auth.user?.can_manage_admin_access ?? false
    const form = useForm<CreateForm>({
        name: '',
        email: '',
        phone: '',
        date_of_birth: '',
        membership_code: '',
        password: '',
        password_confirmation: '',
        role: 'user',
        stage_id: stages[0] ? String(stages[0].id) : '',
    })

    const submit = (e: FormEvent) => {
        e.preventDefault()
        form.post('/admin/users')
    }

    return (
        <AdminLayout title="Create User">
            <div className="ava-card p-4 max-w-560">
                <form onSubmit={submit}>
                    <div className="mb-3">
                        <label className="form-label" htmlFor="name">Name</label>
                        <input
                            id="name"
                            className="form-control"
                            value={form.data.name}
                            onChange={(e) => form.setData('name', e.target.value)}
                        />
                        {form.errors.name && <div className="text-danger small mt-1">{form.errors.name}</div>}
                    </div>

                    <div className="mb-3">
                        <label className="form-label" htmlFor="email">Email</label>
                        <input
                            id="email"
                            type="email"
                            className="form-control"
                            value={form.data.email}
                            onChange={(e) => form.setData('email', e.target.value)}
                        />
                        {form.errors.email && <div className="text-danger small mt-1">{form.errors.email}</div>}
                    </div>

                    <div className="mb-3">
                        <label className="form-label" htmlFor="phone">Phone</label>
                        <input
                            id="phone"
                            type="tel"
                            className="form-control"
                            value={form.data.phone}
                            onChange={(e) => form.setData('phone', e.target.value)}
                        />
                        {form.errors.phone && <div className="text-danger small mt-1">{form.errors.phone}</div>}
                    </div>

                    <div className="mb-3">
                        <label className="form-label" htmlFor="date_of_birth">Date of Birth</label>
                        <input
                            id="date_of_birth"
                            type="date"
                            className="form-control"
                            value={form.data.date_of_birth}
                            onChange={(e) => form.setData('date_of_birth', e.target.value)}
                        />
                        {form.errors.date_of_birth && (
                            <div className="text-danger small mt-1">{form.errors.date_of_birth}</div>
                        )}
                    </div>

                    <div className="mb-3">
                        <label className="form-label" htmlFor="membership_code">Membership Code</label>
                        <input
                            id="membership_code"
                            className="form-control text-uppercase"
                            value={form.data.membership_code}
                            onChange={(e) => form.setData('membership_code', e.target.value)}
                        />
                        {form.errors.membership_code && (
                            <div className="text-danger small mt-1">{form.errors.membership_code}</div>
                        )}
                    </div>

                    <div className="mb-3">
                        <label className="form-label" htmlFor="password">Password</label>
                        <input
                            id="password"
                            type="password"
                            className="form-control"
                            value={form.data.password}
                            onChange={(e) => form.setData('password', e.target.value)}
                        />
                        {form.errors.password && <div className="text-danger small mt-1">{form.errors.password}</div>}
                    </div>

                    <div className="mb-3">
                        <label className="form-label" htmlFor="password_confirmation">Confirm Password</label>
                        <input
                            id="password_confirmation"
                            type="password"
                            className="form-control"
                            value={form.data.password_confirmation}
                            onChange={(e) => form.setData('password_confirmation', e.target.value)}
                        />
                    </div>

                    <div className="mb-3">
                        <label className="form-label" htmlFor="stage_id">Stage</label>
                        <select
                            id="stage_id"
                            className="form-select"
                            value={form.data.stage_id}
                            onChange={(e) => form.setData('stage_id', e.target.value)}
                        >
                            {stages.map((stage) => (
                                <option key={stage.id} value={stage.id}>
                                    {stage.name}
                                </option>
                            ))}
                        </select>
                        {form.errors.stage_id && <div className="text-danger small mt-1">{form.errors.stage_id}</div>}
                    </div>

                    <div className="mb-4">
                        <label className="form-label" htmlFor="role">Role</label>
                        <select
                            id="role"
                            className="form-select"
                            value={form.data.role}
                            onChange={(e) => form.setData('role', e.target.value)}
                        >
                            <option value="user">User</option>
                            {canManageAdminAccess && (
                                <>
                                    <option value="admin">Admin</option>
                                    <option value="superadmin">Super Admin</option>
                                </>
                            )}
                        </select>
                        <p className="text-ava-muted text-tiny mt-1 mb-0">
                            {canManageAdminAccess
                                ? 'Admin and Super Admin roles are granted SSO admin panel access immediately.'
                                : 'Only a super admin can assign the Admin or Super Admin role.'}
                        </p>
                        {form.errors.role && <div className="text-danger small mt-1">{form.errors.role}</div>}
                    </div>

                    <div className="d-flex gap-2">
                        <button type="submit" className="btn btn-primary" disabled={form.processing}>
                            Create User
                        </button>
                        <button
                            type="button"
                            className="btn btn-outline-primary"
                            onClick={() => router.visit('/admin/users')}
                        >
                            Cancel
                        </button>
                    </div>
                </form>
            </div>
        </AdminLayout>
    )
}
