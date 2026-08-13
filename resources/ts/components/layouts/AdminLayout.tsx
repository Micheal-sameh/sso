import type { ReactNode } from 'react'
import FlashMessage from '@/components/shared/FlashMessage'
import Sidebar, { type SidebarLink } from '@/components/layouts/Sidebar'
import TopBar from '@/components/layouts/TopBar'

interface AdminLayoutProps {
    children: ReactNode
    title?: string
    action?: ReactNode
}

const NAV_LINKS: SidebarLink[] = [
    { href: '/admin/users', label: 'Users', icon: 'bi-people' },
    { href: '/admin/clients', label: 'OAuth Clients', icon: 'bi-key' },
    { href: '/admin/tokens', label: 'Tokens', icon: 'bi-ticket-perforated' },
]

const ACCOUNT_LINK: SidebarLink = { href: '/account', label: 'Back to Account', icon: 'bi-arrow-left' }

export default function AdminLayout({ children, title, action }: AdminLayoutProps) {
    return (
        <div className="d-flex">
            <Sidebar links={NAV_LINKS} footerLinks={[ACCOUNT_LINK]} />

            <main className="flex-grow-1">
                <TopBar title={title} action={action} />
                <div className="p-4">
                    <FlashMessage />
                    {children}
                </div>
            </main>
        </div>
    )
}
