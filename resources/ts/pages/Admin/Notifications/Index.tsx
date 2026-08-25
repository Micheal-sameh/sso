import { useState } from 'react'
import type { ChangeEvent, FormEvent } from 'react'
import { router, useForm } from '@inertiajs/react'
import { useTranslation } from 'react-i18next'
import AdminLayout from '@/components/layouts/AdminLayout'
import { useConfirm } from '@/components/shared/ConfirmDialog'
import Pagination from '@/components/shared/Pagination'
import type { AppNotification, Group, LiteUser, PaginatedData, Stage } from '@/types'

interface IndexProps {
    notifications: PaginatedData<AppNotification>
    stages: Stage[]
    groups: Group[]
    users: LiteUser[]
    filters: { type?: string }
    availableTypes: AppNotification['type'][]
}

interface NotificationForm {
    message: string
    image: File | null
    target_type: 'all' | 'stage' | 'specific' | 'group'
    stage_ids: number[]
    user_ids: number[]
    group_ids: number[]
}

const emptyForm: NotificationForm = {
    message: '',
    image: null,
    target_type: 'all',
    stage_ids: [],
    user_ids: [],
    group_ids: [],
}

export default function Index({ notifications, stages, groups, users, filters, availableTypes }: IndexProps) {
    const { t } = useTranslation(['admin', 'common'])
    const TARGET_TYPE_LABELS: Record<AppNotification['target_type'], string> = {
        all: t('targeting.all'),
        stage: t('targeting.stage'),
        specific: t('targeting.specific'),
        group: t('targeting.group'),
    }
    const TYPE_LABELS: Record<AppNotification['type'], string> = {
        manual: t('notifications.types.manual'),
        cmc: t('notifications.types.cmc'),
        youtube: t('notifications.types.youtube'),
        donation_reminder: t('notifications.types.donation_reminder'),
        birthday: t('notifications.types.birthday'),
        spiritual_note_reminder: t('notifications.types.spiritual_note_reminder'),
    }
    const [imagePreview, setImagePreview] = useState<string | null>(null)
    const [userSearch, setUserSearch] = useState('')
    const form = useForm<NotificationForm>(emptyForm)
    const filterForm = useForm({ type: filters.type ?? '' })
    const confirm = useConfirm()

    const applyTypeFilter = (type: string) => {
        filterForm.setData('type', type)
        router.get('/admin/notifications', { type: type || undefined }, { preserveState: true })
    }

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
        if (await confirm(t('notifications.confirmDelete'), { confirmLabel: t('common:actions.delete') })) {
            router.delete(`/admin/notifications/${notification.id}`)
        }
    }

    const targetSummary = (notification: AppNotification): string => {
        switch (notification.target_type) {
            case 'all':
                return t('targeting.all')
            case 'stage':
                return (notification.stages ?? []).map((s) => s.name).join(', ') || t('notifications.noStages')
            case 'group':
                return (notification.groups ?? []).map((g) => g.name).join(', ') || t('notifications.noGroups')
            case 'specific':
                return (notification.target_users ?? []).map((u) => u.name).join(', ') || t('notifications.noUsers')
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
        <AdminLayout title={t('notifications.title')}>
            <div className="row g-4">
                <div className="col-lg-5">
                    <h5 className="mb-3">{t('notifications.sendNotification')}</h5>
                    <div className="ava-card p-4">
                        <form onSubmit={submit}>
                            <div className="mb-3">
                                <label className="form-label" htmlFor="message">{t('notifications.message')}</label>
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
                                <label className="form-label" htmlFor="image">{t('notifications.pictureOptional')}</label>
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
                                <label className="form-label" htmlFor="target_type">{t('notifications.sendTo')}</label>
                                <select
                                    id="target_type"
                                    className="form-select"
                                    value={form.data.target_type}
                                    onChange={(e) => form.setData('target_type', e.target.value as NotificationForm['target_type'])}
                                >
                                    <option value="all">{t('targeting.all')}</option>
                                    <option value="stage">{t('targeting.stage')}</option>
                                    <option value="specific">{t('targeting.specific')}</option>
                                    <option value="group">{t('targeting.group')}</option>
                                </select>
                                {form.errors.target_type && (
                                    <div className="text-danger small mt-1">{form.errors.target_type}</div>
                                )}
                            </div>

                            {form.data.target_type === 'stage' && (
                                <div className="mb-3">
                                    <label className="form-label">{t('notifications.stagesLabel')}</label>
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
                                    <label className="form-label">{t('notifications.groupsLabel')}</label>
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
                                            <p className="text-ava-muted text-tiny mb-0">{t('search.noGroupsYet')}</p>
                                        )}
                                    </div>
                                    {form.errors.group_ids && (
                                        <div className="text-danger small mt-1">{form.errors.group_ids}</div>
                                    )}
                                </div>
                            )}

                            {form.data.target_type === 'specific' && (
                                <div className="mb-3">
                                    <label className="form-label">{t('notifications.usersLabel')}</label>
                                    <input
                                        type="text"
                                        className="form-control mb-2"
                                        placeholder={t('search.users')}
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
                                            <p className="text-ava-muted text-tiny mb-0">{t('search.noUsersMatch')}</p>
                                        )}
                                    </div>
                                    {form.errors.user_ids && (
                                        <div className="text-danger small mt-1">{form.errors.user_ids}</div>
                                    )}
                                </div>
                            )}

                            <button type="submit" className="btn btn-primary w-100" disabled={form.processing}>
                                <i className="bi bi-send me-1" />
                                {t('notifications.send')}
                            </button>
                        </form>
                    </div>
                </div>

                <div className="col-lg-7">
                    <div className="d-flex flex-wrap align-items-center justify-content-between gap-2 mb-3">
                        <h5 className="mb-0">{t('notifications.sentNotifications')}</h5>
                        <div style={{ minWidth: 200 }}>
                            <select
                                className="form-select form-select-sm"
                                aria-label={t('notifications.filterByType')}
                                value={filterForm.data.type}
                                onChange={(e) => applyTypeFilter(e.target.value)}
                            >
                                <option value="">{t('notifications.types.all')}</option>
                                {availableTypes.map((type) => (
                                    <option key={type} value={type}>{TYPE_LABELS[type]}</option>
                                ))}
                            </select>
                        </div>
                    </div>
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
                                                <span className="role-chip">{TYPE_LABELS[notification.type]}</span>
                                                <span className="role-chip">{TARGET_TYPE_LABELS[notification.target_type]}</span>
                                                <span className="text-ava-muted text-tiny">{targetSummary(notification)}</span>
                                            </div>
                                            <div className="text-ava-muted text-tiny mt-1">
                                                {notification.creator
                                                    ? t('notifications.sentBy', { date: notification.created_at, name: notification.creator.name })
                                                    : t('notifications.sent', { date: notification.created_at })}
                                            </div>
                                        </div>
                                        <button
                                            type="button"
                                            className="btn btn-sm btn-outline-primary text-nowrap align-self-start"
                                            onClick={() => remove(notification)}
                                        >
                                            {t('common:actions.delete')}
                                        </button>
                                    </div>
                                </li>
                            ))}
                            {notifications.data.length === 0 && (
                                <li className="list-group-item text-center text-ava-muted py-4">
                                    {t('notifications.empty')}
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
