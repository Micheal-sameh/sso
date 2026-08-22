import { useForm } from '@inertiajs/react'
import type { FormEvent } from 'react'
import { useTranslation } from 'react-i18next'
import AuthLayout from '@/components/layouts/AuthLayout'
import AvaLogo from '@/components/shared/AvaLogo'

interface ForgotPasswordForm {
    email: string
}

export default function ForgotPassword() {
    const { t } = useTranslation('auth')
    const form = useForm<ForgotPasswordForm>({ email: '' })

    const submit = (e: FormEvent) => {
        e.preventDefault()
        form.post('/forgot-password')
    }

    return (
        <AuthLayout>
            <div className="text-center mb-4">
                <AvaLogo size={56} showText />
                <p className="text-ava-muted small mt-2 mb-0">{t('forgotPassword.resetYourPassword')}</p>
            </div>

            <form onSubmit={submit}>
                <div className="mb-3">
                    <label className="form-label" htmlFor="email">{t('forgotPassword.email')}</label>
                    <input
                        id="email"
                        type="email"
                        className="form-control"
                        value={form.data.email}
                        onChange={(e) => form.setData('email', e.target.value)}
                        autoFocus
                    />
                    {form.errors.email && <div className="text-danger small mt-1">{form.errors.email}</div>}
                </div>

                <button type="submit" className="btn btn-primary w-100" disabled={form.processing}>
                    {t('forgotPassword.sendResetLink')}
                </button>

                <div className="text-center mt-3">
                    <a href="/login" className="text-ava-secondary text-decoration-none small">{t('forgotPassword.backToSignIn')}</a>
                </div>
            </form>

            <p className="text-center text-ava-muted text-tiny mt-4 mb-0">
                {t('forgotPassword.poweredBy')}
            </p>
        </AuthLayout>
    )
}
