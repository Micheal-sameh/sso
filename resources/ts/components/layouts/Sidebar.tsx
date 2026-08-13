import { Link, usePage } from '@inertiajs/react'
import AvaLogo from '@/components/shared/AvaLogo'

export interface SidebarLink {
    href: string
    label: string
    icon: string
}

interface SidebarProps {
    links: SidebarLink[]
    footerLinks?: SidebarLink[]
}

export default function Sidebar({ links, footerLinks }: SidebarProps) {
    const { url } = usePage()

    return (
        <aside className="sidebar-main d-flex flex-column py-4">
            <div className="px-3 mb-4">
                <AvaLogo size={36} showText textColor="#fff" />
            </div>
            <nav className="flex-grow-1">
                {links.map((link) => (
                    <Link
                        key={link.href}
                        href={link.href}
                        className={`sidebar-link ${url.startsWith(link.href) ? 'active' : ''}`}
                    >
                        <i className={`bi ${link.icon}`} />
                        {link.label}
                    </Link>
                ))}
            </nav>
            {footerLinks && footerLinks.length > 0 && (
                <div className="border-top border-white border-opacity-10 pt-2 mt-2">
                    {footerLinks.map((link) => (
                        <Link key={link.href} href={link.href} className="sidebar-link">
                            <i className={`bi ${link.icon}`} />
                            {link.label}
                        </Link>
                    ))}
                </div>
            )}
        </aside>
    )
}
