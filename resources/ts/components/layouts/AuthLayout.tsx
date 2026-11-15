import type { ReactNode } from 'react'
import { useTranslation } from 'react-i18next'
import FlashMessage from '@/components/shared/FlashMessage'
import LanguageSwitcher from '@/components/shared/LanguageSwitcher'
import ThemeToggle from '@/components/shared/ThemeToggle'

interface AuthLayoutProps {
    children: ReactNode
    wide?: boolean
}

export default function AuthLayout({ children, wide = false }: AuthLayoutProps) {
    const { t } = useTranslation('common')

    return (
        <div className="auth-wrapper">
            <div className={`${wide ? 'auth-card-wide' : 'auth-card'} page-content`}>
                <div className="d-flex justify-content-end gap-2 mb-2">
                    <ThemeToggle />
                    <LanguageSwitcher />
                </div>
                <FlashMessage />
                <div className="ava-card">
                    <div className="card-accent card-accent-secondary" />
                    <div className="p-4 p-md-5">{children}</div>
                </div>
                <div className="trust-note">
                    <i className="bi bi-shield-check" />
                    {t('brand.securedBy')}
                </div>
            </div>
        </div>
    )
}
