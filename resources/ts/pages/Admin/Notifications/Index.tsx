import { useState } from 'react'
import type { ChangeEvent, FormEvent } from 'react'
import { router, useForm } from '@inertiajs/react'
import AdminLayout from '@/components/layouts/AdminLayout'
import { useConfirm } from '@/components/shared/ConfirmDialog'
import Pagination from '@/components/shared/Pagination'
import type { AppNotification, Group, LiteUser, PaginatedData, Stage } from '@/types'

interface IndexProps {
    notifications: PaginatedData<AppNotification>
    stages: Stage[]
    groups: Group[]
    users: LiteUser[]
}

interface NotificationForm {
    message: string
    image: File | null
    target_type: 'all' | 'stage' | 'specific' | 'group'
    stage_ids: number[]
    user_ids: number[]
    group_ids: number[]
}

const TARGET_TYPE_LABELS: Record<AppNotification['target_type'], string> = {
    all: 'All Users',
    stage: 'Specific Stage(s)',
    specific: 'Specific Users',
    group: 'Specific Group(s)',
}

const emptyForm: NotificationForm = {
    message: '',
    image: null,
    target_type: 'all',
    stage_ids: [],
    user_ids: [],
    group_ids: [],
}

export default function Index({ notifications, stages, groups, users }: IndexProps) {
    const [imagePreview, setImagePreview] = useState<string | null>(null)
    const [userSearch, setUserSearch] = useState('')
    const form = useForm<NotificationForm>(emptyForm)
    const confirm = useConfirm()

    const onImageChange = (e: ChangeEvent<HTMLInputElement>) => {
        const file = e.target.files?.[0] ?? null
        form.setData('image', file)

        if (file) {
            const reader = new FileReader()
            reader.onload = () => setImagePreview(reader.result as string)
            reader.readAsDataURL(file)
        } else {
            setImagePreview(null)
        }
    }

    const submit = (e: FormEvent) => {
        e.preventDefault()
        form.post('/admin/notifications', {
            forceFormData: true,
            onSuccess: () => {
                form.reset()
                setImagePreview(null)
                setUserSearch('')
            },
        })
    }

    const toggleStage = (stageId: number) => {
        form.setData(
            'stage_ids',
            form.data.stage_ids.includes(stageId)
                ? form.data.stage_ids.filter((id) => id !== stageId)
                : [...form.data.stage_ids, stageId],
        )
    }

    const toggleUser = (userId: number) => {
        form.setData(
            'user_ids',
            form.data.user_ids.includes(userId)
                ? form.data.user_ids.filter((id) => id !== userId)
                : [...form.data.user_ids, userId],
        )
    }

    const toggleGroup = (groupId: number) => {
        form.setData(
            'group_ids',
            form.data.group_ids.includes(groupId)
                ? form.data.group_ids.filter((id) => id !== groupId)
                : [...form.data.group_ids, groupId],
        )
    }

    const remove = async (notification: AppNotification) => {
        if (await confirm('Delete this notification? Recipients will no longer see it.', { confirmLabel: 'Delete' })) {
            router.delete(`/admin/notifications/${notification.id}`)
        }
    }

    const targetSummary = (notification: AppNotification): string => {
        switch (notification.target_type) {
            case 'all':
                return 'All Users'
            case 'stage':
                return (notification.stages ?? []).map((s) => s.name).join(', ') || 'No stages'
            case 'group':
                return (notification.groups ?? []).map((g) => g.name).join(', ') || 'No groups'
            case 'specific':
                return (notification.target_users ?? []).map((u) => u.name).join(', ') || 'No users'
            default:
                return ''
        }
    }

    const filteredUsers = users.filter(
        (u) =>
            u.name.toLowerCase().includes(userSearch.toLowerCase()) ||
            u.email.toLowerCase().includes(userSearch.toLowerCase()),
    )

    return (
        <AdminLayout title="Notification Center">
            <div className="row g-4">
                <div className="col-lg-5">
                    <h5 className="mb-3">Send Notification</h5>
                    <div className="ava-card p-4">
                        <form onSubmit={submit}>
                            <div className="mb-3">
                                <label className="form-label" htmlFor="message">Message</label>
                                <textarea
                                    id="message"
                                    className="form-control"
                                    rows={4}
                                    maxLength={2000}
                                    value={form.data.message}
                                    onChange={(e) => form.setData('message', e.target.value)}
                                />
                                {form.errors.message && <div className="text-danger small mt-1">{form.errors.message}</div>}
                            </div>

                            <div className="mb-3">
                                <label className="form-label" htmlFor="image">Picture (optional)</label>
                                <div className="d-flex align-items-center gap-3">
                                    {imagePreview && (
                                        <img
                                            src={imagePreview}
                                            alt="Preview"
                                            width={56}
                                            height={56}
                                            className="rounded object-fit-cover"
                                        />
                                    )}
                                    <input
                                        id="image"
                                        type="file"
                                        accept="image/*"
                                        className="form-control"
                                        onChange={onImageChange}
                                    />
                                </div>
                                {form.errors.image && <div className="text-danger small mt-1">{form.errors.image}</div>}
                            </div>

                            <div className="mb-3">
                                <label className="form-label" htmlFor="target_type">Send To</label>
                                <select
                                    id="target_type"
                                    className="form-select"
                                    value={form.data.target_type}
                                    onChange={(e) => form.setData('target_type', e.target.value as NotificationForm['target_type'])}
                                >
                                    <option value="all">All Users</option>
                                    <option value="stage">Specific Stage(s)</option>
                                    <option value="specific">Specific Users</option>
                                    <option value="group">Specific Group(s)</option>
                                </select>
                                {form.errors.target_type && (
                                    <div className="text-danger small mt-1">{form.errors.target_type}</div>
                                )}
                            </div>

                            {form.data.target_type === 'stage' && (
                                <div className="mb-3">
                                    <label className="form-label">Stages</label>
                                    <div className="permission-list">
                                        {stages.map((stage) => (
                                            <div key={stage.id} className="form-check">
                                                <input
                                                    id={`stage-${stage.id}`}
                                                    type="checkbox"
                                                    className="form-check-input"
                                                    checked={form.data.stage_ids.includes(stage.id)}
                                                    onChange={() => toggleStage(stage.id)}
                                                />
                                                <label className="form-check-label" htmlFor={`stage-${stage.id}`}>
                                                    {stage.name}
                                                </label>
                                            </div>
                                        ))}
                                    </div>
                                    {form.errors.stage_ids && (
                                        <div className="text-danger small mt-1">{form.errors.stage_ids}</div>
                                    )}
                                </div>
                            )}

                            {form.data.target_type === 'group' && (
                                <div className="mb-3">
                                    <label className="form-label">Groups</label>
                                    <div className="permission-list">
                                        {groups.map((group) => (
                                            <div key={group.id} className="form-check">
                                                <input
                                                    id={`group-${group.id}`}
                                                    type="checkbox"
                                                    className="form-check-input"
                                                    checked={form.data.group_ids.includes(group.id)}
                                                    onChange={() => toggleGroup(group.id)}
                                                />
                                                <label className="form-check-label" htmlFor={`group-${group.id}`}>
                                                    {group.name}
                                                </label>
                                            </div>
                                        ))}
                                        {groups.length === 0 && (
                                            <p className="text-ava-muted text-tiny mb-0">No groups exist yet.</p>
                                        )}
                                    </div>
                                    {form.errors.group_ids && (
                                        <div className="text-danger small mt-1">{form.errors.group_ids}</div>
                                    )}
                                </div>
                            )}

                            {form.data.target_type === 'specific' && (
                                <div className="mb-3">
                                    <label className="form-label">Users</label>
                                    <input
                                        type="text"
                                        className="form-control mb-2"
                                        placeholder="Search users..."
                                        value={userSearch}
                                        onChange={(e) => setUserSearch(e.target.value)}
                                    />
                                    <div className="permission-list scroll-picker">
                                        {filteredUsers.map((u) => (
                                            <div key={u.id} className="form-check">
                                                <input
                                                    id={`user-${u.id}`}
                                                    type="checkbox"
                                                    className="form-check-input"
                                                    checked={form.data.user_ids.includes(u.id)}
                                                    onChange={() => toggleUser(u.id)}
                                                />
                                                <label className="form-check-label" htmlFor={`user-${u.id}`}>
                                                    {u.name}
                                                    <span className="text-ava-muted text-tiny ms-1">{u.email}</span>
                                                </label>
                                            </div>
                                        ))}
                                        {filteredUsers.length === 0 && (
                                            <p className="text-ava-muted text-tiny mb-0">No users match your search.</p>
                                        )}
                                    </div>
                                    {form.errors.user_ids && (
                                        <div className="text-danger small mt-1">{form.errors.user_ids}</div>
                                    )}
                                </div>
                            )}

                            <button type="submit" className="btn btn-primary w-100" disabled={form.processing}>
                                <i className="bi bi-send me-1" />
                                Send Notification
                            </button>
                        </form>
                    </div>
                </div>

                <div className="col-lg-7">
                    <h5 className="mb-3">Sent Notifications</h5>
                    <div className="ava-card">
                        <ul className="list-group list-group-flush">
                            {notifications.data.map((notification) => (
                                <li key={notification.id} className="list-group-item p-3">
                                    <div className="d-flex gap-3">
                                        {notification.image_url && (
                                            <img
                                                src={notification.image_url}
                                                alt=""
                                                width={48}
                                                height={48}
                                                className="rounded object-fit-cover flex-shrink-0"
                                            />
                                        )}
                                        <div className="min-w-0 flex-grow-1">
                                            <div className="mb-1">{notification.message}</div>
                                            <div className="d-flex flex-wrap align-items-center gap-2">
                                                <span className="role-chip">{TARGET_TYPE_LABELS[notification.target_type]}</span>
                                                <span className="text-ava-muted text-tiny">{targetSummary(notification)}</span>
                                            </div>
                                            <div className="text-ava-muted text-tiny mt-1">
                                                Sent {notification.created_at}
                                                {notification.creator && ` by ${notification.creator.name}`}
                                            </div>
                                        </div>
                                        <button
                                            type="button"
                                            className="btn btn-sm btn-outline-primary text-nowrap align-self-start"
                                            onClick={() => remove(notification)}
                                        >
                                            Delete
                                        </button>
                                    </div>
                                </li>
                            ))}
                            {notifications.data.length === 0 && (
                                <li className="list-group-item text-center text-ava-muted py-4">
                                    No notifications sent yet.
                                </li>
                            )}
                        </ul>
                    </div>
                    <div className="mt-3">
                        <Pagination links={notifications.links} />
                    </div>
                </div>
            </div>
        </AdminLayout>
    )
}
