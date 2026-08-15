import type { FormEvent } from 'react'
import { router, useForm } from '@inertiajs/react'
import AdminLayout from '@/components/layouts/AdminLayout'
import type { Stage, User } from '@/types'

interface EditProps {
    viewedUser: User
    stages: Stage[]
}

interface EditForm {
    name: string
    email: string
    membership_code: string
    role: string
    stage_id: string
}

export default function Edit({ viewedUser, stages }: EditProps) {
    const form = useForm<EditForm>({
        name: viewedUser.name,
        email: viewedUser.email,
        membership_code: viewedUser.membership_code,
        role: viewedUser.role,
        stage_id: String(viewedUser.stage.id),
    })

    const submit = (e: FormEvent) => {
        e.preventDefault()
        form.put(`/admin/users/${viewedUser.id}`)
    }

    return (
        <AdminLayout title="Edit User">
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
                        <label className="form-label" htmlFor="membership_code">Membership Code</label>
                        <input
                            id="membership_code"
                            className="form-control"
                            value={form.data.membership_code}
                            onChange={(e) => form.setData('membership_code', e.target.value)}
                        />
                        {form.errors.membership_code && (
                            <div className="text-danger small mt-1">{form.errors.membership_code}</div>
                        )}
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
                            <option value="admin">Admin</option>
                            <option value="superadmin">Super Admin</option>
                        </select>
                        <p className="text-ava-muted text-tiny mt-1 mb-0">
                            Admin and Super Admin roles are granted SSO admin panel access immediately.
                        </p>
                        {form.errors.role && <div className="text-danger small mt-1">{form.errors.role}</div>}
                    </div>

                    <div className="d-flex gap-2">
                        <button type="submit" className="btn btn-primary" disabled={form.processing}>
                            Save Changes
                        </button>
                        <button
                            type="button"
                            className="btn btn-outline-primary"
                            onClick={() => router.visit(`/admin/users/${viewedUser.id}`)}
                        >
                            Cancel
                        </button>
                    </div>
                </form>
            </div>
        </AdminLayout>
    )
}
