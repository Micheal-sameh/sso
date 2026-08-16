import { useEffect } from 'react'
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
    open: boolean
    onClose: () => void
}

export default function Sidebar({ links, footerLinks, open, onClose }: SidebarProps) {
    const { url } = usePage()

    useEffect(() => {
        onClose()
        // eslint-disable-next-line react-hooks/exhaustive-deps
    }, [url])

    return (
        <>
            {open && <div className="sidebar-overlay d-lg-none" onClick={onClose} />}
            <aside className={`sidebar-main d-flex flex-column py-4 ${open ? 'sidebar-open' : ''}`}>
                <div className="sidebar-logo-row px-3 mb-4 d-flex align-items-center justify-content-between">
                    <AvaLogo size={36} showText textColor="#fff" />
                    <button
                        type="button"
                        className="btn-close btn-close-white d-lg-none"
                        aria-label="Close menu"
                        onClick={onClose}
                    />
                </div>
                <nav className="flex-grow-1">
                    {links.map((link) => (
                        <Link
                            key={link.href}
                            href={link.href}
                            className={`sidebar-link ${url.startsWith(link.href) ? 'active' : ''}`}
                        >
                            <i className={`bi ${link.icon}`} />
                            <span className="sidebar-link-label">{link.label}</span>
                        </Link>
                    ))}
                </nav>
                {footerLinks && footerLinks.length > 0 && (
                    <div className="border-top border-white border-opacity-10 pt-2 mt-2">
                        {footerLinks.map((link) => (
                            <Link key={link.href} href={link.href} className="sidebar-link">
                                <i className={`bi ${link.icon}`} />
                                <span className="sidebar-link-label">{link.label}</span>
                            </Link>
                        ))}
                    </div>
                )}
            </aside>
        </>
    )
}
