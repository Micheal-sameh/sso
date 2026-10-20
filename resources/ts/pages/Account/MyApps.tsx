import { useTranslation } from 'react-i18next'
import AppLayout from '@/components/layouts/AppLayout'

interface MyApp {
    id: string
    name: string
    logo: string | null
    url: string
}

export default function MyApps({ apps }: { apps: MyApp[] }) {
    const { t } = useTranslation('common')

    return (
        <AppLayout title={t('nav.myApps')}>
            {apps.length === 0 ? (
                <div className="text-center py-5">
                    <i className="bi bi-grid text-ava-muted icon-fs-48" />
                    <p className="text-ava-muted mt-3">{t('welcome.noApps')}</p>
                </div>
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
                                {/* Only http(s): a stored javascript:/data: URL must never become a link. */}
                                {app.url && /^https?:\/\//i.test(app.url) && (
                                    <a href={app.url} className="btn btn-primary btn-sm">
                                        {t('welcome.openApp')}
                                    </a>
                                )}
                            </div>
                        </div>
                    ))}
                </div>
            )}
        </AppLayout>
    )
}
