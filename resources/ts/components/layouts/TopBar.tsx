import { router, usePage } from '@inertiajs/react'
import type { SharedProps } from '@/types'

interface TopBarProps {
    onToggleSidebar: () => void
}

export default function TopBar({ onToggleSidebar }: TopBarProps) {
    const { auth } = usePage<SharedProps>().props

    const logout = () => {
        router.post('/logout')
    }

    return (
        <div className="topbar d-flex justify-content-between align-items-center border-bottom px-3 px-md-4 py-3 gap-2">
            <button
                type="button"
                className="btn btn-outline-primary btn-sm d-lg-none"
                aria-label="Open menu"
                onClick={onToggleSidebar}
            >
                <i className="bi bi-list" />
            </button>
            <div className="ms-auto dropdown">
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
    )
}
