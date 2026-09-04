import { useEffect, useState } from 'react'
import { Link, usePage } from '@inertiajs/react'
import { useTranslation } from 'react-i18next'
import AvaLogo from '@/components/shared/AvaLogo'

export interface SidebarLink {
    href: string
    label: string
    icon: string
}

export interface SidebarGroup {
    id: string
    label: string
    links: SidebarLink[]
}

interface SidebarProps {
    groups: SidebarGroup[]
    footerLinks?: SidebarLink[]
    open: boolean
    onClose: () => void
}

const COLLAPSED_GROUPS_KEY = 'admin-sidebar-collapsed-groups'

function readCollapsedGroups(): Record<string, boolean> {
    try {
        const raw = localStorage.getItem(COLLAPSED_GROUPS_KEY)
        return raw ? JSON.parse(raw) : {}
    } catch {
        return {}
    }
}

export default function Sidebar({ groups, footerLinks, open, onClose }: SidebarProps) {
    const { t } = useTranslation('common')
    const { url } = usePage()
    const [collapsed, setCollapsed] = useState<Record<string, boolean>>(readCollapsedGroups)

    useEffect(() => {
        onClose()
        // eslint-disable-next-line react-hooks/exhaustive-deps
    }, [url])

    const toggleGroup = (id: string) => {
        setCollapsed((prev) => {
            const next = { ...prev, [id]: !prev[id] }
            try {
                localStorage.setItem(COLLAPSED_GROUPS_KEY, JSON.stringify(next))
            } catch {
                // localStorage unavailable, ignore
            }
            return next
        })
    }

    return (
        <>
            {open && <div className="sidebar-overlay d-lg-none" onClick={onClose} />}
            <aside className={`sidebar-main d-flex flex-column py-4 ${open ? 'sidebar-open' : ''}`}>
                <div className="sidebar-logo-row px-3 mb-4 d-flex align-items-center justify-content-between">
                    <Link href="/" className="text-decoration-none">
                        <AvaLogo size={36} />
                    </Link>
                    <button
                        type="button"
                        className="btn-close d-lg-none"
                        aria-label={t('sidebar.closeMenu')}
                        onClick={onClose}
                    />
                </div>
                <nav className="flex-grow-1">
                    {groups.map((group) => {
                        const isCollapsed = !!collapsed[group.id]
                        const items = group.links.map((link) => (
                            <Link
                                key={link.href}
                                href={link.href}
                                className={`sidebar-link ${url.startsWith(link.href) ? 'active' : ''}`}
                            >
                                <i className={`bi ${link.icon}`} />
                                <span className="sidebar-link-label">{link.label}</span>
                            </Link>
                        ))

                        if (!group.label) {
                            return <div key={group.id}>{items}</div>
                        }

                        return (
                            <div key={group.id} className={`sidebar-group ${isCollapsed ? 'collapsed' : ''}`}>
                                <button
                                    type="button"
                                    className="sidebar-group-header"
                                    onClick={() => toggleGroup(group.id)}
                                    aria-expanded={!isCollapsed}
                                >
                                    <span className="sidebar-group-label">{group.label}</span>
                                    <i className={`bi bi-chevron-down sidebar-group-chevron`} />
                                </button>
                                <div className="sidebar-group-items">{items}</div>
                            </div>
                        )
                    })}
                </nav>
                {footerLinks && footerLinks.length > 0 && (
                    <div className="border-top pt-2 mt-2">
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
