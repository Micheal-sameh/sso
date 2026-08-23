import { useEffect, useRef, useState } from 'react'
import { router } from '@inertiajs/react'

const PULL_THRESHOLD = 70
const MAX_PULL = 100

/**
 * Custom pull-to-refresh gesture for the installed PWA. The browser's native
 * overscroll/refresh gesture is disabled globally (see overscroll-behavior-y
 * in app.css) so an installed, chrome-less app has no other way to reload —
 * this replaces it with an Inertia `router.reload()`, which re-fetches the
 * current page's data without a full document reload.
 */
export default function PullToRefresh() {
    const [pullDistance, setPullDistance] = useState(0)
    const [refreshing, setRefreshing] = useState(false)
    const startY = useRef<number | null>(null)
    const pulling = useRef(false)

    useEffect(() => {
        const onTouchStart = (event: TouchEvent) => {
            if (window.scrollY > 0 || refreshing) return
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
                    router.reload({ onFinish: () => setRefreshing(false) })
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
