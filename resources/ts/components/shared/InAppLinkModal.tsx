import { useEffect, useRef, useState } from 'react'

interface InAppLinkModalProps {
    url: string | null
    onClose: () => void
}

const LOAD_FALLBACK_DELAY_MS = 5000

export default function InAppLinkModal({ url, onClose }: InAppLinkModalProps) {
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
                                Open in new tab <i className="bi bi-box-arrow-up-right ms-1" />
                            </a>
                            <button type="button" className="btn-close" aria-label="Close" onClick={onClose} />
                        </div>
                    </div>
                    <div className="modal-body p-0 position-relative d-flex flex-column">
                        {!loaded && (
                            <div className="position-absolute top-0 start-0 w-100 h-100 d-flex align-items-center justify-content-center bg-white">
                                <div className="spinner-border text-ava-secondary" role="status">
                                    <span className="visually-hidden">Loading...</span>
                                </div>
                            </div>
                        )}
                        {showFallback && (
                            <div className="alert alert-warning m-3 mb-0 d-flex align-items-center justify-content-between gap-2">
                                <span>This site may not display correctly here.</span>
                                <a href={url} target="_blank" rel="noopener noreferrer" className="btn btn-sm btn-warning text-nowrap">
                                    Open in new tab
                                </a>
                            </div>
                        )}
                        <iframe
                            src={url}
                            title={host}
                            className="flex-grow-1 border-0 w-100"
                            referrerPolicy="no-referrer"
                            sandbox="allow-scripts allow-same-origin allow-forms allow-popups allow-popups-to-escape-sandbox"
                            onLoad={() => setLoaded(true)}
                        />
                    </div>
                </div>
            </div>
        </div>
    )
}
