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
    name: string
    email: string
    avatar: File | null
}

export default function Show({ user }: ShowProps) {
    const [preview, setPreview] = useState<string | null>(user.avatar)
    const form = useForm<ProfileForm>({
        name: user.name,
        email: user.email,
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
        form.post('/account', { forceFormData: true })
    }

    return (
        <AppLayout title="Profile">
            <div className="ava-card p-4 max-w-560">
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

                    <div className="mb-4">
                        <label className="form-label" htmlFor="email">Email</label>
                        <input id="email" className="form-control text-ava-muted" value={form.data.email} readOnly />
                    </div>

                    <button type="submit" className="btn btn-primary" disabled={form.processing}>
                        Save Changes
                    </button>
                </form>
            </div>
        </AppLayout>
    )
}
