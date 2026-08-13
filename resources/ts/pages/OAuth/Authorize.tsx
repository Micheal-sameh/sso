import { useForm, usePage } from '@inertiajs/react'
import AuthLayout from '@/components/layouts/AuthLayout'
import AvaLogo from '@/components/shared/AvaLogo'
import type { SharedProps } from '@/types'

interface AuthorizeProps {
    client: { name: string; logo: string | null; homepage_url: string | null }
    scopes: { id: string; description: string }[]
    authRequest: string
    state: string
}

export default function Authorize({ client, scopes, authRequest, state }: AuthorizeProps) {
    const { auth } = usePage<SharedProps>().props
    const approveForm = useForm({ auth_token: authRequest, state })
    const denyForm = useForm({ auth_token: authRequest, state })

    const approve = () => approveForm.post('/oauth/authorize')
    const deny = () => denyForm.delete('/oauth/authorize')

    return (
        <AuthLayout wide>
            <div className="d-flex align-items-center justify-content-center gap-3 mb-4">
                {client.logo ? (
                    <img src={client.logo} alt={client.name} width={48} height={48} className="rounded" />
                ) : (
                    <div className="icon-circle-sm text-ava-muted">
                        <i className="bi bi-app" />
                    </div>
                )}
                <i className="bi bi-arrow-right text-ava-gold" />
                <AvaLogo size={48} />
            </div>

            <div className="text-center mb-4">
                <h4 className="mb-1">{client.name} wants to access your Avarewase account</h4>
                {auth.user && <p className="text-ava-muted small mb-0">{auth.user.email}</p>}
            </div>

            <div className="permission-list mb-4">
                <p className="fw-semibold small mb-3">This app will be able to:</p>
                <ul className="list-unstyled mb-0">
                    {scopes.map((scope) => (
                        <li key={scope.id} className="d-flex align-items-start gap-2 mb-2">
                            <i className="bi bi-check-circle-fill text-ava-gold mt-1" />
                            <span className="small">{scope.description}</span>
                        </li>
                    ))}
                </ul>
            </div>

            <div className="d-flex gap-2">
                <button
                    type="button"
                    className="btn btn-outline-primary flex-grow-1"
                    disabled={denyForm.processing}
                    onClick={deny}
                >
                    Deny
                </button>
                <button
                    type="button"
                    className="btn btn-primary flex-grow-1"
                    disabled={approveForm.processing}
                    onClick={approve}
                >
                    Allow Access
                </button>
            </div>

            <p className="text-ava-muted small text-center mt-4 mb-0 pt-3 border-top">
                You can revoke access at any time in your Avarewase Security Settings.
            </p>
        </AuthLayout>
    )
}
