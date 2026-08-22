import type { ReactNode } from 'react'
import FlashMessage from '@/components/shared/FlashMessage'
import LanguageSwitcher from '@/components/shared/LanguageSwitcher'

interface AuthLayoutProps {
    children: ReactNode
    wide?: boolean
}

export default function AuthLayout({ children, wide = false }: AuthLayoutProps) {
    return (
        <div className="auth-wrapper">
            <div className={`${wide ? 'auth-card-wide' : 'auth-card'} page-content`}>
                <div className="d-flex justify-content-end mb-2">
                    <LanguageSwitcher />
                </div>
                <FlashMessage />
                <div className="ava-card">
                    <div className="card-accent card-accent-secondary" />
                    <div className="p-4 p-md-5">{children}</div>
                </div>
            </div>
        </div>
    )
}
