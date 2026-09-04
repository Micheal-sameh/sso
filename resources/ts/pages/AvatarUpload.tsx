import { useState } from 'react'
import type { ChangeEvent, FormEvent } from 'react'
import { router, useForm } from '@inertiajs/react'
import { useTranslation } from 'react-i18next'
import AuthLayout from '@/components/layouts/AuthLayout'
import AvaLogo from '@/components/shared/AvaLogo'
import AvatarInitials from '@/components/shared/AvatarInitials'

interface AvatarUploadProps {
    status: 'form' | 'success' | 'invalid'
    userName?: string
    avatar?: string | null
    token?: string
    action?: 'uploaded' | 'removed'
}

interface AvatarUploadForm {
    avatar: File | null
}

export default function AvatarUpload({ status, userName, avatar, token, action }: AvatarUploadProps) {
    const { t } = useTranslation('common')
    const [preview, setPreview] = useState<string | null>(avatar ?? null)
    const form = useForm<AvatarUploadForm>({ avatar: null })

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
        form.post(`/avatar-upload/${token}`, { forceFormData: true })
    }

    const removePhoto = () => {
        router.delete(`/avatar-upload/${token}`)
    }

    if (status === 'invalid') {
        return (
            <AuthLayout>
                <div className="text-center mb-4">
                    <AvaLogo size={56} />
                </div>
                <p className="text-center text-ava-muted mb-0">
                    <i className="bi bi-exclamation-circle text-danger me-1" />
                    {t('avatarUpload.linkExpired')}
                </p>
            </AuthLayout>
        )
    }

    if (status === 'success') {
        return (
            <AuthLayout>
                <div className="text-center mb-4">
                    <AvaLogo size={56} />
                </div>
                <p className="text-center text-ava-muted mb-0">
                    <i className="bi bi-check-circle text-success me-1" />
                    {t(action === 'removed' ? 'avatarUpload.removeSuccessMessage' : 'avatarUpload.successMessage', { userName })}
                </p>
            </AuthLayout>
        )
    }

    return (
        <AuthLayout>
            <div className="text-center mb-4">
                <AvaLogo size={56} />
                <p className="text-ava-muted small mt-2 mb-0">{t('avatarUpload.updateProfilePicture', { userName })}</p>
            </div>

            <div className="text-center mb-4">
                {preview ? (
                    <img
                        src={preview}
                        alt={userName}
                        width={96}
                        height={96}
                        className="rounded-circle object-fit-cover"
                    />
                ) : (
                    <div className="d-inline-flex">
                        <AvatarInitials name={userName ?? '?'} size={96} />
                    </div>
                )}
            </div>

            <form onSubmit={submit}>
                <div className="mb-4">
                    <label className="form-label" htmlFor="avatar">{t('avatarUpload.choosePhoto')}</label>
                    <input
                        id="avatar"
                        type="file"
                        accept="image/*"
                        className="form-control"
                        onChange={onAvatarChange}
                    />
                    {form.errors.avatar && <div className="text-danger small mt-1">{form.errors.avatar}</div>}
                </div>

                <button type="submit" className="btn btn-primary w-100" disabled={form.processing || !form.data.avatar}>
                    {t('avatarUpload.uploadPhoto')}
                </button>
            </form>

            {avatar && (
                <button
                    type="button"
                    className="btn btn-link text-danger w-100 mt-2"
                    onClick={removePhoto}
                    disabled={form.processing}
                >
                    {t('avatarUpload.removePhoto')}
                </button>
            )}

            <p className="text-center text-ava-muted text-tiny mt-4 mb-0">
                {t('avatarUpload.poweredBy')}
            </p>
        </AuthLayout>
    )
}
