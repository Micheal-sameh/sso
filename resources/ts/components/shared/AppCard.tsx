import { useTranslation } from 'react-i18next'

interface AppCardProps {
    app: { id: string; name: string; logo: string | null; url: string }
}

/** Launcher tile for an app the signed-in user can open. */
export default function AppCard({ app }: AppCardProps) {
    const { t } = useTranslation('common')
    // Only http(s): a stored javascript:/data: URL must never become a link.
    const safeUrl = app.url && /^https?:\/\//i.test(app.url) ? app.url : null

    return (
        <article className="app-card">
            {app.logo ? (
                <img src={app.logo} alt="" width={48} height={48} className="app-card-logo" />
            ) : (
                <div className="app-card-logo app-card-logo-fallback">{app.name.slice(0, 2).toUpperCase()}</div>
            )}
            <div className="min-w-0">
                <h3 className="app-card-title text-truncate">{app.name}</h3>
                <p className="app-card-subtitle mb-0">{t('brand.appSubtitle')}</p>
            </div>
            {safeUrl && (
                <a href={safeUrl} className="btn btn-outline-primary app-card-open">
                    {t('welcome.openApp')}
                    <i className="bi bi-arrow-right" />
                </a>
            )}
        </article>
    )
}
