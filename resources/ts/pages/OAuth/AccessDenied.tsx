import { router } from '@inertiajs/react'
import AuthLayout from '@/components/layouts/AuthLayout'

interface AccessDeniedProps {
    client: { name: string }
}

export default function AccessDenied({ client }: AccessDeniedProps) {
    return (
        <AuthLayout>
            <div className="text-center">
                <div className="icon-circle text-ava-primary mx-auto mb-3">
                    <i className="bi bi-shield-lock" />
                </div>
                <h4 className="mb-2">Access Denied</h4>
                <p className="text-ava-muted mb-4">
                    Your Avarewase account doesn&apos;t have access to <strong>{client.name}</strong>. Contact an
                    administrator if you believe this is a mistake.
                </p>
                <button type="button" className="btn btn-primary w-100" onClick={() => router.visit('/account')}>
                    Back to My Account
                </button>
            </div>
        </AuthLayout>
    )
}
