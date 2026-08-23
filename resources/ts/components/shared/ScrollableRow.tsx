import { useRef, useState, type ReactNode } from 'react'

interface ScrollableRowProps<T> {
    items: T[]
    itemKey: (item: T) => string | number
    renderItem: (item: T) => ReactNode
}

/**
 * Horizontally-scrollable row with dot pagination below it, one dot per
 * item. Dots track which item is currently nearest the scroll position and
 * clicking one scrolls that item into view — there's no fixed-size "page"
 * here since card widths vary by content, so pagination is per-item rather
 * than per-viewport.
 */
export default function ScrollableRow<T>({ items, itemKey, renderItem }: ScrollableRowProps<T>) {
    const containerRef = useRef<HTMLDivElement>(null)
    const itemRefs = useRef<(HTMLDivElement | null)[]>([])
    const [activeIndex, setActiveIndex] = useState(0)

    const handleScroll = () => {
        const container = containerRef.current
        if (!container) return

        const containerCenter = container.scrollLeft + container.clientWidth / 2

        let closestIndex = 0
        let closestDistance = Infinity
        itemRefs.current.forEach((el, index) => {
            if (!el) return
            const itemCenter = el.offsetLeft + el.offsetWidth / 2
            const distance = Math.abs(itemCenter - containerCenter)
            if (distance < closestDistance) {
                closestDistance = distance
                closestIndex = index
            }
        })

        setActiveIndex(closestIndex)
    }

    const scrollToIndex = (index: number) => {
        itemRefs.current[index]?.scrollIntoView({ behavior: 'smooth', inline: 'center', block: 'nearest' })
    }

    return (
        <div>
            <div ref={containerRef} className="d-flex gap-3 overflow-auto pb-1" onScroll={handleScroll}>
                {items.map((item, index) => (
                    <div
                        key={itemKey(item)}
                        ref={(el) => {
                            itemRefs.current[index] = el
                        }}
                        className="flex-shrink-0"
                    >
                        {renderItem(item)}
                    </div>
                ))}
            </div>
            {items.length > 1 && (
                <div className="d-flex justify-content-center gap-2 mt-2">
                    {items.map((item, index) => (
                        <button
                            key={itemKey(item)}
                            type="button"
                            aria-label={`Go to item ${index + 1}`}
                            onClick={() => scrollToIndex(index)}
                            className="btn p-0 border-0 bg-transparent"
                            style={{ lineHeight: 0 }}
                        >
                            <span
                                className="d-block rounded-circle"
                                style={{
                                    width: 6,
                                    height: 6,
                                    backgroundColor: index === activeIndex ? 'var(--ava-secondary)' : 'var(--ava-muted)',
                                    opacity: index === activeIndex ? 1 : 0.4,
                                }}
                            />
                        </button>
                    ))}
                </div>
            )}
        </div>
    )
}
