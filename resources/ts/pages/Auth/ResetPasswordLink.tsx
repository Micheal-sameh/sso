import type { FormEvent } from 'react'
import { useForm } from '@inertiajs/react'
import { useTranslation } from 'react-i18next'
import AuthLayout from '@/components/layouts/AuthLayout'
import AvaLogo from '@/components/shared/AvaLogo'

interface ResetPasswordLinkProps {
    status: 'form' | 'success' | 'invalid'
    token?: string
}

interface ResetPasswordLinkForm {
    password: string
    password_confirmation: string
}

export default function ResetPasswordLink({ status, token }: ResetPasswordLinkProps) {
    const { t } = useTranslation(['auth', 'common'])
    const form = useForm<ResetPasswordLinkForm>({ password: '', password_confirmation: '' })

    const submit = (e: FormEvent) => {
        e.preventDefault()
        form.post(`/reset-password/link/${token}`)
    }

    if (status === 'invalid') {
        return (
            <AuthLayout>
                <div className="text-center mb-4">
                    <AvaLogo size={56} />
                </div>
                <p className="text-center text-ava-muted mb-0">
                    <i className="bi bi-exclamation-circle text-danger me-1" />
                    {t('common:avatarUpload.linkExpired')}
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
                    {t('resetPassword.success')}
                </p>
            </AuthLayout>
        )
    }

    return (
        <AuthLayout>
            <div className="text-center mb-4">
                <AvaLogo size={56} />
                <p className="text-ava-muted small mt-2 mb-0">{t('resetPassword.chooseNewPassword')}</p>
            </div>

            <form onSubmit={submit}>
                <div className="mb-3">
                    <label className="form-label" htmlFor="password">{t('resetPassword.newPassword')}</label>
                    <input
                        id="password"
                        type="password"
                        className="form-control"
                        value={form.data.password}
                        onChange={(e) => form.setData('password', e.target.value)}
                        autoFocus
                    />
                    {form.errors.password && <div className="text-danger small mt-1">{form.errors.password}</div>}
                </div>

                <div className="mb-4">
                    <label className="form-label" htmlFor="password_confirmation">{t('resetPassword.confirmNewPassword')}</label>
                    <input
                        id="password_confirmation"
                        type="password"
                        className="form-control"
                        value={form.data.password_confirmation}
                        onChange={(e) => form.setData('password_confirmation', e.target.value)}
                    />
                </div>

                <button type="submit" className="btn btn-primary w-100" disabled={form.processing}>
                    {t('resetPassword.resetPassword')}
                </button>
            </form>
        </AuthLayout>
    )
}
