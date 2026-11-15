export type Theme = 'light' | 'dark'

const STORAGE_KEY = 'ava-theme'

export function currentTheme(): Theme {
    return document.documentElement.dataset.theme === 'dark' ? 'dark' : 'light'
}

export function applyTheme(theme: Theme): void {
    document.documentElement.dataset.theme = theme
    document.documentElement.dataset.bsTheme = theme
    try {
        localStorage.setItem(STORAGE_KEY, theme)
    } catch {
        // Private mode / blocked storage: the choice just won't persist.
    }
}

export function initTheme(): void {
    let stored: string | null = null
    try {
        stored = localStorage.getItem(STORAGE_KEY)
    } catch {
        // ignore
    }
    const prefersDark = window.matchMedia?.('(prefers-color-scheme: dark)').matches
    applyTheme(stored === 'dark' || (stored === null && prefersDark) ? 'dark' : 'light')
}
