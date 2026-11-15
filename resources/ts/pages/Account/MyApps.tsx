import { usePage } from '@inertiajs/react'
import { useTranslation } from 'react-i18next'
import AppLayout from '@/components/layouts/AppLayout'
import AppCard from '@/components/shared/AppCard'
import type { SharedProps } from '@/types'

interface MyApp {
    id: string
    name: string
    logo: string | null
    url: string
}

export default function MyApps({ apps }: { apps: MyApp[] }) {
    const { t } = useTranslation('common')
    const user = usePage<SharedProps>().props.auth.user
    const firstName = user?.name.trim().split(/\s+/)[0] ?? ''

    return (
        <AppLayout>
            <section className="hero-banner mb-4">
                <div>
                    <span className="hero-pill">
                        <i className="bi bi-stars" />
                        {t('brand.welcomeBack', { name: firstName })}
                    </span>
                    <h2 className="mb-2">{t('welcome.heroTitle')}</h2>
                    <p className="mb-0">{t('welcome.heroDescription')}</p>
                </div>
                <div className="hero-art d-none d-sm-grid">
                    <i className="bi bi-shield-check" />
                    <span className="hero-orbit hero-orbit-one">
                        <i className="bi bi-person" />
                    </span>
                    <span className="hero-orbit hero-orbit-two">
                        <i className="bi bi-key" />
                    </span>
                </div>
            </section>

            <div className="section-heading">
                <div>
                    <span className="eyebrow">{t('brand.connectedServices')}</span>
                    <h3>{t('nav.myApps')}</h3>
                </div>
                <span className="text-ava-muted small">{t('brand.appsAvailable', { count: apps.length })}</span>
            </div>

            {apps.length === 0 ? (
                <div className="panel text-center py-5">
                    <i className="bi bi-grid text-ava-muted icon-fs-48" />
                    <p className="text-ava-muted mt-3 mb-0">{t('welcome.noApps')}</p>
                </div>
            ) : (
                <div className="row g-3">
                    {apps.map((app) => (
                        <div key={app.id} className="col-sm-6 col-xl-4">
                            <AppCard app={app} />
                        </div>
                    ))}
                </div>
            )}
        </AppLayout>
    )
}
