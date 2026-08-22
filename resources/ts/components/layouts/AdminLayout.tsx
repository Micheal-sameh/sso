import { useState } from 'react'
import type { ReactNode } from 'react'
import { usePage } from '@inertiajs/react'
import { useTranslation } from 'react-i18next'
import type { SharedProps } from '@/types'
import FlashMessage from '@/components/shared/FlashMessage'
import Sidebar, { type SidebarLink } from '@/components/layouts/Sidebar'
import TopBar from '@/components/layouts/TopBar'

interface AdminLayoutProps {
    children: ReactNode
    title?: string
    action?: ReactNode
}

export default function AdminLayout({ children, title, action }: AdminLayoutProps) {
    const { t } = useTranslation('common')
    const [sidebarOpen, setSidebarOpen] = useState(false)
    const { auth } = usePage<SharedProps>().props

    const fullNavLinks: SidebarLink[] = [
        { href: '/admin/users', label: t('nav.users'), icon: 'bi-people' },
        { href: '/admin/admins', label: t('nav.ssoAdmins'), icon: 'bi-shield-check' },
        { href: '/admin/stages', label: t('nav.stages'), icon: 'bi-signpost-split' },
        { href: '/admin/groups', label: t('nav.groups'), icon: 'bi-collection' },
        { href: '/admin/clients', label: t('nav.oauthClients'), icon: 'bi-key' },
        { href: '/admin/tokens', label: t('nav.tokens'), icon: 'bi-ticket-perforated' },
        { href: '/admin/notifications', label: t('nav.notifications'), icon: 'bi-bell' },
        { href: '/admin/cmc-posts', label: t('nav.cmcPosts'), icon: 'bi-newspaper' },
    ]

    const cmcOnlyNavLinks: SidebarLink[] = [
        { href: '/admin/cmc-posts', label: t('nav.cmcPosts'), icon: 'bi-newspaper' },
    ]

    const accountLink: SidebarLink = { href: '/account', label: t('nav.backToAccount'), icon: 'bi-arrow-left' }

    const navLinks = auth.user?.is_admin ? fullNavLinks : cmcOnlyNavLinks

    return (
        <div className="d-flex">
            <Sidebar links={navLinks} footerLinks={[accountLink]} open={sidebarOpen} onClose={() => setSidebarOpen(false)} />

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
