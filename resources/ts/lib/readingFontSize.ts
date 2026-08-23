import { useState } from 'react'

export const READING_FONT_SIZE_MIN = 14
export const READING_FONT_SIZE_MAX = 28
export const READING_FONT_SIZE_STEP = 2
const READING_FONT_SIZE_DEFAULT = 16
const READING_FONT_SIZE_STORAGE_KEY = 'reading-font-size'

function readStoredReadingFontSize(): number {
    try {
        const stored = Number(localStorage.getItem(READING_FONT_SIZE_STORAGE_KEY))

        if (stored >= READING_FONT_SIZE_MIN && stored <= READING_FONT_SIZE_MAX) {
            return stored
        }
    } catch {
        // localStorage unavailable (private browsing, blocked storage, etc.)
    }

    return READING_FONT_SIZE_DEFAULT
}

export function useReadingFontSize() {
    const [fontSize, setFontSize] = useState(readStoredReadingFontSize)

    const adjust = (delta: number) => {
        setFontSize((prev) => {
            const next = Math.min(READING_FONT_SIZE_MAX, Math.max(READING_FONT_SIZE_MIN, prev + delta))

            try {
                localStorage.setItem(READING_FONT_SIZE_STORAGE_KEY, String(next))
            } catch {
                // localStorage unavailable
            }

            return next
        })
    }

    return {
        fontSize,
        increase: () => adjust(READING_FONT_SIZE_STEP),
        decrease: () => adjust(-READING_FONT_SIZE_STEP),
        canIncrease: fontSize < READING_FONT_SIZE_MAX,
        canDecrease: fontSize > READING_FONT_SIZE_MIN,
    }
}
