import type { ReactNode } from 'react'
import { router, usePage } from '@inertiajs/react'
import type { SharedProps } from '@/types'

interface TopBarProps {
    title?: string
    action?: ReactNode
    onToggleSidebar: () => void
}

export default function TopBar({ title, action, onToggleSidebar }: TopBarProps) {
    const { auth } = usePage<SharedProps>().props

    const logout = () => {
        router.post('/logout')
    }

    return (
        <div className="topbar d-flex justify-content-between align-items-center border-bottom px-3 px-md-4 py-3 gap-2">
            <div className="d-flex align-items-center gap-2 min-w-0">
                <button
                    type="button"
                    className="btn btn-outline-primary btn-sm d-lg-none flex-shrink-0"
                    aria-label="Open menu"
                    onClick={onToggleSidebar}
                >
                    <i className="bi bi-list" />
                </button>
                <h4 className="mb-0 text-truncate">{title}</h4>
            </div>
            <div className="d-flex align-items-center gap-2 flex-shrink-0">
                {action}
                <div className="dropdown">
                    <button type="button" className="btn btn-outline-primary dropdown-toggle" data-bs-toggle="dropdown">
                        <span className="d-none d-sm-inline">{auth.user?.name}</span>
                        <i className="bi bi-person-circle d-sm-none" />
                    </button>
                    <ul className="dropdown-menu dropdown-menu-end">
                        <li>
                            <button type="button" className="dropdown-item" onClick={logout}>
                                Log out
                            </button>
                        </li>
                    </ul>
                </div>
            </div>
        </div>
    )
}
