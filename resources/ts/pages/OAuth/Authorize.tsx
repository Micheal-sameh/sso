import { usePage } from '@inertiajs/react'
import { useTranslation } from 'react-i18next'
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
    const { t } = useTranslation('oauth')
    const { auth, csrf_token } = usePage<SharedProps>().props

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
                <i className="bi bi-arrow-right text-ava-secondary" />
                <AvaLogo size={48} />
            </div>

            <div className="text-center mb-4">
                <h4 className="mb-1">{t('authorize.wantsToAccess', { clientName: client.name })}</h4>
                {auth.user && <p className="text-ava-muted small mb-0">{auth.user.email}</p>}
            </div>

            <div className="mb-4">
                <p className="fw-semibold small mb-3">{t('authorize.thisWillAllow', { clientName: client.name })}</p>
                <div className="d-flex flex-column gap-2">
                    {scopes.map((scope) => (
                        <div key={scope.id} className="d-flex align-items-start gap-2 p-3 rounded border bg-light">
                            <i className="bi bi-check-circle-fill text-ava-secondary mt-1" />
                            <span className="small">{scope.description}</span>
                        </div>
                    ))}
                </div>
            </div>

            {/*
                These submit as real top-level browser navigations (not Inertia/XHR).
                The server redirects across origins back to the requesting app, and a
                cross-origin redirect can only be followed safely by a full navigation
                — following it via fetch/XHR would be blocked by CORS.
            */}
            <div className="d-flex gap-2">
                <form method="POST" action="/oauth/authorize" className="flex-grow-1">
                    <input type="hidden" name="_token" value={csrf_token} />
                    <input type="hidden" name="_method" value="DELETE" />
                    <input type="hidden" name="auth_token" value={authRequest} />
                    <input type="hidden" name="state" value={state} />
                    <button type="submit" className="btn btn-outline-primary w-100">
                        {t('authorize.deny')}
                    </button>
                </form>
                <form method="POST" action="/oauth/authorize" className="flex-grow-1">
                    <input type="hidden" name="_token" value={csrf_token} />
                    <input type="hidden" name="auth_token" value={authRequest} />
                    <input type="hidden" name="state" value={state} />
                    <button type="submit" className="btn btn-primary w-100">
                        {t('authorize.allowAccess')}
                    </button>
                </form>
            </div>

            <form method="POST" action="/logout" className="text-center mt-3">
                <input type="hidden" name="_token" value={csrf_token} />
                <input type="hidden" name="intended" value={window.location.pathname + window.location.search} />
                <button type="submit" className="btn btn-link btn-sm text-ava-secondary text-decoration-none p-0">
                    {t('authorize.useAnotherAccount')}
                </button>
            </form>

            <p className="text-ava-muted small text-center mt-4 mb-0 pt-3 border-top">
                {t('authorize.revokeNotice')}
            </p>
        </AuthLayout>
    )
}
