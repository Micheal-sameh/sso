import { Link, usePage } from '@inertiajs/react'
import { useTranslation } from 'react-i18next'
import AppCard from '@/components/shared/AppCard'
import AvaLogo from '@/components/shared/AvaLogo'
import UserMenu from '@/components/shared/UserMenu'
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

    return (
        <div className="welcome-page">
            <header className="welcome-header d-flex align-items-center justify-content-between px-4 py-3">
                <AvaLogo size={36} />
                {user ? (
                    <UserMenu />
                ) : (
                    <Link href="/login" className="btn btn-primary btn-sm">
                        {t('welcome.signIn')}
                    </Link>
                )}
            </header>

            <section className="container pt-4 pt-md-5 pb-4">
                <div className="hero-banner">
                    <div>
                        <span className="hero-pill">
                            <i className="bi bi-stars" />
                            {t('welcome.eyebrow')}
                        </span>
                        <h1 className="mb-3">{t('welcome.heroTitle')}</h1>
                        <p className="mb-4">{t('welcome.heroDescription')}</p>
                        {user && (
                            <p className="mb-4">
                                <strong className="text-white">{t('welcome.signedInAs', { name: user.name })}</strong>
                                <br />
                                {t('welcome.signedInHint')}
                            </p>
                        )}
                        <div className="d-flex gap-2 flex-wrap">
                            {!user && (
                                <Link href="/login" className="btn btn-primary">
                                    {t('welcome.signInToYourAccount')}
                                </Link>
                            )}
                            <a href="#features" className="btn btn-outline-primary">
                                {t('welcome.learnMore')}
                            </a>
                        </div>
                    </div>
                    <div className="hero-art d-none d-md-grid">
                        <i className="bi bi-shield-check" />
                        <span className="hero-orbit hero-orbit-one">
                            <i className="bi bi-person" />
                        </span>
                        <span className="hero-orbit hero-orbit-two">
                            <i className="bi bi-key" />
                        </span>
                    </div>
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
                                    <AppCard app={app} />
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
                            <div className="feature-card">
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
