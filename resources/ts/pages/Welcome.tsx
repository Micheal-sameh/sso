import { Link } from '@inertiajs/react'
import { useTranslation } from 'react-i18next'
import AvaLogo from '@/components/shared/AvaLogo'

const FEATURE_KEYS = ['sso', 'mfa', 'directory', 'accessControl', 'recovery', 'integrations'] as const

const FEATURE_ICONS: Record<(typeof FEATURE_KEYS)[number], string> = {
    sso: 'bi-shield-lock',
    mfa: 'bi-phone',
    directory: 'bi-people',
    accessControl: 'bi-key',
    recovery: 'bi-clock-history',
    integrations: 'bi-diagram-3',
}

export default function Welcome() {
    const { t } = useTranslation('common')

    return (
        <div className="welcome-page">
            <header className="welcome-header d-flex align-items-center justify-content-between px-4 py-3">
                <AvaLogo size={36} showText />
                <Link href="/login" className="btn btn-primary btn-sm">
                    {t('welcome.signIn')}
                </Link>
            </header>

            <section className="welcome-hero text-center py-5 px-3">
                <span className="role-chip mb-3 d-inline-block">{t('welcome.eyebrow')}</span>
                <h1 className="mb-3">{t('welcome.heroTitle')}</h1>
                <p className="text-ava-muted mx-auto mb-4 max-w-560">
                    {t('welcome.heroDescription')}
                </p>
                <div className="d-flex gap-2 justify-content-center flex-wrap">
                    <Link href="/login" className="btn btn-primary">
                        {t('welcome.signInToYourAccount')}
                    </Link>
                    <a href="#features" className="btn btn-outline-primary">
                        {t('welcome.learnMore')}
                    </a>
                </div>
            </section>

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
