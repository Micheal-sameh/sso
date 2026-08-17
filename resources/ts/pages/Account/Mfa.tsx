import { useState } from 'react'
import type { FormEvent } from 'react'
import { router, useForm } from '@inertiajs/react'
import AppLayout from '@/components/layouts/AppLayout'
import OtpInput from '@/components/shared/OtpInput'
import StatusBadge from '@/components/shared/StatusBadge'
import type { UserMfa } from '@/types'

interface MfaProps {
    mfa: UserMfa | null
    qrCodeSvg?: string
    secret?: string
    recoveryCodes?: string[] | null
}

export default function Mfa({ mfa, qrCodeSvg, secret, recoveryCodes }: MfaProps) {
    const [step, setStep] = useState<1 | 2>(qrCodeSvg ? 2 : 1)
    const [revealed, setRevealed] = useState(false)

    const confirmForm = useForm({ code: '' })
    const disableForm = useForm({ code: '' })

    const startSetup = () => {
        router.post('/account/mfa/enable', {}, { onSuccess: () => setStep(2) })
    }

    const confirmEnable = (e: FormEvent) => {
        e.preventDefault()
        confirmForm.post('/account/mfa/confirm')
    }

    const disable = (e: FormEvent) => {
        e.preventDefault()
        disableForm.post('/account/mfa/disable')
    }

    const regenerate = () => {
        router.post('/account/mfa/regenerate')
    }

    const copySecret = () => {
        if (secret) {
            navigator.clipboard.writeText(secret)
        }
    }

    return (
        <AppLayout title="Security">
            <p className="text-ava-muted mb-4">Manage your two-factor authentication and account recovery options.</p>

            <div className="row g-4">
                <div className="col-lg-4 d-flex flex-column gap-4">
                    <div className="ava-card">
                        <div className="card-accent card-accent-secondary" />
                        <div className="p-4">
                            <div className="d-flex align-items-center justify-content-between mb-3">
                                <span className="fw-semibold">
                                    <i className="bi bi-patch-check-fill text-ava-secondary me-2" />
                                    MFA Status
                                </span>
                                <StatusBadge status={mfa?.is_enabled ? 'enabled' : 'disabled'} />
                            </div>
                            <p className="text-ava-muted small">
                                Two-factor authentication adds an extra layer of security to your account. You will
                                need a code from your mobile device to sign in.
                            </p>
                            <div className="d-flex align-items-start gap-2 bg-light rounded p-3 border">
                                <i className="bi bi-phone text-ava-muted mt-1" />
                                <div>
                                    <p className="fw-semibold small mb-1">Authenticator App</p>
                                    <p className="text-ava-muted small mb-0">
                                        Use Google Authenticator, Authy, or 1Password.
                                    </p>
                                </div>
                            </div>
                        </div>
                    </div>

                    {recoveryCodes && (
                        <div className="ava-card">
                            <div className="p-4">
                                <div className="d-flex align-items-center gap-2 mb-3">
                                    <i className="bi bi-receipt text-ava-muted" />
                                    <span className="fw-semibold">Recovery Codes</span>
                                </div>
                                <p className="text-ava-muted small">
                                    Use these if you lose access to your authenticator app. Keep them in a safe
                                    place.
                                </p>
                                <div className="row row-cols-2 g-2 mb-3">
                                    {recoveryCodes.map((code) => (
                                        <div key={code} className="col">
                                            <div className={`recovery-code-box ${revealed ? '' : 'recovery-codes-blurred'}`}>
                                                {code}
                                            </div>
                                        </div>
                                    ))}
                                </div>
                                <div className="d-flex gap-2">
                                    <button
                                        type="button"
                                        className="btn btn-sm btn-outline-primary flex-grow-1"
                                        onClick={() => setRevealed(!revealed)}
                                    >
                                        <i className={`bi ${revealed ? 'bi-eye-slash' : 'bi-eye'} me-1`} />
                                        {revealed ? 'Hide' : 'Reveal'}
                                    </button>
                                    <button
                                        type="button"
                                        className="btn btn-sm btn-outline-primary flex-grow-1"
                                        onClick={regenerate}
                                    >
                                        <i className="bi bi-arrow-clockwise me-1" />
                                        Regenerate
                                    </button>
                                </div>
                            </div>
                        </div>
                    )}
                </div>

                <div className="col-lg-8">
                    {!mfa?.is_enabled && (
                        <div className="ava-card">
                            <div className="card-accent card-accent-primary" />
                            <div className="p-4 border-bottom">
                                <h5 className="mb-2">Configure Authenticator App</h5>
                                <p className="text-ava-muted small mb-0">
                                    Follow these steps to link your mobile device to your Avarewase account.
                                </p>
                            </div>

                            <div className="p-4 d-flex flex-column gap-4">
                                <div className="d-flex gap-3">
                                    <div className="step-badge step-badge-active">1</div>
                                    <div className="flex-grow-1">
                                        <h6 className="mb-2">Scan QR Code</h6>
                                        {step === 1 ? (
                                            <>
                                                <p className="text-ava-muted small mb-3">
                                                    Open your authenticator app and start setup to generate your QR
                                                    code and secret key.
                                                </p>
                                                <button type="button" className="btn btn-primary" onClick={startSetup}>
                                                    Start Setup
                                                </button>
                                            </>
                                        ) : (
                                            <>
                                                <p className="text-ava-muted small mb-3">
                                                    Open your authenticator app and scan the code below to add this
                                                    account.
                                                </p>
                                                <div className="d-flex flex-column flex-sm-row gap-4 align-items-center align-items-sm-start bg-light rounded p-3 border">
                                                    {qrCodeSvg && (
                                                        <div dangerouslySetInnerHTML={{ __html: qrCodeSvg }} />
                                                    )}
                                                    <div className="flex-grow-1 w-100">
                                                        <p className="small fw-semibold mb-1">Can&apos;t scan the code?</p>
                                                        <p className="text-ava-muted small mb-2">
                                                            Enter this setup key manually into your app instead.
                                                        </p>
                                                        <div className="input-group">
                                                            <input
                                                                type="text"
                                                                readOnly
                                                                className="form-control font-monospace small"
                                                                value={secret ?? ''}
                                                            />
                                                            <button
                                                                type="button"
                                                                className="btn btn-outline-primary"
                                                                title="Copy to clipboard"
                                                                onClick={copySecret}
                                                            >
                                                                <i className="bi bi-clipboard" />
                                                            </button>
                                                        </div>
                                                    </div>
                                                </div>
                                            </>
                                        )}
                                    </div>
                                </div>

                                {step === 2 && (
                                    <div className="d-flex gap-3">
                                        <div className="step-badge step-badge-pending">2</div>
                                        <div className="flex-grow-1">
                                            <h6 className="mb-2">Verify Connection</h6>
                                            <p className="text-ava-muted small mb-3">
                                                Enter the 6-digit code generated by your app to confirm setup.
                                            </p>
                                            <form onSubmit={confirmEnable}>
                                                <div className="mb-3">
                                                    <OtpInput
                                                        value={confirmForm.data.code}
                                                        onChange={(code) => confirmForm.setData('code', code)}
                                                    />
                                                </div>
                                                {confirmForm.errors.code && (
                                                    <div className="text-danger small mb-3">{confirmForm.errors.code}</div>
                                                )}
                                                <button
                                                    type="submit"
                                                    className="btn btn-primary"
                                                    disabled={confirmForm.processing}
                                                >
                                                    <i className="bi bi-unlock me-1" />
                                                    Enable Two-Factor
                                                </button>
                                            </form>
                                        </div>
                                    </div>
                                )}
                            </div>
                        </div>
                    )}

                    {mfa?.is_enabled && (
                        <div className="ava-card">
                            <div className="card-accent card-accent-primary" />
                            <div className="p-4">
                                <h5 className="mb-2">Disable Two-Factor Authentication</h5>
                                <p className="text-ava-muted small mb-3">
                                    Enter your current 6-digit code to turn off two-factor authentication.
                                </p>
                                <form onSubmit={disable}>
                                    <div className="mb-3">
                                        <OtpInput
                                            value={disableForm.data.code}
                                            onChange={(code) => disableForm.setData('code', code)}
                                        />
                                    </div>
                                    {disableForm.errors.code && (
                                        <div className="text-danger small mb-3">{disableForm.errors.code}</div>
                                    )}
                                    <button type="submit" className="btn btn-outline-primary" disabled={disableForm.processing}>
                                        Disable 2FA
                                    </button>
                                </form>
                            </div>
                        </div>
                    )}
                </div>
            </div>
        </AppLayout>
    )
}
