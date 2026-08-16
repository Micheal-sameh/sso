import { useForm } from '@inertiajs/react'
import { useState } from 'react'
import type { FormEvent } from 'react'
import AuthLayout from '@/components/layouts/AuthLayout'
import AvaLogo from '@/components/shared/AvaLogo'

interface LoginProps {
    client?: { name: string; logo: string | null }
}

interface LoginForm {
    membership_code: string
    password: string
    remember: boolean
}

export default function Login({ client }: LoginProps) {
    const [showPassword, setShowPassword] = useState(false)
    const form = useForm<LoginForm>({
        membership_code: '',
        password: '',
        remember: false,
    })

    const submit = (e: FormEvent) => {
        e.preventDefault()
        form.post('/login')
    }

    return (
        <AuthLayout>
            <div className="text-center mb-4">
                <AvaLogo size={56} showText />
                <p className="text-ava-muted small mt-2 mb-0">Sign in to your account</p>
            </div>

            {client && (
                <div className="app-requesting-banner mb-4">
                    <strong>{client.name}</strong> uses Avarewase to sign you in.
                </div>
            )}

            <form onSubmit={submit}>
                <div className="mb-3">
                    <label className="form-label" htmlFor="membership_code">Membership Code</label>
                    <input
                        id="membership_code"
                        type="text"
                        className="form-control"
                        value={form.data.membership_code}
                        onChange={(e) => form.setData('membership_code', e.target.value)}
                        autoFocus
                    />
                    {form.errors.membership_code && (
                        <div className="text-danger small mt-1">{form.errors.membership_code}</div>
                    )}
                </div>

                <div className="mb-3">
                    <label className="form-label" htmlFor="password">Password</label>
                    <div className="input-group">
                        <input
                            id="password"
                            type={showPassword ? 'text' : 'password'}
                            className="form-control"
                            value={form.data.password}
                            onChange={(e) => form.setData('password', e.target.value)}
                        />
                        <button
                            type="button"
                            className="btn btn-outline-primary"
                            aria-label="Toggle password visibility"
                            onClick={() => setShowPassword(!showPassword)}
                        >
                            <i className={`bi ${showPassword ? 'bi-eye-slash' : 'bi-eye'}`} />
                        </button>
                    </div>
                    {form.errors.password && <div className="text-danger small mt-1">{form.errors.password}</div>}
                </div>

                <div className="form-check mb-3">
                    <input
                        id="remember"
                        type="checkbox"
                        className="form-check-input"
                        checked={form.data.remember}
                        onChange={(e) => form.setData('remember', e.target.checked)}
                    />
                    <label className="form-check-label" htmlFor="remember">Remember me</label>
                </div>

                <button type="submit" className="btn btn-primary w-100" disabled={form.processing}>
                    Sign In
                </button>

                <div className="text-center mt-3">
                    <a href="/forgot-password" className="text-ava-gold text-decoration-none small">Forgot password?</a>
                </div>
            </form>

            <p className="text-center text-ava-muted text-tiny mt-4 mb-0">
                Powered by Avarewase Auth
            </p>
        </AuthLayout>
    )
}
