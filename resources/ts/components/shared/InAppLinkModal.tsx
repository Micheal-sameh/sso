import { useEffect, useRef, useState } from 'react'
import { useTranslation } from 'react-i18next'

interface InAppLinkModalProps {
    url: string | null
    onClose: () => void
}

const LOAD_FALLBACK_DELAY_MS = 5000

function toYoutubeEmbedUrl(url: string): string | null {
    let parsed: URL
    try {
        parsed = new URL(url)
    } catch {
        return null
    }

    const host = parsed.host.toLowerCase()
    let videoId: string | null = null

    if (host === 'youtu.be' || host.endsWith('.youtu.be')) {
        videoId = parsed.pathname.replace(/^\//, '') || null
    } else if (host.endsWith('youtube.com')) {
        videoId = parsed.searchParams.get('v')
    }

    if (!videoId) return null

    // The embedded player validates the embedding page's origin; without it
    // (and with a referrer-stripping policy on the iframe) it refuses to
    // play and shows "Error 153: Video player configuration error".
    const origin = encodeURIComponent(window.location.origin)

    return `https://www.youtube.com/embed/${videoId}?origin=${origin}`
}

export default function InAppLinkModal({ url, onClose }: InAppLinkModalProps) {
    const { t } = useTranslation('common')
    const [loaded, setLoaded] = useState(false)
    const [showFallback, setShowFallback] = useState(false)
    const timerRef = useRef<ReturnType<typeof setTimeout> | null>(null)

    useEffect(() => {
        setLoaded(false)
        setShowFallback(false)

        if (timerRef.current) clearTimeout(timerRef.current)
        if (url) {
            timerRef.current = setTimeout(() => setShowFallback(true), LOAD_FALLBACK_DELAY_MS)
        }

        return () => {
            if (timerRef.current) clearTimeout(timerRef.current)
        }
    }, [url])

    if (!url) return null

    let host = url
    try {
        host = new URL(url).host
    } catch {
        // keep raw url as the label
    }

    // YouTube's normal watch/short-link pages send X-Frame-Options and
    // refuse to load in an iframe at all; only /embed/{id} is embeddable.
    const youtubeEmbedSrc = toYoutubeEmbedUrl(url)
    const embedSrc = youtubeEmbedSrc ?? url

    return (
        <div className="modal d-block modal-backdrop-custom" tabIndex={-1}>
            <div className="modal-dialog modal-dialog-centered modal-fullscreen-md-down modal-xl">
                <div className="modal-content" style={{ height: '85vh' }}>
                    <div className="modal-header py-2">
                        <div className="d-flex align-items-center gap-2 min-w-0">
                            <i className="bi bi-globe text-ava-muted" />
                            <span className="text-truncate text-ava-muted text-tiny">{host}</span>
                        </div>
                        <div className="d-flex align-items-center gap-2">
                            <a href={url} target="_blank" rel="noopener noreferrer" className="btn btn-sm btn-outline-primary">
                                {t('inAppLinkModal.openInNewTab')} <i className="bi bi-box-arrow-up-right ms-1" />
                            </a>
                            <button type="button" className="btn-close" aria-label={t('actions.close')} onClick={onClose} />
                        </div>
                    </div>
                    <div className="modal-body p-0 position-relative d-flex flex-column">
                        {!loaded && (
                            <div className="position-absolute top-0 start-0 w-100 h-100 d-flex align-items-center justify-content-center bg-white">
                                <div className="spinner-border text-ava-secondary" role="status">
                                    <span className="visually-hidden">{t('inAppLinkModal.loading')}</span>
                                </div>
                            </div>
                        )}
                        {showFallback && (
                            <div className="alert alert-warning m-3 mb-0 d-flex align-items-center justify-content-between gap-2">
                                <span>{t('inAppLinkModal.mayNotDisplayCorrectly')}</span>
                                <a href={url} target="_blank" rel="noopener noreferrer" className="btn btn-sm btn-warning text-nowrap">
                                    {t('inAppLinkModal.openInNewTab')}
                                </a>
                            </div>
                        )}
                        <iframe
                            src={embedSrc}
                            title={host}
                            className="flex-grow-1 border-0 w-100"
                            referrerPolicy={youtubeEmbedSrc ? 'strict-origin-when-cross-origin' : 'no-referrer'}
                            sandbox="allow-scripts allow-same-origin allow-forms allow-popups allow-popups-to-escape-sandbox"
                            onLoad={() => setLoaded(true)}
                        />
                    </div>
                </div>
            </div>
        </div>
    )
}
