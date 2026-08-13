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
    { href: '/account', label: 'Profile', icon: 'bi-person' },
    { href: '/account/mfa', label: 'Security', icon: 'bi-shield-lock' },
    { href: '/account/apps', label: 'Connected Apps', icon: 'bi-grid' },
]

const ADMIN_LINK: SidebarLink = { href: '/admin/users', label: 'Manage SSO', icon: 'bi-speedometer2' }

export default function AppLayout({ children, title }: AppLayoutProps) {
    const { auth } = usePage<SharedProps>().props

    return (
        <div className="d-flex">
            <Sidebar links={NAV_LINKS} footerLinks={auth.user?.is_admin ? [ADMIN_LINK] : undefined} />

            <main className="flex-grow-1">
                <TopBar title={title} />
                <div className="p-4">
                    <FlashMessage />
                    {children}
                </div>
            </main>
        </div>
    )
}
