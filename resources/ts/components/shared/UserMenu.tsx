import { Link, router, usePage } from '@inertiajs/react'
import { useTranslation } from 'react-i18next'
import AvatarInitials from '@/components/shared/AvatarInitials'
import type { SharedProps } from '@/types'

/**
 * Top-right account menu: profile summary, the accounts signed in on this
 * browser (switch between them), add another account, and sign out.
 */
export default function UserMenu() {
    const { t } = useTranslation('common')
    const { auth, csrf_token, other_accounts } = usePage<SharedProps>().props
    const user = auth.user

    if (!user) {
        return null
    }

    // Plain "user" accounts have no profile page (opening it signs them out).
    const hasProfilePage = user.role !== 'user'

    return (
        <div className="dropdown">
            <button
                type="button"
                className="btn btn-link p-0 border-0 d-flex align-items-center gap-2 text-decoration-none"
                data-bs-toggle="dropdown"
                aria-expanded="false"
                aria-label={t('userMenu.open')}
            >
                {user.avatar ? (
                    <img src={user.avatar} alt="" width={36} height={36} className="rounded-circle" />
                ) : (
                    <AvatarInitials name={user.name} size={36} />
                )}
                <span className="d-none d-sm-inline fw-semibold text-body">{user.name}</span>
                <i className="bi bi-chevron-down small text-ava-muted" />
            </button>

            <ul className="dropdown-menu dropdown-menu-end shadow" style={{ minWidth: 280 }}>
                <li className="px-3 py-2">
                    <div className="d-flex align-items-center gap-2">
                        {user.avatar ? (
                            <img src={user.avatar} alt="" width={44} height={44} className="rounded-circle" />
                        ) : (
                            <AvatarInitials name={user.name} size={44} />
                        )}
                        <div className="min-w-0">
                            <div className="fw-semibold text-truncate">{user.name}</div>
                            {user.email && <div className="small text-ava-muted text-truncate">{user.email}</div>}
                            <div className="small text-ava-muted">{user.membership_code.toUpperCase()}</div>
                        </div>
                    </div>
                </li>
                {hasProfilePage && (
                    <li>
                        <Link href="/account" className="dropdown-item">
                            <i className="bi bi-person me-2" />
                            {t('nav.profile')}
                        </Link>
                    </li>
                )}

                <li><hr className="dropdown-divider" /></li>
                <li><h6 className="dropdown-header">{t('userMenu.accounts')}</h6></li>
                <li>
                    <span className="dropdown-item-text d-flex align-items-center gap-2">
                        <i className="bi bi-check-circle-fill text-ava-secondary" />
                        <span className="text-truncate">{user.name}</span>
                        <span className="small text-ava-muted ms-auto">{t('userMenu.current')}</span>
                    </span>
                </li>
                {other_accounts.map((account) => (
                    <li key={account.id}>
                        <form method="POST" action="/accounts/switch">
                            <input type="hidden" name="_token" value={csrf_token} />
                            <input type="hidden" name="user_id" value={account.id} />
                            <button type="submit" className="dropdown-item d-flex align-items-center gap-2 py-2">
                                <AvatarInitials name={account.name} size={32} />
                                <span className="min-w-0">
                                    <span className="d-block fw-semibold text-truncate">{account.name}</span>
                                    <span className="d-block small text-ava-muted text-truncate">{account.membership_code.toUpperCase()}</span>
                                </span>
                            </button>
                        </form>
                    </li>
                ))}
                <li>
                    <form method="POST" action="/accounts/add">
                        <input type="hidden" name="_token" value={csrf_token} />
                        <button type="submit" className="dropdown-item">
                            <i className="bi bi-person-plus me-2" />
                            {t('userMenu.addAccount')}
                        </button>
                    </form>
                </li>

                <li><hr className="dropdown-divider" /></li>
                <li>
                    <button type="button" className="dropdown-item" onClick={() => router.post('/logout')}>
                        <i className="bi bi-box-arrow-right me-2" />
                        {t('userMenu.signOutAll')}
                    </button>
                </li>
            </ul>
        </div>
    )
}
