import { useTranslation } from 'react-i18next'
import AppLayout from '@/components/layouts/AppLayout'
import Pagination from '@/components/shared/Pagination'
import type { PaginatedData, YoutubeVideo } from '@/types'

interface IndexProps {
    videos: PaginatedData<YoutubeVideo>
}

export default function Index({ videos }: IndexProps) {
    const { t } = useTranslation('videos')

    return (
        <AppLayout title={t('index.title')}>
            <div className="row g-3">
                {videos.data.map((video) => (
                    <div key={video.id} className="col-6 col-md-4">
                        <a
                            href={video.watch_url}
                            target="_blank"
                            rel="noopener noreferrer"
                            className="ava-card d-block overflow-hidden text-reset text-decoration-none h-100"
                        >
                            {video.thumbnail_url && (
                                <div className="ratio ratio-16x9">
                                    <img src={video.thumbnail_url} alt="" className="w-100 h-100 object-fit-cover" />
                                </div>
                            )}
                            <div className="p-2">
                                <div className="text-truncate-2 text-tiny fw-semibold">{video.title}</div>
                                {video.published_at && (
                                    <div className="text-ava-muted text-tiny mt-1">{video.published_at}</div>
                                )}
                            </div>
                        </a>
                    </div>
                ))}
                {videos.data.length === 0 && (
                    <div className="col-12">
                        <div className="ava-card text-center py-5">
                            <i className="bi bi-play-btn text-ava-muted icon-fs-48 d-block mb-3" />
                            <p className="text-ava-muted mb-0">{t('index.empty')}</p>
                        </div>
                    </div>
                )}
            </div>
            <div className="mt-3">
                <Pagination pagination={videos} />
            </div>
        </AppLayout>
    )
}
