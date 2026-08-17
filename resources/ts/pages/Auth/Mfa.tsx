import { useState } from 'react'
import type { FormEvent } from 'react'
import { router, useForm } from '@inertiajs/react'
import AuthLayout from '@/components/layouts/AuthLayout'
import OtpInput from '@/components/shared/OtpInput'

interface MfaForm {
    code: string
}

export default function Mfa() {
    const [mode, setMode] = useState<'totp' | 'recovery'>('totp')
    const form = useForm<MfaForm>({ code: '' })

    const submit = (e: FormEvent) => {
        e.preventDefault()
        form.post('/mfa')
    }

    return (
        <AuthLayout>
            <div className="text-center mb-4">
                <div className="icon-circle text-ava-secondary mx-auto mb-3">
                    <i className="bi bi-shield-lock-fill" />
                </div>
                <h4>Two-Factor Authentication</h4>
                <p className="text-ava-muted small mb-0">
                    {mode === 'totp'
                        ? 'Enter the 6-digit code from your authenticator app to verify your identity.'
                        : 'Enter one of your recovery codes to verify your identity.'}
                </p>
            </div>

            <form onSubmit={submit}>
                {mode === 'totp' ? (
                    <div className="mb-3">
                        <OtpInput value={form.data.code} onChange={(code) => form.setData('code', code)} autoFocus />
                    </div>
                ) : (
                    <input
                        className="form-control mb-3"
                        placeholder="XXXX-XXXX"
                        maxLength={9}
                        autoFocus
                        value={form.data.code}
                        onChange={(e) => form.setData('code', e.target.value)}
                    />
                )}
                {form.errors.code && <div className="text-danger small mb-3">{form.errors.code}</div>}

                <button type="submit" className="btn btn-primary w-100 mb-3 d-flex align-items-center justify-content-center gap-2" disabled={form.processing}>
                    Verify Code
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
                    {mode === 'totp' ? 'Use a recovery code instead' : 'Use authenticator app instead'}
                </button>
            </div>

            <div className="text-center mt-3 pt-3 border-top">
                <span className="text-ava-muted small">
                    <i className="bi bi-info-circle me-1" />
                    Need help?
                </span>{' '}
                <button
                    type="button"
                    className="btn btn-link text-ava-secondary text-decoration-none small p-0"
                    onClick={() => router.visit('/login')}
                >
                    Return to Sign In
                </button>
            </div>
        </AuthLayout>
    )
}
