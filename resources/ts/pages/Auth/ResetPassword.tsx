import { useForm, usePage } from '@inertiajs/react'
import { useRef } from 'react'
import type { FormEvent } from 'react'
import { useTranslation } from 'react-i18next'
import AuthLayout from '@/components/layouts/AuthLayout'
import AvaLogo from '@/components/shared/AvaLogo'
import Turnstile from '@/components/shared/Turnstile'
import type { TurnstileHandle } from '@/components/shared/Turnstile'
import type { SharedProps } from '@/types'

interface ResetPasswordProps {
    token: string
    email: string
}

interface ResetPasswordForm {
    token: string
    email: string
    password: string
    password_confirmation: string
    'cf-turnstile-response': string
}

export default function ResetPassword({ token, email }: ResetPasswordProps) {
    const { t } = useTranslation('auth')
    const { turnstile_site_key: turnstileSiteKey } = usePage<SharedProps>().props
    const turnstileRef = useRef<TurnstileHandle>(null)
    const form = useForm<ResetPasswordForm>({
        token,
        email,
        password: '',
        password_confirmation: '',
        'cf-turnstile-response': '',
    })

    const submit = (e: FormEvent) => {
        e.preventDefault()
        form.post('/reset-password', {
            onFinish: () => {
                // A Turnstile token is single-use: the server already consumed
                // it on this attempt (even one that failed for another reason,
                // like an expired reset link), so a retry needs a fresh one.
                turnstileRef.current?.reset()
                form.setData('cf-turnstile-response', '')
            },
        })
    }

    return (
        <AuthLayout>
            <div className="text-center mb-4">
                <AvaLogo size={56} />
                <p className="text-ava-muted small mt-2 mb-0">{t('resetPassword.chooseNewPassword')}</p>
            </div>

            <form onSubmit={submit}>
                <div className="mb-3">
                    <label className="form-label" htmlFor="email">{t('resetPassword.email')}</label>
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

                {turnstileSiteKey && (
                    <div className="mb-3">
                        <Turnstile
                            ref={turnstileRef}
                            siteKey={turnstileSiteKey}
                            onVerify={(token) => form.setData('cf-turnstile-response', token)}
                            onExpire={() => form.setData('cf-turnstile-response', '')}
                        />
                        {form.errors['cf-turnstile-response'] && (
                            <div className="text-danger small mt-1">{form.errors['cf-turnstile-response']}</div>
                        )}
                    </div>
                )}

                <button
                    type="submit"
                    className="btn btn-primary w-100"
                    disabled={form.processing || (!!turnstileSiteKey && !form.data['cf-turnstile-response'])}
                >
                    {t('resetPassword.resetPassword')}
                </button>
            </form>
        </AuthLayout>
    )
}
