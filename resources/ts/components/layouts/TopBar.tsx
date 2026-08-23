import { useEffect, useState } from 'react'
import { Link, router, usePage } from '@inertiajs/react'
import { useTranslation } from 'react-i18next'
import echo from '@/lib/echo'
import LanguageSwitcher from '@/components/shared/LanguageSwitcher'
import { useConfirm } from '@/components/shared/ConfirmDialog'
import type { SharedProps, UserNotification } from '@/types'

interface TopBarProps {
    onToggleSidebar: () => void
}

export default function TopBar({ onToggleSidebar }: TopBarProps) {
    const { t } = useTranslation('common')
    const { auth, unread_notifications_count, recent_notifications } = usePage<SharedProps>().props
    const [unreadCount, setUnreadCount] = useState(unread_notifications_count)
    const [notifications, setNotifications] = useState(recent_notifications)
    const confirm = useConfirm()

    useEffect(() => {
        setUnreadCount(unread_notifications_count)
        setNotifications(recent_notifications)
    }, [unread_notifications_count, recent_notifications])

    useEffect(() => {
        const userId = auth.user?.id
        if (!userId) return

        const channel = echo.private(`App.Models.User.${userId}`)
        channel.listen('.notification.sent', ({ notification }: { notification: UserNotification }) => {
            setUnreadCount((count) => count + 1)
            setNotifications((current) => [notification, ...current].slice(0, 5))
        })

        return () => {
            echo.leave(`App.Models.User.${userId}`)
        }
    }, [auth.user?.id])

    const logout = async () => {
        if (await confirm(t('topbar.logOutConfirm'), { confirmLabel: t('topbar.logOut'), variant: 'danger' })) {
            router.post('/logout')
        }
    }

    const markRead = (notification: UserNotification) => {
        if (!notification.is_read) {
            router.post(`/notifications/${notification.id}/read`, {}, { preserveScroll: true })
        }
    }

    const openNotification = (notification: UserNotification) => {
        markRead(notification)
        if (notification.cmc_post_id) {
            router.visit(`/cmc/${notification.cmc_post_id}`)
        }
    }

    const markAllRead = () => {
        setUnreadCount(0)
        setNotifications((current) => current.map((notification) => ({ ...notification, is_read: true })))
        router.post('/notifications/read-all', {}, { preserveScroll: true })
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
            <div className="dropdown ms-auto">
                <button
                    type="button"
                    className="btn btn-outline-primary position-relative"
                    aria-label={t('topbar.notifications')}
                    data-bs-toggle="dropdown"
                >
                    <i className="bi bi-bell" />
                    {unreadCount > 0 && (
                        <span className="position-absolute top-0 start-100 translate-middle badge rounded-pill bg-danger">
                            {unreadCount > 9 ? '9+' : unreadCount}
                        </span>
                    )}
                </button>
                <div className="dropdown-menu dropdown-menu-end notification-dropdown-menu p-0">
                    <div className="px-3 py-2 border-bottom d-flex justify-content-between align-items-center">
                        <span className="fw-semibold">{t('topbar.notifications')}</span>
                        {unreadCount > 0 && (
                            <button type="button" className="btn btn-link btn-sm p-0 text-ava-secondary" onClick={markAllRead}>
                                {t('topbar.markAllAsRead')}
                            </button>
                        )}
                    </div>
                    {notifications.length === 0 ? (
                        <div className="px-3 py-4 text-center text-ava-muted text-tiny">{t('topbar.noNotificationsYet')}</div>
                    ) : (
                        <ul className="list-unstyled mb-0">
                            {notifications.map((notification) => (
                                <li key={notification.id}>
                                    <button
                                        type="button"
                                        className={`dropdown-item d-flex gap-2 py-2 px-3 ${notification.is_read ? '' : 'notification-unread'}`}
                                        onClick={() => openNotification(notification)}
                                    >
                                        {notification.image_url && (
                                            <img
                                                src={notification.image_url}
                                                alt=""
                                                width={36}
                                                height={36}
                                                className="rounded object-fit-cover flex-shrink-0"
                                            />
                                        )}
                                        <span className="min-w-0">
                                            <span className={`d-block text-truncate-2 ${notification.is_read ? '' : 'fw-semibold'}`}>
                                                {notification.message}
                                            </span>
                                            <span className="d-block text-ava-muted text-tiny">{notification.created_at}</span>
                                        </span>
                                    </button>
                                </li>
                            ))}
                        </ul>
                    )}
                    <Link href="/notifications" className="dropdown-item text-center text-ava-secondary border-top py-2">
                        {t('topbar.seeAllNotifications')}
                    </Link>
                </div>
            </div>
            <LanguageSwitcher />
            <div className="dropdown">
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
