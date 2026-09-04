import { Link, router, usePage } from '@inertiajs/react'
import { useTranslation } from 'react-i18next'
import LanguageSwitcher from '@/components/shared/LanguageSwitcher'
import { useConfirm } from '@/components/shared/ConfirmDialog'
import type { SharedProps } from '@/types'

interface TopBarProps {
    onToggleSidebar: () => void
}

export default function TopBar({ onToggleSidebar }: TopBarProps) {
    const { t } = useTranslation('common')
    const { auth } = usePage<SharedProps>().props
    const confirm = useConfirm()

    const logout = async () => {
        if (await confirm(t('topbar.logOutConfirm'), { confirmLabel: t('topbar.logOut'), variant: 'danger' })) {
            router.post('/logout')
        }
    }

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
            <div className="dropdown ms-auto">
                <button type="button" className="btn btn-outline-primary dropdown-toggle" data-bs-toggle="dropdown">
                    <span className="d-none d-sm-inline">{auth.user?.name}</span>
                    <i className="bi bi-person-circle d-sm-none" />
                </button>
                <ul className="dropdown-menu dropdown-menu-end">
                    <li>
                        <Link href="/account" className="dropdown-item">
                            <i className="bi bi-person me-2" />
                            {t('nav.profile')}
                        </Link>
                    </li>
                    <li>
                        <hr className="dropdown-divider" />
                    </li>
                    <li>
                        <button type="button" className="dropdown-item" onClick={logout}>
                            <i className="bi bi-box-arrow-right me-2" />
                            {t('topbar.logOut')}
                        </button>
                    </li>
                </ul>
            </div>
        </div>
    )
}
