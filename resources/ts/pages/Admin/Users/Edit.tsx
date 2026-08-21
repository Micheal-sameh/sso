import { useState } from 'react'
import type { ChangeEvent, FormEvent } from 'react'
import { router, useForm, usePage } from '@inertiajs/react'
import AdminLayout from '@/components/layouts/AdminLayout'
import AvatarInitials from '@/components/shared/AvatarInitials'
import type { SharedProps, Stage, User } from '@/types'

interface EditProps {
    viewedUser: User
    stages: Stage[]
}

interface EditForm {
    name: string
    email: string
    phone: string
    date_of_birth: string
    role: string
    stage_id: string
    avatar: File | null
}

export default function Edit({ viewedUser, stages }: EditProps) {
    const { auth } = usePage<SharedProps>().props
    const canManageAdminAccess = auth.user?.can_manage_admin_access ?? false
    const roleLocked = !canManageAdminAccess && viewedUser.role !== 'user'
    const [preview, setPreview] = useState<string | null>(viewedUser.avatar)
    const form = useForm<EditForm>({
        name: viewedUser.name,
        email: viewedUser.email,
        phone: viewedUser.phone ?? '',
        date_of_birth: viewedUser.date_of_birth ?? '',
        role: viewedUser.role,
        stage_id: String(viewedUser.stage.id),
        avatar: null,
    })

    const onAvatarChange = (e: ChangeEvent<HTMLInputElement>) => {
        const file = e.target.files?.[0] ?? null
        form.setData('avatar', file)

        if (file) {
            const reader = new FileReader()
            reader.onload = () => setPreview(reader.result as string)
            reader.readAsDataURL(file)
        }
    }

    const submit = (e: FormEvent) => {
        e.preventDefault()
        // PHP never parses multipart bodies on a genuine PUT request (only POST), and
        // Inertia's usual auto-fix for this only triggers when a File is actually present
        // in the payload. Since avatar is optional here, force POST + _method spoofing
        // ourselves so the request parses correctly whether or not a photo was chosen.
        form.transform((data) => ({ ...data, _method: 'put' }))
        form.post(`/admin/users/${viewedUser.id}`, { forceFormData: true })
    }

    return (
        <AdminLayout title="Edit User">
            <div className="ava-card p-4 max-w-560">
                <div className="d-flex align-items-center gap-3 mb-4">
                    {preview ? (
                        <img
                            src={preview}
                            alt={viewedUser.name}
                            width={80}
                            height={80}
                            className="rounded-circle object-fit-cover"
                        />
                    ) : (
                        <AvatarInitials name={viewedUser.name} size={80} />
                    )}
                    <div>
                        <input type="file" accept="image/*" className="form-control" onChange={onAvatarChange} />
                        {form.errors.avatar && <div className="text-danger small mt-1">{form.errors.avatar}</div>}
                    </div>
                </div>

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
                            className="form-control text-ava-muted text-uppercase"
                            value={viewedUser.membership_code}
                            readOnly
                        />
                        <p className="text-ava-muted text-tiny mt-1 mb-0">
                            The membership code is permanent and cannot be changed once a user is created.
                        </p>
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
                            disabled={roleLocked}
                            onChange={(e) => form.setData('role', e.target.value)}
                        >
                            <option value="user">User</option>
                            {(canManageAdminAccess || viewedUser.role === 'secretary') && (
                                <option value="secretary">Secretary</option>
                            )}
                            {(canManageAdminAccess || viewedUser.role === 'admin') && <option value="admin">Admin</option>}
                            {(canManageAdminAccess || viewedUser.role === 'superadmin') && (
                                <option value="superadmin">Super Admin</option>
                            )}
                        </select>
                        <p className="text-ava-muted text-tiny mt-1 mb-0">
                            {roleLocked
                                ? 'Only a super admin can change the Admin or Super Admin role.'
                                : 'Admin and Super Admin roles are granted SSO admin panel access immediately.'}
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
