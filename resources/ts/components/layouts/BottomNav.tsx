import { Link, usePage } from '@inertiajs/react'

export interface BottomNavLink {
    href: string
    label: string
    icon: string
}

interface BottomNavProps {
    links: BottomNavLink[]
}

export default function BottomNav({ links }: BottomNavProps) {
    const { url } = usePage()

    const isActive = (href: string) => (href === '/' ? url === '/' : url.startsWith(href))

    return (
        <nav className="bottom-nav d-lg-none">
            {links.map((link) => (
                <Link key={link.href} href={link.href} className={`bottom-nav-link ${isActive(link.href) ? 'active' : ''}`}>
                    <i className={`bi ${link.icon}`} />
                    <span>{link.label}</span>
                </Link>
            ))}
        </nav>
    )
}
