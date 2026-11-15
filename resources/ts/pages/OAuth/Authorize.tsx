import { usePage } from '@inertiajs/react'
import { useTranslation } from 'react-i18next'
import AuthLayout from '@/components/layouts/AuthLayout'
import AvaLogo from '@/components/shared/AvaLogo'
import AvatarInitials from '@/components/shared/AvatarInitials'
import type { SharedProps } from '@/types'

interface AuthorizeProps {
    client: { name: string; logo: string | null; homepage_url: string | null }
    scopes: { id: string; description: string }[]
    authRequest: string
    state: string
}

export default function Authorize({ client, scopes, authRequest, state }: AuthorizeProps) {
    const { t } = useTranslation('oauth')
    const { auth, csrf_token, other_accounts } = usePage<SharedProps>().props
    const intended = window.location.pathname + window.location.search

    return (
        <AuthLayout wide>
            <div className="app-connection mb-4">
                {client.logo ? (
                    <img src={client.logo} alt={client.name} width={48} height={48} className="app-card-logo" />
                ) : (
                    <div className="app-card-logo app-card-logo-fallback">{client.name.slice(0, 2).toUpperCase()}</div>
                )}
                <i className="bi bi-arrow-right" />
                <AvaLogo size={48} />
            </div>

            <div className="text-center mb-4">
                <h4 className="mb-0">{t('authorize.wantsToAccess', { clientName: client.name })}</h4>
            </div>

            {auth.user && (
                <div className="user-summary mb-4">
                    {auth.user.avatar ? (
                        <img src={auth.user.avatar} alt="" width={44} height={44} className="rounded-circle" />
                    ) : (
                        <AvatarInitials name={auth.user.name} size={44} />
                    )}
                    <div className="min-w-0">
                        <div className="fw-semibold text-truncate">{auth.user.name}</div>
                        <div className="user-summary-code">{auth.user.membership_code}</div>
                        {auth.user.email && <div className="text-ava-muted small text-truncate">{auth.user.email}</div>}
                    </div>
                </div>
            )}

            <div className="mb-4">
                <p className="fw-semibold small mb-2">{t('authorize.thisWillAllow', { clientName: client.name })}</p>
                {scopes.map((scope) => (
                    <div key={scope.id} className="permission-row">
                        <span className="permission-check">
                            <i className="bi bi-check-lg" />
                        </span>
                        <span>{scope.description}</span>
                    </div>
                ))}
            </div>

            {/*
                These submit as real top-level browser navigations (not Inertia/XHR).
                The server redirects across origins back to the requesting app, and a
                cross-origin redirect can only be followed safely by a full navigation
                — following it via fetch/XHR would be blocked by CORS.
            */}
            <div className="button-pair">
                <form method="POST" action="/oauth/authorize">
                    <input type="hidden" name="_token" value={csrf_token} />
                    <input type="hidden" name="_method" value="DELETE" />
                    <input type="hidden" name="auth_token" value={authRequest} />
                    <input type="hidden" name="state" value={state} />
                    <button type="submit" className="btn btn-outline-primary w-100">
                        {t('authorize.deny')}
                    </button>
                </form>
                <form method="POST" action="/oauth/authorize">
                    <input type="hidden" name="_token" value={csrf_token} />
                    <input type="hidden" name="auth_token" value={authRequest} />
                    <input type="hidden" name="state" value={state} />
                    <button type="submit" className="btn btn-primary w-100">
                        {t('authorize.allowAccess')}
                    </button>
                </form>
            </div>

            {other_accounts.length > 0 && (
                <div className="mt-4 pt-3 border-top">
                    <p className="fw-semibold small mb-2">{t('authorize.switchAccount')}</p>
                    <div className="d-flex flex-column gap-2">
                        {other_accounts.map((account) => (
                            <form key={account.id} method="POST" action="/accounts/switch">
                                <input type="hidden" name="_token" value={csrf_token} />
                                <input type="hidden" name="user_id" value={account.id} />
                                <input type="hidden" name="intended" value={intended} />
                                <button
                                    type="submit"
                                    className="account-switch-btn"
                                >
                                    <AvatarInitials name={account.name} size={40} />
                                    <span className="flex-grow-1 min-w-0">
                                        <span className="d-block fw-semibold text-truncate">{account.name}</span>
                                        <span className="d-block small text-ava-muted text-truncate">
                                            {account.membership_code.toUpperCase()}
                                            {account.email ? ` · ${account.email}` : ''}
                                        </span>
                                    </span>
                                    <i className="bi bi-chevron-right text-ava-muted" />
                                </button>
                            </form>
                        ))}
                    </div>
                </div>
            )}

            <form method="POST" action="/accounts/add" className="text-center mt-3">
                <input type="hidden" name="_token" value={csrf_token} />
                <input type="hidden" name="intended" value={intended} />
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
