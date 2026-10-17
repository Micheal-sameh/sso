import { Link, router, usePage } from '@inertiajs/react'
import { useTranslation } from 'react-i18next'
import AvaLogo from '@/components/shared/AvaLogo'
import type { SharedProps } from '@/types'

const FEATURE_KEYS = ['sso', 'mfa', 'directory', 'accessControl', 'recovery', 'integrations'] as const

const FEATURE_ICONS: Record<(typeof FEATURE_KEYS)[number], string> = {
    sso: 'bi-shield-lock',
    mfa: 'bi-phone',
    directory: 'bi-people',
    accessControl: 'bi-key',
    recovery: 'bi-clock-history',
    integrations: 'bi-diagram-3',
}

interface AccessibleApp {
    id: string
    name: string
    logo: string | null
    url: string
}

export default function Welcome({ apps = [] }: { apps?: AccessibleApp[] }) {
    const { t } = useTranslation('common')
    const user = usePage<SharedProps>().props.auth.user
    const signOut = () => router.post('/logout')

    return (
        <div className="welcome-page">
            <header className="welcome-header d-flex align-items-center justify-content-between px-4 py-3">
                <AvaLogo size={36} />
                {user ? (
                    <button type="button" className="btn btn-outline-primary btn-sm" onClick={signOut}>
                        {t('welcome.signOut')}
                    </button>
                ) : (
                    <Link href="/login" className="btn btn-primary btn-sm">
                        {t('welcome.signIn')}
                    </Link>
                )}
            </header>

            <section className="welcome-hero text-center py-5 px-3">
                <span className="role-chip mb-3 d-inline-block">{t('welcome.eyebrow')}</span>
                <h1 className="mb-3">{t('welcome.heroTitle')}</h1>
                <p className="text-ava-muted mx-auto mb-4 max-w-560">
                    {t('welcome.heroDescription')}
                </p>
                {user && (
                    <p className="mx-auto mb-4 max-w-560">
                        <strong>{t('welcome.signedInAs', { name: user.name })}</strong>
                        <br />
                        <span className="text-ava-muted">{t('welcome.signedInHint')}</span>
                    </p>
                )}
                <div className="d-flex gap-2 justify-content-center flex-wrap">
                    {!user && (
                        <Link href="/login" className="btn btn-primary">
                            {t('welcome.signInToYourAccount')}
                        </Link>
                    )}
                    <a href="#features" className="btn btn-outline-primary">
                        {t('welcome.learnMore')}
                    </a>
                </div>
            </section>

            {user && (
                <section className="container pb-4">
                    <h5 className="mb-3">{t('welcome.yourApps')}</h5>
                    {apps.length === 0 ? (
                        <p className="text-ava-muted">{t('welcome.noApps')}</p>
                    ) : (
                        <div className="row g-3">
                            {apps.map((app) => (
                                <div key={app.id} className="col-sm-6 col-lg-4">
                                    <div className="ava-card p-3 h-100 d-flex align-items-center gap-3">
                                        {app.logo ? (
                                            <img src={app.logo} alt="" width={40} height={40} className="rounded" />
                                        ) : (
                                            <div className="icon-tile text-ava-secondary">
                                                <i className="bi bi-grid" />
                                            </div>
                                        )}
                                        <span className="fw-semibold flex-grow-1">{app.name}</span>
                                        <a href={app.url} className="btn btn-primary btn-sm">
                                            {t('welcome.openApp')}
                                        </a>
                                    </div>
                                </div>
                            ))}
                        </div>
                    )}
                </section>
            )}

            <section id="features" className="container py-5">
                <div className="row g-4">
                    {FEATURE_KEYS.map((key) => (
                        <div key={key} className="col-md-6 col-lg-4">
                            <div className="ava-card p-4 h-100">
                                <div className="icon-tile text-ava-secondary mb-3">
                                    <i className={`bi ${FEATURE_ICONS[key]}`} />
                                </div>
                                <h5 className="mb-2">{t(`welcome.features.${key}.title`)}</h5>
                                <p className="text-ava-muted small mb-0">{t(`welcome.features.${key}.description`)}</p>
                            </div>
                        </div>
                    ))}
                </div>
            </section>

            <footer className="text-center text-ava-muted text-tiny py-4 border-top">
                {t('welcome.footer', { year: new Date().getFullYear() })}
            </footer>
        </div>
    )
}
