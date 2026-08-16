import { useForm } from '@inertiajs/react'
import type { FormEvent } from 'react'
import AuthLayout from '@/components/layouts/AuthLayout'
import AvaLogo from '@/components/shared/AvaLogo'

interface ResetPasswordProps {
    token: string
    email: string
}

interface ResetPasswordForm {
    token: string
    email: string
    password: string
    password_confirmation: string
}

export default function ResetPassword({ token, email }: ResetPasswordProps) {
    const form = useForm<ResetPasswordForm>({
        token,
        email,
        password: '',
        password_confirmation: '',
    })

    const submit = (e: FormEvent) => {
        e.preventDefault()
        form.post('/reset-password')
    }

    return (
        <AuthLayout>
            <div className="text-center mb-4">
                <AvaLogo size={56} showText />
                <p className="text-ava-muted small mt-2 mb-0">Choose a new password</p>
            </div>

            <form onSubmit={submit}>
                <div className="mb-3">
                    <label className="form-label" htmlFor="email">Email</label>
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
                    <label className="form-label" htmlFor="password">New Password</label>
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
                    <label className="form-label" htmlFor="password_confirmation">Confirm New Password</label>
                    <input
                        id="password_confirmation"
                        type="password"
                        className="form-control"
                        value={form.data.password_confirmation}
                        onChange={(e) => form.setData('password_confirmation', e.target.value)}
                    />
                </div>

                <button type="submit" className="btn btn-primary w-100" disabled={form.processing}>
                    Reset Password
                </button>
            </form>
        </AuthLayout>
    )
}
