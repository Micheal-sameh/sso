import { router } from '@inertiajs/react'
import AuthLayout from '@/components/layouts/AuthLayout'

interface ProfileUpdateRequiredProps {
    client: { name: string }
}

export default function ProfileUpdateRequired({ client }: ProfileUpdateRequiredProps) {
    return (
        <AuthLayout>
            <div className="text-center">
                <div className="icon-circle text-ava-primary mx-auto mb-3">
                    <i className="bi bi-person-gear" />
                </div>
                <h4 className="mb-2">Update Your Data</h4>
                <p className="text-ava-muted mb-4">
                    Your Avarewase account needs to be updated before you can sign in to <strong>{client.name}</strong>.
                    Contact an administrator to update your details, then try again.
                </p>
                <button type="button" className="btn btn-primary w-100" onClick={() => router.visit('/account')}>
                    Back to My Account
                </button>
            </div>
        </AuthLayout>
    )
}
