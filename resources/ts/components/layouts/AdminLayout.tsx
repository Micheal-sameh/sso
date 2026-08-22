import { useState } from 'react'
import type { ReactNode } from 'react'
import { usePage } from '@inertiajs/react'
import { useTranslation } from 'react-i18next'
import type { SharedProps } from '@/types'
import FlashMessage from '@/components/shared/FlashMessage'
import Sidebar, { type SidebarGroup, type SidebarLink } from '@/components/layouts/Sidebar'
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

    const fullNavGroups: SidebarGroup[] = [
        {
            id: 'people',
            label: t('nav.groupPeople'),
            links: [
                { href: '/admin/users', label: t('nav.users'), icon: 'bi-people' },
                { href: '/admin/admins', label: t('nav.ssoAdmins'), icon: 'bi-shield-check' },
                { href: '/admin/groups', label: t('nav.groups'), icon: 'bi-collection' },
            ],
        },
        {
            id: 'access-security',
            label: t('nav.groupAccessSecurity'),
            links: [
                { href: '/admin/clients', label: t('nav.oauthClients'), icon: 'bi-key' },
                { href: '/admin/tokens', label: t('nav.tokens'), icon: 'bi-ticket-perforated' },
                { href: '/admin/stages', label: t('nav.stages'), icon: 'bi-signpost-split' },
            ],
        },
        {
            id: 'content',
            label: t('nav.groupContent'),
            links: [
                { href: '/admin/cmc-posts', label: t('nav.cmcPosts'), icon: 'bi-newspaper' },
                { href: '/admin/videos', label: t('nav.videos'), icon: 'bi-play-btn' },
                { href: '/admin/notifications', label: t('nav.notifications'), icon: 'bi-bell' },
            ],
        },
        {
            id: 'finance',
            label: t('nav.groupFinance'),
            links: [
                { href: '/admin/donations', label: t('nav.donations'), icon: 'bi-heart' },
                { href: '/admin/funds', label: t('nav.funds'), icon: 'bi-piggy-bank' },
            ],
        },
    ]

    const cmcOnlyNavGroups: SidebarGroup[] = [
        {
            id: 'content',
            label: t('nav.groupContent'),
            links: [
                { href: '/admin/cmc-posts', label: t('nav.cmcPosts'), icon: 'bi-newspaper' },
                { href: '/admin/videos', label: t('nav.videos'), icon: 'bi-play-btn' },
            ],
        },
    ]

    const accountLink: SidebarLink = { href: '/account', label: t('nav.backToAccount'), icon: 'bi-arrow-left' }

    const navGroups = auth.user?.is_admin ? fullNavGroups : cmcOnlyNavGroups

    return (
        <div className="d-flex">
            <Sidebar groups={navGroups} footerLinks={[accountLink]} open={sidebarOpen} onClose={() => setSidebarOpen(false)} />

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
