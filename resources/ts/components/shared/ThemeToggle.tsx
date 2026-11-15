import { useState } from 'react'
import { useTranslation } from 'react-i18next'
import { applyTheme, currentTheme, type Theme } from '@/lib/theme'

export default function ThemeToggle() {
    const { t } = useTranslation('common')
    const [theme, setTheme] = useState<Theme>(currentTheme)

    const toggle = () => {
        const next: Theme = theme === 'dark' ? 'light' : 'dark'
        applyTheme(next)
        setTheme(next)
    }

    return (
        <button
            type="button"
            className="btn btn-outline-primary btn-sm theme-toggle"
            aria-label={t('theme.toggle')}
            onClick={toggle}
        >
            <i className={`bi ${theme === 'dark' ? 'bi-sun' : 'bi-moon'}`} />
        </button>
    )
}
