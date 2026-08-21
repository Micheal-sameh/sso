import { useState } from 'react'
import type { ReactNode } from 'react'
import { usePage } from '@inertiajs/react'
import type { SharedProps } from '@/types'
import FlashMessage from '@/components/shared/FlashMessage'
import Sidebar, { type SidebarLink } from '@/components/layouts/Sidebar'
import TopBar from '@/components/layouts/TopBar'

interface AppLayoutProps {
    children: ReactNode
    title?: string
}

const NAV_LINKS: SidebarLink[] = [
    { href: '/account', label: 'Identity', icon: 'bi-person' },
    { href: '/account/mfa', label: 'Security', icon: 'bi-shield-lock' },
    { href: '/account/apps', label: 'Connected Apps', icon: 'bi-grid' },
    { href: '/notifications', label: 'Notifications', icon: 'bi-bell' },
    { href: '/cmc', label: 'CMC Posts', icon: 'bi-newspaper' },
]

const ADMIN_LINK: SidebarLink = { href: '/admin/users', label: 'Manage SSO', icon: 'bi-speedometer2' }
const CMC_POSTS_LINK: SidebarLink = { href: '/admin/cmc-posts', label: 'Manage CMC Posts', icon: 'bi-pencil-square' }

export default function AppLayout({ children, title }: AppLayoutProps) {
    const { auth } = usePage<SharedProps>().props
    const [sidebarOpen, setSidebarOpen] = useState(false)

    const footerLinks = auth.user?.is_admin
        ? [ADMIN_LINK]
        : auth.user?.can_manage_cmc_posts
          ? [CMC_POSTS_LINK]
          : undefined

    return (
        <div className="d-flex">
            <Sidebar
                links={NAV_LINKS}
                footerLinks={footerLinks}
                open={sidebarOpen}
                onClose={() => setSidebarOpen(false)}
            />

            <main className="flex-grow-1 main-content-area">
                <TopBar onToggleSidebar={() => setSidebarOpen((o) => !o)} />
                <div className="p-3 p-md-4 page-content">
                    <FlashMessage />
                    {title && <h3 className="mb-4">{title}</h3>}
                    {children}
                </div>
            </main>
        </div>
    )
}
