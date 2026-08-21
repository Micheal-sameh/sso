import { router } from '@inertiajs/react'
import { useEffect, useState } from 'react'
import AppLayout from '@/components/layouts/AppLayout'
import Pagination from '@/components/shared/Pagination'
import type { PaginatedData, UserNotification } from '@/types'

interface NotificationsProps {
    notifications: PaginatedData<UserNotification>
}

export default function Notifications({ notifications }: NotificationsProps) {
    const [selectedIds, setSelectedIds] = useState<number[]>([])

    useEffect(() => {
        setSelectedIds([])
    }, [notifications])

    const markRead = (notification: UserNotification) => {
        router.post(`/notifications/${notification.id}/read`, {}, { preserveScroll: true })
    }

    const markAllRead = () => {
        router.post('/notifications/read-all', {}, { preserveScroll: true })
    }

    const markSelectedRead = () => {
        if (selectedIds.length === 0) return

        router.post(
            '/notifications/read-many',
            { ids: selectedIds },
            { preserveScroll: true, onSuccess: () => setSelectedIds([]) },
        )
    }

    const toggleSelected = (notification: UserNotification, checked: boolean) => {
        setSelectedIds((current) =>
            checked ? [...current, notification.id] : current.filter((id) => id !== notification.id),
        )
    }

    const unreadOnPage = notifications.data.filter((notification) => !notification.is_read)
    const hasUnread = unreadOnPage.length > 0
    const allUnreadSelected = hasUnread && unreadOnPage.every((notification) => selectedIds.includes(notification.id))

    const toggleSelectAll = (checked: boolean) => {
        setSelectedIds(checked ? unreadOnPage.map((notification) => notification.id) : [])
    }

    return (
        <AppLayout title="Notifications">
            <div className="ava-card">
                {hasUnread && (
                    <div className="d-flex align-items-center justify-content-between gap-2 p-3 pb-0">
                        <div className="form-check mb-0">
                            <input
                                type="checkbox"
                                className="form-check-input"
                                id="select-all-unread"
                                checked={allUnreadSelected}
                                onChange={(e) => toggleSelectAll(e.target.checked)}
                            />
                            <label className="form-check-label text-tiny text-ava-muted" htmlFor="select-all-unread">
                                Select all unread
                            </label>
                        </div>
                        <div className="d-flex gap-2">
                            {selectedIds.length > 0 && (
                                <button type="button" className="btn btn-sm btn-outline-primary" onClick={markSelectedRead}>
                                    Mark selected as read ({selectedIds.length})
                                </button>
                            )}
                            <button type="button" className="btn btn-sm btn-outline-primary" onClick={markAllRead}>
                                Mark all as read
                            </button>
                        </div>
                    </div>
                )}
                <ul className="list-group list-group-flush">
                    {notifications.data.map((notification) => (
                        <li
                            key={notification.id}
                            className={`list-group-item p-3 ${notification.is_read ? '' : 'notification-unread'}`}
                        >
                            <div className="d-flex gap-3 align-items-start">
                                {!notification.is_read && (
                                    <input
                                        type="checkbox"
                                        className="form-check-input mt-1 flex-shrink-0"
                                        aria-label="Select notification"
                                        checked={selectedIds.includes(notification.id)}
                                        onChange={(e) => toggleSelected(notification, e.target.checked)}
                                    />
                                )}
                                {notification.image_url && (
                                    <img
                                        src={notification.image_url}
                                        alt=""
                                        width={56}
                                        height={56}
                                        className="rounded object-fit-cover flex-shrink-0"
                                    />
                                )}
                                <div className="min-w-0 flex-grow-1">
                                    <div className="d-flex align-items-start justify-content-between gap-2">
                                        <div className={notification.is_read ? '' : 'fw-semibold'}>{notification.message}</div>
                                        {!notification.is_read && <span className="scope-chip text-nowrap">New</span>}
                                    </div>
                                    <div className="text-ava-muted text-tiny mt-1">{notification.created_at}</div>
                                </div>
                                {!notification.is_read && (
                                    <button
                                        type="button"
                                        className="btn btn-sm btn-outline-primary text-nowrap align-self-start"
                                        onClick={() => markRead(notification)}
                                    >
                                        Mark as Read
                                    </button>
                                )}
                            </div>
                        </li>
                    ))}
                    {notifications.data.length === 0 && (
                        <li className="list-group-item text-center py-5">
                            <i className="bi bi-bell text-ava-muted icon-fs-48 d-block mb-3" />
                            <p className="text-ava-muted mb-0">No notifications yet.</p>
                        </li>
                    )}
                </ul>
            </div>
            <div className="mt-3">
                <Pagination links={notifications.links} />
            </div>
        </AppLayout>
    )
}
