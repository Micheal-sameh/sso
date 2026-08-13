import type { ReactNode } from 'react'
import { router, usePage } from '@inertiajs/react'
import type { SharedProps } from '@/types'

interface TopBarProps {
    title?: string
    action?: ReactNode
}

export default function TopBar({ title, action }: TopBarProps) {
    const { auth } = usePage<SharedProps>().props

    const logout = () => {
        router.post('/logout')
    }

    return (
        <div className="d-flex justify-content-between align-items-center border-bottom bg-white px-4 py-3">
            <h4 className="mb-0">{title}</h4>
            <div className="d-flex align-items-center gap-2">
                {action}
                <div className="dropdown">
                    <button type="button" className="btn btn-outline-primary dropdown-toggle" data-bs-toggle="dropdown">
                        {auth.user?.name}
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
