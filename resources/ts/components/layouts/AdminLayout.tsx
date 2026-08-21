import { useState } from 'react'
import type { ReactNode } from 'react'
import { usePage } from '@inertiajs/react'
import type { SharedProps } from '@/types'
import FlashMessage from '@/components/shared/FlashMessage'
import Sidebar, { type SidebarLink } from '@/components/layouts/Sidebar'
import TopBar from '@/components/layouts/TopBar'

interface AdminLayoutProps {
    children: ReactNode
    title?: string
    action?: ReactNode
}

const FULL_NAV_LINKS: SidebarLink[] = [
    { href: '/admin/users', label: 'Users', icon: 'bi-people' },
    { href: '/admin/admins', label: 'SSO Admins', icon: 'bi-shield-check' },
    { href: '/admin/stages', label: 'Stages', icon: 'bi-signpost-split' },
    { href: '/admin/groups', label: 'Groups', icon: 'bi-collection' },
    { href: '/admin/clients', label: 'OAuth Clients', icon: 'bi-key' },
    { href: '/admin/tokens', label: 'Tokens', icon: 'bi-ticket-perforated' },
    { href: '/admin/notifications', label: 'Notifications', icon: 'bi-bell' },
    { href: '/admin/cmc-posts', label: 'CMC Posts', icon: 'bi-newspaper' },
]

const CMC_ONLY_NAV_LINKS: SidebarLink[] = [
    { href: '/admin/cmc-posts', label: 'CMC Posts', icon: 'bi-newspaper' },
]

const ACCOUNT_LINK: SidebarLink = { href: '/account', label: 'Back to Account', icon: 'bi-arrow-left' }

export default function AdminLayout({ children, title, action }: AdminLayoutProps) {
    const [sidebarOpen, setSidebarOpen] = useState(false)
    const { auth } = usePage<SharedProps>().props
    const navLinks = auth.user?.is_admin ? FULL_NAV_LINKS : CMC_ONLY_NAV_LINKS

    return (
        <div className="d-flex">
            <Sidebar links={navLinks} footerLinks={[ACCOUNT_LINK]} open={sidebarOpen} onClose={() => setSidebarOpen(false)} />

            <main className="flex-grow-1 main-content-area">
                <TopBar onToggleSidebar={() => setSidebarOpen((o) => !o)} />
                <div className="p-3 p-md-4 page-content">
                    <FlashMessage />
                    {(title || action) && (
                        <div className="d-flex flex-wrap justify-content-between align-items-center gap-2 mb-4">
                            {title && <h3 className="mb-0">{title}</h3>}
                            {action && <div className="d-flex flex-wrap gap-2">{action}</div>}
                        </div>
                    )}
                    {children}
                </div>
            </main>
        </div>
    )
}
