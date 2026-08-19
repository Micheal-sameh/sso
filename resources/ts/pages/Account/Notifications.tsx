import { router } from '@inertiajs/react'
import AppLayout from '@/components/layouts/AppLayout'
import Pagination from '@/components/shared/Pagination'
import type { PaginatedData, UserNotification } from '@/types'

interface NotificationsProps {
    notifications: PaginatedData<UserNotification>
}

export default function Notifications({ notifications }: NotificationsProps) {
    const markRead = (notification: UserNotification) => {
        router.post(`/notifications/${notification.id}/read`, {}, { preserveScroll: true })
    }

    return (
        <AppLayout title="Notifications">
            <div className="ava-card">
                <ul className="list-group list-group-flush">
                    {notifications.data.map((notification) => (
                        <li
                            key={notification.id}
                            className={`list-group-item p-3 ${notification.is_read ? '' : 'notification-unread'}`}
                        >
                            <div className="d-flex gap-3">
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
