import { useEffect, useRef, useState } from 'react'

const PULL_THRESHOLD = 70
const MAX_PULL = 100
// Gesture must start within this many px of the top of the viewport — not
// just anywhere on an unscrolled page — so it can't be triggered by a
// downward drag started lower on the screen (e.g. over a card or carousel).
const TOP_ZONE_PX = 80

/**
 * Finds how far the content under `target` is scrolled from its top. The
 * window itself doesn't always do the scrolling — on mobile, .main-content-area
 * is the actual scroll container (see .app-shell in app.css) while the window
 * stays fixed at scrollY 0 — so we have to walk up to whichever ancestor is
 * actually scrollable rather than assume it's the window.
 */
function getScrollTop(target: EventTarget | null): number {
    let node = target instanceof Element ? target : null

    while (node && node !== document.body && node !== document.documentElement) {
        const style = getComputedStyle(node)
        if ((style.overflowY === 'auto' || style.overflowY === 'scroll') && node.scrollHeight > node.clientHeight) {
            return node.scrollTop
        }
        node = node.parentElement
    }

    return window.scrollY || document.documentElement.scrollTop
}

/**
 * Custom pull-to-refresh gesture for the installed PWA. The browser's native
 * overscroll/refresh gesture is disabled globally (see overscroll-behavior-y
 * in app.css) so an installed, chrome-less app has no other way to reload —
 * this replaces it with a real document reload (window.location.reload()),
 * matching what a browser's own pull-to-refresh does: fresh HTML/JS/CSS, not
 * just an Inertia data refetch.
 */
export default function PullToRefresh() {
    const [pullDistance, setPullDistance] = useState(0)
    const [refreshing, setRefreshing] = useState(false)
    const startY = useRef<number | null>(null)
    const pulling = useRef(false)

    useEffect(() => {
        const onTouchStart = (event: TouchEvent) => {
            if (refreshing) return
            if (getScrollTop(event.target) > 0) return
            if (event.touches[0].clientY > TOP_ZONE_PX) return
            startY.current = event.touches[0].clientY
            pulling.current = true
        }

        const onTouchMove = (event: TouchEvent) => {
            if (!pulling.current || startY.current === null) return
            const delta = event.touches[0].clientY - startY.current
            setPullDistance(delta > 0 ? Math.min(delta * 0.5, MAX_PULL) : 0)
        }

        const onTouchEnd = () => {
            if (!pulling.current) return
            pulling.current = false
            startY.current = null
            setPullDistance((distance) => {
                if (distance >= PULL_THRESHOLD) {
                    setRefreshing(true)
                    window.location.reload()
                }
                return 0
            })
        }

        window.addEventListener('touchstart', onTouchStart, { passive: true })
        window.addEventListener('touchmove', onTouchMove, { passive: true })
        window.addEventListener('touchend', onTouchEnd)

        return () => {
            window.removeEventListener('touchstart', onTouchStart)
            window.removeEventListener('touchmove', onTouchMove)
            window.removeEventListener('touchend', onTouchEnd)
        }
    }, [refreshing])

    const progress = Math.min(pullDistance / PULL_THRESHOLD, 1)
    const active = refreshing || pullDistance > 0

    return (
        <div
            className="pull-to-refresh-indicator"
            style={{
                opacity: active ? 1 : 0,
                transform: `translate(-50%, ${active ? 0 : -48}px)`,
            }}
        >
            <i
                className={`bi bi-arrow-clockwise ${refreshing ? 'pull-to-refresh-spin' : ''}`}
                style={!refreshing ? { transform: `rotate(${progress * 360}deg)` } : undefined}
            />
        </div>
    )
}
