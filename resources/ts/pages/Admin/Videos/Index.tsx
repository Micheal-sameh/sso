import { useState } from 'react'
import { router } from '@inertiajs/react'
import { useTranslation } from 'react-i18next'
import AdminLayout from '@/components/layouts/AdminLayout'
import Pagination from '@/components/shared/Pagination'
import type { PaginatedData, YoutubeVideo } from '@/types'

interface IndexProps {
    videos: PaginatedData<YoutubeVideo>
    pinnedVideos: YoutubeVideo[]
}

export default function Index({ videos, pinnedVideos }: IndexProps) {
    const { t } = useTranslation(['admin', 'common'])
    const [syncing, setSyncing] = useState(false)

    const sync = () => {
        setSyncing(true)
        router.post('/admin/videos/sync', {}, {
            preserveScroll: true,
            onFinish: () => setSyncing(false),
        })
    }

    const togglePin = (video: YoutubeVideo) => {
        router.patch(`/admin/videos/${video.id}/toggle-pin`, {}, { preserveScroll: true })
    }

    const moveUp = (video: YoutubeVideo) => {
        router.patch(`/admin/videos/${video.id}/move-up`, {}, { preserveScroll: true })
    }

    const moveDown = (video: YoutubeVideo) => {
        router.patch(`/admin/videos/${video.id}/move-down`, {}, { preserveScroll: true })
    }

    return (
        <AdminLayout
            title={t('videos.title')}
            action={(
                <button type="button" className="btn btn-primary" onClick={sync} disabled={syncing}>
                    <i className="bi bi-arrow-repeat me-1" />
                    {syncing ? t('videos.syncing') : t('videos.syncNow')}
                </button>
            )}
        >
            {pinnedVideos.length > 0 && (
                <div className="ava-card mb-4">
                    <h5 className="p-3 pb-0 mb-2">
                        <i className="bi bi-pin-angle-fill me-2" />
                        {t('videos.pinnedVideos')}
                    </h5>
                    <ul className="list-group list-group-flush">
                        {pinnedVideos.map((video, index) => (
                            <li key={video.id} className="list-group-item p-3">
                                <div className="d-flex gap-3">
                                    {video.thumbnail_url && (
                                        <img
                                            src={video.thumbnail_url}
                                            alt=""
                                            width={96}
                                            height={54}
                                            className="rounded object-fit-cover flex-shrink-0"
                                        />
                                    )}
                                    <div className="min-w-0 flex-grow-1">
                                        <div className="text-truncate-2 mb-1">{video.title}</div>
                                        {video.published_at && (
                                            <div className="text-ava-muted text-tiny">
                                                {t('videos.published', { date: video.published_at })}
                                            </div>
                                        )}
                                    </div>
                                    <div className="d-flex flex-column gap-2 align-items-stretch flex-shrink-0">
                                        <div className="d-flex gap-1">
                                            <button
                                                type="button"
                                                className="btn btn-sm btn-outline-primary"
                                                onClick={() => moveUp(video)}
                                                disabled={index === 0}
                                                aria-label={t('videos.moveUp')}
                                            >
                                                <i className="bi bi-arrow-up" />
                                            </button>
                                            <button
                                                type="button"
                                                className="btn btn-sm btn-outline-primary"
                                                onClick={() => moveDown(video)}
                                                disabled={index === pinnedVideos.length - 1}
                                                aria-label={t('videos.moveDown')}
                                            >
                                                <i className="bi bi-arrow-down" />
                                            </button>
                                        </div>
                                        <button
                                            type="button"
                                            className="btn btn-sm btn-outline-primary text-nowrap"
                                            onClick={() => togglePin(video)}
                                        >
                                            {t('videos.unpin')}
                                        </button>
                                    </div>
                                </div>
                            </li>
                        ))}
                    </ul>
                </div>
            )}

            <div className="ava-card">
                <ul className="list-group list-group-flush">
                    {videos.data.map((video) => (
                        <li key={video.id} className="list-group-item p-3">
                            <div className="d-flex gap-3">
                                {video.thumbnail_url && (
                                    <img
                                        src={video.thumbnail_url}
                                        alt=""
                                        width={96}
                                        height={54}
                                        className="rounded object-fit-cover flex-shrink-0"
                                    />
                                )}
                                <div className="min-w-0 flex-grow-1">
                                    <div className="text-truncate-2 mb-1">{video.title}</div>
                                    {video.published_at && (
                                        <div className="text-ava-muted text-tiny">
                                            {t('videos.published', { date: video.published_at })}
                                        </div>
                                    )}
                                    {video.is_pinned && (
                                        <span className="badge bg-primary mt-1">
                                            <i className="bi bi-pin-angle-fill me-1" />
                                            {t('videos.pinned')}
                                        </span>
                                    )}
                                </div>
                                <div className="d-flex align-items-start">
                                    <button
                                        type="button"
                                        className="btn btn-sm btn-outline-primary text-nowrap"
                                        onClick={() => togglePin(video)}
                                    >
                                        {video.is_pinned ? t('videos.unpin') : t('videos.pin')}
                                    </button>
                                </div>
                            </div>
                        </li>
                    ))}
                    {videos.data.length === 0 && (
                        <li className="list-group-item text-center text-ava-muted py-4">{t('videos.empty')}</li>
                    )}
                </ul>
            </div>
            <div className="mt-3">
                <Pagination pagination={videos} />
            </div>
        </AdminLayout>
    )
}
