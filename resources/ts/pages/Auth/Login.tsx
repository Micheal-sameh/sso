import { useForm, usePage } from '@inertiajs/react'
import { useRef, useState } from 'react'
import type { FormEvent } from 'react'
import { Trans, useTranslation } from 'react-i18next'
import AuthLayout from '@/components/layouts/AuthLayout'
import AvaLogo from '@/components/shared/AvaLogo'
import Turnstile from '@/components/shared/Turnstile'
import type { TurnstileHandle } from '@/components/shared/Turnstile'
import type { SharedProps } from '@/types'

interface LoginProps {
    client?: { name: string; logo: string | null }
}

interface LoginForm {
    membership_code: string
    password: string
    remember: boolean
    'cf-turnstile-response': string
}

export default function Login({ client }: LoginProps) {
    const { t } = useTranslation('auth')
    const { turnstile_site_key: turnstileSiteKey } = usePage<SharedProps>().props
    const [showPassword, setShowPassword] = useState(false)
    const turnstileRef = useRef<TurnstileHandle>(null)
    const form = useForm<LoginForm>({
        membership_code: '',
        password: '',
        remember: false,
        'cf-turnstile-response': '',
    })

    const submit = (e: FormEvent) => {
        e.preventDefault()
        form.post('/login', {
            onFinish: () => {
                // A Turnstile token is single-use: the server already consumed
                // it on this attempt (even one that failed for another reason,
                // like wrong credentials), so a retry needs a fresh one.
                turnstileRef.current?.reset()
                form.setData('cf-turnstile-response', '')
            },
        })
    }

    return (
        <AuthLayout>
            <div className="text-center mb-4">
                <AvaLogo size={56} />
            </div>

            {client && (
                <div className="app-requesting-banner mb-4">
                    <i className="bi bi-app-indicator" />
                    <Trans t={t} i18nKey="login.appRequestingBanner" values={{ clientName: client.name }} components={{ strong: <strong /> }} />
                </div>
            )}

            <div className="auth-heading">
                <h1>{t('login.signInToAccount')}</h1>
                <p>{t('login.welcomeBack')}</p>
            </div>

            <form onSubmit={submit}>
                <div className="mb-3">
                    <label className="form-label" htmlFor="membership_code">{t('login.membershipCode')}</label>
                    <div className="input-icon-field">
                        <i className="bi bi-person input-icon" />
                        <input
                            id="membership_code"
                            type="text"
                            className="form-control"
                            placeholder={t('placeholders.membershipCode')}
                            autoComplete="username"
                            value={form.data.membership_code}
                            onChange={(e) => form.setData('membership_code', e.target.value)}
                            autoFocus
                        />
                    </div>
                    {form.errors.membership_code && (
                        <div className="text-danger small mt-1">{form.errors.membership_code}</div>
                    )}
                </div>

                <div className="mb-3">
                    <label className="form-label" htmlFor="password">{t('login.password')}</label>
                    <div className="input-icon-field has-action">
                        <i className="bi bi-lock input-icon" />
                        <input
                            id="password"
                            type={showPassword ? 'text' : 'password'}
                            className="form-control"
                            placeholder={t('placeholders.password')}
                            autoComplete="current-password"
                            value={form.data.password}
                            onChange={(e) => form.setData('password', e.target.value)}
                        />
                        <button
                            type="button"
                            className="input-action"
                            aria-label={t('login.togglePasswordVisibility')}
                            onClick={() => setShowPassword(!showPassword)}
                        >
                            <i className={`bi ${showPassword ? 'bi-eye-slash' : 'bi-eye'}`} />
                        </button>
                    </div>
                    {form.errors.password && <div className="text-danger small mt-1">{form.errors.password}</div>}
                </div>

                <div className="d-flex align-items-center justify-content-between gap-3 mb-3">
                    <div className="form-check mb-0">
                        <input
                            id="remember"
                            type="checkbox"
                            className="form-check-input"
                            checked={form.data.remember}
                            onChange={(e) => form.setData('remember', e.target.checked)}
                        />
                        <label className="form-check-label fw-normal text-ava-muted" htmlFor="remember">{t('login.rememberMe')}</label>
                    </div>
                    <a href="/forgot-password" className="text-ava-secondary text-decoration-none small fw-semibold">
                        {t('login.forgotPassword')}
                    </a>
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
                    {t('login.signIn')}
                    <i className="bi bi-arrow-right ms-2 rtl-flip" />
                </button>
            </form>

            <p className="text-center text-ava-muted text-tiny mt-4 mb-0">
                {t('login.needHelp')}{' '}
                <a href="mailto:avarewase.dc@gmail.com" className="fw-semibold text-body">avarewase.dc@gmail.com</a>
            </p>
        </AuthLayout>
    )
}
