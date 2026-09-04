import { useState } from 'react'
import type { ReactNode } from 'react'
import { usePage } from '@inertiajs/react'
import { useTranslation } from 'react-i18next'
import type { SharedProps } from '@/types'
import BottomNav from '@/components/layouts/BottomNav'
import FlashMessage from '@/components/shared/FlashMessage'
import PullToRefresh from '@/components/shared/PullToRefresh'
import Sidebar, { type SidebarLink } from '@/components/layouts/Sidebar'
import TopBar from '@/components/layouts/TopBar'

interface AppLayoutProps {
    children: ReactNode
    title?: string
}

export default function AppLayout({ children, title }: AppLayoutProps) {
    const { t } = useTranslation('common')
    const { auth } = usePage<SharedProps>().props
    const [sidebarOpen, setSidebarOpen] = useState(false)

    const navLinks: SidebarLink[] = [
        { href: '/account', label: t('nav.identity'), icon: 'bi-person' },
        { href: '/account/mfa', label: t('nav.security'), icon: 'bi-shield-lock' },
        { href: '/account/apps', label: t('nav.connectedApps'), icon: 'bi-grid' },
    ]

    const bottomNavLinks: SidebarLink[] = [
        { href: '/more', label: t('nav.more'), icon: 'bi-grid' },
    ]

    const adminLink: SidebarLink = { href: '/admin/users', label: t('nav.manageSso'), icon: 'bi-speedometer2' }

    const footerLinks = auth.user?.is_admin ? [adminLink] : undefined

    return (
        <div className="d-flex">
            <PullToRefresh />
            <Sidebar
                groups={[{ id: 'main', label: '', links: navLinks }]}
                footerLinks={footerLinks}
                open={sidebarOpen}
                onClose={() => setSidebarOpen(false)}
            />

            <main className="flex-grow-1 main-content-area has-bottom-nav">
                <TopBar onToggleSidebar={() => setSidebarOpen((o) => !o)} />
                <div className="p-3 p-md-4 page-content">
                    <FlashMessage />
                    {title && <h3 className="mb-4">{title}</h3>}
                    {children}
                </div>
            </main>

            <BottomNav links={bottomNavLinks} />
        </div>
    )
}
