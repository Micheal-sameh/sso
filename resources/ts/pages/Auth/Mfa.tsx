import { useEffect, useState } from 'react'
import type { FormEvent } from 'react'
import { router, useForm } from '@inertiajs/react'
import { useTranslation } from 'react-i18next'
import AuthLayout from '@/components/layouts/AuthLayout'
import AvaLogo from '@/components/shared/AvaLogo'
import OtpInput from '@/components/shared/OtpInput'

interface MfaForm {
    code: string
}

export default function Mfa() {
    const { t } = useTranslation('auth')
    const [mode, setMode] = useState<'totp' | 'recovery'>('totp')
    const form = useForm<MfaForm>({ code: '' })

    // Auto-submit as soon as the 6th authenticator digit is entered (typed or pasted).
    useEffect(() => {
        if (mode === 'totp' && /^\d{6}$/.test(form.data.code) && !form.processing) {
            form.post('/mfa')
        }
        // eslint-disable-next-line react-hooks/exhaustive-deps
    }, [form.data.code, mode])

    const submit = (e: FormEvent) => {
        e.preventDefault()
        form.post('/mfa')
    }

    return (
        <AuthLayout>
            <div className="text-center mb-3">
                <AvaLogo size={56} />
            </div>
            <button type="button" className="back-link" onClick={() => router.visit('/login')}>
                <i className="bi bi-arrow-left" />
                {t('mfa.returnToSignIn')}
            </button>
            <div className="text-center">
                <div className="security-icon">
                    <i className="bi bi-fingerprint" />
                </div>
            </div>
            <div className="auth-heading">
                <h1>{t('mfa.title')}</h1>
                <p>{mode === 'totp' ? t('mfa.totpDescription') : t('mfa.recoveryDescription')}</p>
            </div>

            <form onSubmit={submit}>
                {mode === 'totp' ? (
                    <div className="mb-3">
                        <OtpInput value={form.data.code} onChange={(code) => form.setData('code', code)} autoFocus />
                    </div>
                ) : (
                    <input
                        className="form-control mb-3"
                        placeholder={t('mfa.recoveryCodePlaceholder')}
                        maxLength={9}
                        autoFocus
                        value={form.data.code}
                        onChange={(e) => form.setData('code', e.target.value)}
                    />
                )}
                {form.errors.code && <div className="text-danger small mb-3">{form.errors.code}</div>}

                <button type="submit" className="btn btn-primary w-100 mb-3 d-flex align-items-center justify-content-center gap-2" disabled={form.processing}>
                    {t('mfa.verifyCode')}
                    <i className="bi bi-arrow-right" />
                </button>
            </form>

            <div className="text-center">
                <button
                    type="button"
                    className="btn btn-link text-ava-secondary text-decoration-none small"
                    onClick={() => setMode(mode === 'totp' ? 'recovery' : 'totp')}
                >
                    <i className={`bi ${mode === 'totp' ? 'bi-key' : 'bi-shield-lock'} me-1`} />
                    {mode === 'totp' ? t('mfa.useRecoveryCodeInstead') : t('mfa.useAuthenticatorInstead')}
                </button>
            </div>

            <div className="text-center mt-3 pt-3 border-top">
                <span className="text-ava-muted small">
                    <i className="bi bi-info-circle me-1" />
                    {t('mfa.needHelp')}
                </span>{' '}
                <button
                    type="button"
                    className="btn btn-link text-ava-secondary text-decoration-none small p-0"
                    onClick={() => router.visit('/login')}
                >
                    {t('mfa.returnToSignIn')}
                </button>
            </div>
        </AuthLayout>
    )
}
