import { usePage } from '@inertiajs/react'
import { useTranslation } from 'react-i18next'
import LanguageSwitcher from '@/components/shared/LanguageSwitcher'
import UserMenu from '@/components/shared/UserMenu'
import type { SharedProps } from '@/types'

interface TopBarProps {
    onToggleSidebar: () => void
}

export default function TopBar({ onToggleSidebar }: TopBarProps) {
    const { t } = useTranslation('common')
    const { auth } = usePage<SharedProps>().props

    return (
        <div className="topbar d-flex justify-content-between align-items-center border-bottom px-3 px-md-4 py-3 gap-2">
            {auth.user?.is_admin && (
                <button
                    type="button"
                    className="btn btn-outline-primary btn-sm d-lg-none"
                    aria-label={t('topbar.openMenu')}
                    onClick={onToggleSidebar}
                >
                    <i className="bi bi-list" />
                </button>
            )}
            <LanguageSwitcher />
            <div className="ms-auto">
                <UserMenu />
            </div>
        </div>
    )
}
