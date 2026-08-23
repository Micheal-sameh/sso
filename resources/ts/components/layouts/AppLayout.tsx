import { useState } from 'react'
import type { ReactNode } from 'react'
import { usePage } from '@inertiajs/react'
import { useTranslation } from 'react-i18next'
import type { SharedProps } from '@/types'
import BottomNav from '@/components/layouts/BottomNav'
import FlashMessage from '@/components/shared/FlashMessage'
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
        { href: '/home', label: t('nav.home'), icon: 'bi-house' },
        { href: '/account', label: t('nav.identity'), icon: 'bi-person' },
        { href: '/account/mfa', label: t('nav.security'), icon: 'bi-shield-lock' },
        { href: '/account/apps', label: t('nav.connectedApps'), icon: 'bi-grid' },
        { href: '/notifications', label: t('nav.notifications'), icon: 'bi-bell' },
        { href: '/cmc', label: t('nav.cmcPosts'), icon: 'bi-newspaper' },
        { href: '/videos', label: t('nav.videos'), icon: 'bi-play-btn' },
        { href: '/donations', label: t('nav.donate'), icon: 'bi-heart' },
        { href: '/donations/history', label: t('nav.myDonations'), icon: 'bi-receipt' },
    ]

    const bottomNavLinks: SidebarLink[] = [
        { href: '/account/apps', label: t('nav.apps'), icon: 'bi-grid' },
        { href: '/cmc', label: t('nav.cmc'), icon: 'bi-newspaper' },
        { href: '/home', label: t('nav.home'), icon: 'bi-house' },
        { href: '/donations', label: t('nav.donate'), icon: 'bi-heart' },
        { href: '/videos', label: t('nav.videos'), icon: 'bi-play-btn' },
    ]

    const adminLink: SidebarLink = { href: '/admin/users', label: t('nav.manageSso'), icon: 'bi-speedometer2' }
    const cmcPostsLink: SidebarLink = { href: '/admin/cmc-posts', label: t('nav.manageCmcPosts'), icon: 'bi-pencil-square' }
    const videosLink: SidebarLink = { href: '/admin/videos', label: t('nav.manageVideos'), icon: 'bi-play-btn' }

    const footerLinks = auth.user?.is_admin
        ? [adminLink]
        : auth.user?.can_manage_cmc_posts
          ? [cmcPostsLink, videosLink]
          : undefined

    return (
        <div className="d-flex">
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
