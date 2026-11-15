import { useState } from 'react'
import type { ReactNode } from 'react'
import { useTranslation } from 'react-i18next'
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

    const navGroups: SidebarGroup[] = [
        {
            id: 'people',
            label: t('nav.groupPeople'),
            links: [
                { href: '/admin/users', label: t('nav.users'), icon: 'bi-people' },
                { href: '/admin/birthdays', label: t('nav.birthdays'), icon: 'bi-cake2' },
                { href: '/admin/admins', label: t('nav.ssoAdmins'), icon: 'bi-shield-check' },
                { href: '/admin/groups', label: t('nav.groups'), icon: 'bi-collection' },
                { href: '/admin/families', label: t('nav.families'), icon: 'bi-house-heart' },
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
    ]

    const accountLink: SidebarLink = { href: '/account', label: t('nav.backToAccount'), icon: 'bi-arrow-left' }

    return (
        <div className="d-flex">
            <Sidebar groups={navGroups} footerLinks={[accountLink]} open={sidebarOpen} onClose={() => setSidebarOpen(false)} />

            <main className="flex-grow-1 main-content-area">
                <TopBar onToggleSidebar={() => setSidebarOpen((o) => !o)} />
                <div className="p-3 p-md-4 page-content">
                    <FlashMessage />
                    {(title || action) && (
                        <div className="d-flex flex-wrap justify-content-between align-items-center gap-2 mb-4">
                            {title && (
                                <div>
                                    <span className="eyebrow admin-eyebrow">{t('nav.adminConsole')}</span>
                                    <h3 className="mb-0">{title}</h3>
                                </div>
                            )}
                            {action && <div className="d-flex flex-wrap gap-2">{action}</div>}
                        </div>
                    )}
                    {children}
                </div>
            </main>
        </div>
    )
}
