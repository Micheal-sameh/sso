import { useState } from 'react'
import type { ChangeEvent, FormEvent } from 'react'
import { useForm } from '@inertiajs/react'
import AppLayout from '@/components/layouts/AppLayout'
import AvatarInitials from '@/components/shared/AvatarInitials'
import type { User } from '@/types'

interface ShowProps {
    user: User
}

interface ProfileForm {
    avatar: File | null
}

interface PasswordForm {
    current_password: string
    password: string
    password_confirmation: string
}

export default function Show({ user }: ShowProps) {
    const [preview, setPreview] = useState<string | null>(user.avatar)
    const form = useForm<ProfileForm>({ avatar: null })
    const passwordForm = useForm<PasswordForm>({
        current_password: '',
        password: '',
        password_confirmation: '',
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
        form.post('/account', { forceFormData: true })
    }

    const submitPassword = (e: FormEvent) => {
        e.preventDefault()
        passwordForm.post('/account/password', {
            preserveScroll: true,
            onSuccess: () => passwordForm.reset(),
        })
    }

    return (
        <AppLayout title="Profile">
            <div className="ava-card p-4 max-w-560 mb-4">
                <div className="d-flex align-items-center gap-3 mb-4">
                    {preview ? (
                        <img
                            src={preview}
                            alt={user.name}
                            width={80}
                            height={80}
                            className="rounded-circle object-fit-cover"
                        />
                    ) : (
                        <AvatarInitials name={user.name} size={80} />
                    )}
                    <div>
                        <input type="file" accept="image/*" className="form-control" onChange={onAvatarChange} />
                        {form.errors.avatar && <div className="text-danger small mt-1">{form.errors.avatar}</div>}
                    </div>
                </div>

                <form onSubmit={submit} className="mb-4">
                    <div className="mb-3">
                        <label className="form-label" htmlFor="name">Name</label>
                        <input id="name" className="form-control text-ava-muted" value={user.name} readOnly />
                    </div>

                    <div className="mb-3">
                        <label className="form-label" htmlFor="email">Email</label>
                        <input id="email" className="form-control text-ava-muted" value={user.email} readOnly />
                    </div>

                    <p className="text-ava-muted text-tiny mb-3">
                        Your profile details are managed by an administrator. Contact one to update your name, email, or other
                        account details.
                    </p>

                    <button type="submit" className="btn btn-primary" disabled={form.processing}>
                        Save Photo
                    </button>
                </form>
            </div>

            <div className="ava-card p-4 max-w-560">
                <h5 className="mb-3">Change Password</h5>
                <form onSubmit={submitPassword}>
                    <div className="mb-3">
                        <label className="form-label" htmlFor="current_password">Current Password</label>
                        <input
                            id="current_password"
                            type="password"
                            className="form-control"
                            value={passwordForm.data.current_password}
                            onChange={(e) => passwordForm.setData('current_password', e.target.value)}
                        />
                        {passwordForm.errors.current_password && (
                            <div className="text-danger small mt-1">{passwordForm.errors.current_password}</div>
                        )}
                    </div>

                    <div className="mb-3">
                        <label className="form-label" htmlFor="password">New Password</label>
                        <input
                            id="password"
                            type="password"
                            className="form-control"
                            value={passwordForm.data.password}
                            onChange={(e) => passwordForm.setData('password', e.target.value)}
                        />
                        {passwordForm.errors.password && (
                            <div className="text-danger small mt-1">{passwordForm.errors.password}</div>
                        )}
                    </div>

                    <div className="mb-4">
                        <label className="form-label" htmlFor="password_confirmation">Confirm New Password</label>
                        <input
                            id="password_confirmation"
                            type="password"
                            className="form-control"
                            value={passwordForm.data.password_confirmation}
                            onChange={(e) => passwordForm.setData('password_confirmation', e.target.value)}
                        />
                    </div>

                    <button type="submit" className="btn btn-primary" disabled={passwordForm.processing}>
                        Update Password
                    </button>
                </form>
            </div>
        </AppLayout>
    )
}
