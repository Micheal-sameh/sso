import { useEffect, useState } from 'react'
import { Link, usePage } from '@inertiajs/react'
import { useTranslation } from 'react-i18next'
import AppLayout from '@/components/layouts/AppLayout'
import InAppLinkModal from '@/components/shared/InAppLinkModal'
import LinkPreviewCard from '@/components/shared/LinkPreviewCard'
import ScrollableRow from '@/components/shared/ScrollableRow'
import SpiritualNoteProgressRing from '@/components/shared/SpiritualNoteProgressRing'
import echo from '@/lib/echo'
import { isBirthdayToday } from '@/lib/date'
import type { CmcPost, CopticCalendar, SharedProps, YoutubeVideo } from '@/types'

interface YoutubeLive {
    status: 'live' | 'upcoming'
    video_id: string
    title: string | null
    thumbnail_url: string | null
    scheduled_start_time: string | null
}

interface SpiritualNotesSummary {
    total: number
    checked: number
    reminderDue: boolean
}

interface IndexProps {
    coptic: CopticCalendar
    recentPosts: CmcPost[]
    pinnedPosts: CmcPost[]
    youtubeLive: YoutubeLive | null
    pinnedVideos: YoutubeVideo[]
    spiritualNotesToday: SpiritualNotesSummary
}

// The YouTube webhook pushes status changes over Reverb instantly (see
// CheckYoutubeLiveStatusJob); this poll only exists as a fallback in case a
// webhook push is missed, so it can stay slow.
const LIVE_STATUS_POLL_INTERVAL_MS = 5 * 60_000

export default function Index({
    coptic,
    recentPosts,
    pinnedPosts,
    youtubeLive: initialYoutubeLive,
    pinnedVideos,
    spiritualNotesToday,
}: IndexProps) {
    const { t, i18n } = useTranslation('home')
    const { auth } = usePage<SharedProps>().props
    const [openUrl, setOpenUrl] = useState<string | null>(null)
    const [youtubeLive, setYoutubeLive] = useState(initialYoutubeLive)
    const [showAllCommemorations, setShowAllCommemorations] = useState(false)
    const isBirthday = isBirthdayToday(auth.user?.date_of_birth)
    const spiritualNotesIsComplete = spiritualNotesToday.total > 0 && spiritualNotesToday.checked === spiritualNotesToday.total

    useEffect(() => {
        const channel = echo.channel('youtube-live')
        channel.listen('.status.updated', ({ status }: { status: YoutubeLive | null }) => {
            setYoutubeLive(status)
        })

        return () => echo.leaveChannel('youtube-live')
    }, [])

    useEffect(() => {
        const interval = setInterval(() => {
            fetch('/youtube/live-status', {
                headers: { Accept: 'application/json' },
                credentials: 'same-origin',
            })
                .then((response) => (response.ok ? response.json() : null))
                .then((data) => {
                    if (data && 'youtubeLive' in data) {
                        setYoutubeLive(data.youtubeLive)
                    }
                })
                .catch(() => undefined)
        }, LIVE_STATUS_POLL_INTERVAL_MS)

        return () => clearInterval(interval)
    }, [])

    return (
        <AppLayout>
            {isBirthday && (
                <div className="ava-card mb-4 p-3 d-flex align-items-center gap-2">
                    <i className="bi bi-balloon-heart-fill text-ava-primary fs-4" />
                    <span className="fw-semibold">🎉 {t('home.happyBirthday', { name: auth.user?.name })}</span>
                </div>
            )}

            <h3 className="mb-4">{t('home.welcome', { name: auth.user?.name })}</h3>

            <Link
                href="/coptic"
                className="ava-card p-3 mb-3 d-block text-decoration-none text-reset"
                dir={i18n.language === 'ar' ? 'rtl' : 'ltr'}
            >
                <h5 className="mb-2">
                    <i className="bi bi-calendar-event me-2" />
                    {t('home.copticDate')}
                </h5>
                <div className="d-flex align-items-center justify-content-between gap-2">
                    <p className="mb-0">{coptic.date ?? t('home.copticDateUnavailable')}</p>
                    <span className="text-ava-secondary text-nowrap">
                        {t('home.readings')}
                        <i className={`bi ${i18n.language === 'ar' ? 'bi-chevron-left' : 'bi-chevron-right'} ms-1`} />
                    </span>
                </div>
            </Link>

            <Link
                href="/spiritual-notes"
                className="ava-card p-3 mb-3 d-flex align-items-center gap-3 text-decoration-none text-reset"
                dir={i18n.language === 'ar' ? 'rtl' : 'ltr'}
            >
                <SpiritualNoteProgressRing checked={spiritualNotesToday.checked} total={spiritualNotesToday.total} />
                <div className="flex-grow-1">
                    <div className="d-flex align-items-center justify-content-between gap-2 mb-1">
                        <h5 className="mb-0 d-flex align-items-center gap-2">
                            <i className="bi bi-journal-check" />
                            {t('home.spiritualNote')}
                            {spiritualNotesToday.reminderDue && (
                                <span className="badge rounded-pill bg-danger">
                                    <i className="bi bi-bell-fill me-1" />
                                    {t('home.spiritualNoteReminderBadge')}
                                </span>
                            )}
                        </h5>
                        <span className="text-ava-secondary text-nowrap">
                            {t('home.spiritualNoteOpen')}
                            <i className={`bi ${i18n.language === 'ar' ? 'bi-chevron-left' : 'bi-chevron-right'} ms-1`} />
                        </span>
                    </div>
                    <p className="mb-0 text-ava-muted">
                        {spiritualNotesIsComplete
                            ? t('home.spiritualNoteComplete')
                            : t('home.spiritualNoteProgress', { checked: spiritualNotesToday.checked, total: spiritualNotesToday.total })}
                    </p>
                </div>
            </Link>

            <div className="ava-card p-3 mb-3" dir={i18n.language === 'ar' ? 'rtl' : 'ltr'}>
                <h5 className="mb-2">{t('home.synaxariumToday')}</h5>
                {coptic.commemorations.length > 0 ? (
                    <>
                        <ul className={`mb-0 ${i18n.language === 'ar' ? 'pe-3' : 'ps-3'}`}>
                            {(showAllCommemorations ? coptic.commemorations : coptic.commemorations.slice(0, 2)).map((commemoration, index) => (
                                <li key={index} className="mb-1">
                                    {commemoration.url ? (
                                        <button
                                            type="button"
                                            className="btn btn-link p-0 align-baseline"
                                            onClick={() => setOpenUrl(commemoration.url)}
                                        >
                                            {commemoration.name}
                                        </button>
                                    ) : (
                                        commemoration.name
                                    )}
                                </li>
                            ))}
                        </ul>
                        {coptic.commemorations.length > 2 && (
                            <button
                                type="button"
                                className="btn btn-link p-0 align-baseline mt-2"
                                onClick={() => setShowAllCommemorations((prev) => !prev)}
                            >
                                {showAllCommemorations ? t('home.seeLess') : t('home.seeMore')}
                            </button>
                        )}
                    </>
                ) : (
                    <p className="text-ava-muted mb-0">{t('home.noCommemorationToday')}</p>
                )}
            </div>

            {youtubeLive?.status === 'live' && (
                <div className="ava-card mb-3 overflow-hidden">
                    <div className="ratio ratio-16x9">
                        <iframe
                            src={`https://www.youtube.com/embed/${youtubeLive.video_id}?autoplay=1`}
                            title={youtubeLive.title ?? t('home.watchLive')}
                            allow="accelerometer; autoplay; clipboard-write; encrypted-media; gyroscope; picture-in-picture"
                            allowFullScreen
                        />
                    </div>
                    <div className="p-3 d-flex align-items-center gap-2">
                        <span className="badge bg-danger d-flex align-items-center gap-1">
                            <i className="bi bi-broadcast" />
                            {t('home.liveNow')}
                        </span>
                        {youtubeLive.title && <span className="fw-semibold text-truncate">{youtubeLive.title}</span>}
                    </div>
                </div>
            )}

            {youtubeLive?.status === 'upcoming' && (
                <div className="ava-card mb-3 overflow-hidden">
                    {youtubeLive.thumbnail_url && (
                        <img src={youtubeLive.thumbnail_url} alt="" className="w-100 object-fit-cover" style={{ maxHeight: 200 }} />
                    )}
                    <div className="p-3">
                        <span className="badge bg-secondary d-flex align-items-center gap-1 mb-2" style={{ width: 'fit-content' }}>
                            <i className="bi bi-clock-history" />
                            {t('home.upcomingLive')}
                        </span>
                        {youtubeLive.title && <div className="fw-semibold mb-1 text-truncate-2">{youtubeLive.title}</div>}
                        {youtubeLive.scheduled_start_time && (
                            <div className="text-ava-muted text-tiny">
                                {new Date(youtubeLive.scheduled_start_time).toLocaleString(i18n.language)}
                            </div>
                        )}
                    </div>
                </div>
            )}

            {pinnedVideos.length > 0 && (
                <div className="ava-card p-3 mb-3">
                    <h5 className="mb-3">
                        <i className="bi bi-pin-angle-fill me-2" />
                        {t('home.pinnedVideos')}
                    </h5>
                    <ScrollableRow
                        items={pinnedVideos}
                        itemKey={(video) => video.id}
                        renderItem={(video) => (
                            <a
                                href={video.watch_url}
                                target="_blank"
                                rel="noopener noreferrer"
                                className="text-reset text-decoration-none d-block"
                                style={{ width: 220 }}
                            >
                                {video.thumbnail_url && (
                                    <div className="ratio ratio-16x9 mb-2">
                                        <img
                                            src={video.thumbnail_url}
                                            alt=""
                                            className="w-100 h-100 object-fit-cover rounded"
                                        />
                                    </div>
                                )}
                                <div className="text-truncate-2 text-tiny fw-semibold">{video.title}</div>
                            </a>
                        )}
                    />
                </div>
            )}

            {pinnedPosts.length > 0 && (
                <div className="ava-card p-3 mb-3">
                    <h5 className="mb-3">
                        <i className="bi bi-pin-angle-fill me-2" />
                        {t('home.pinnedPosts')}
                    </h5>
                    <ScrollableRow
                        items={pinnedPosts}
                        itemKey={(post) => post.id}
                        renderItem={(post) => (
                            <div className="border rounded p-3" style={{ width: 300 }}>
                                <div className="d-flex gap-3">
                                    {post.image_url && (
                                        <Link href={`/cmc/${post.id}`}>
                                            <img
                                                src={post.image_url}
                                                alt=""
                                                width={64}
                                                height={64}
                                                className="rounded object-fit-cover flex-shrink-0"
                                            />
                                        </Link>
                                    )}
                                    <div className="min-w-0 flex-grow-1">
                                        {post.text && (
                                            <Link href={`/cmc/${post.id}`} className="text-reset text-decoration-none">
                                                <div className="text-truncate-2 mb-2">{post.text}</div>
                                            </Link>
                                        )}
                                        {post.url && (
                                            <div>
                                                <LinkPreviewCard
                                                    url={post.url}
                                                    title={post.url_title}
                                                    description={post.url_description}
                                                    image={post.url_image}
                                                    onOpen={setOpenUrl}
                                                />
                                            </div>
                                        )}
                                        <div className="text-ava-muted text-tiny mt-2">
                                            {post.created_at}
                                            {post.creator && ` · ${t('home.postedBy', { name: post.creator.name })}`}
                                        </div>
                                    </div>
                                </div>
                            </div>
                        )}
                    />
                </div>
            )}

            <div className="ava-card">
                <div className="d-flex justify-content-between align-items-center p-3 pb-0">
                    <h5 className="mb-0">{t('home.latestCmcPosts')}</h5>
                    <Link href="/cmc" className="text-ava-secondary text-decoration-none">
                        {t('home.seeAll')}
                    </Link>
                </div>
                <ul className="list-group list-group-flush">
                    {recentPosts.map((post) => (
                        <li key={post.id} className="list-group-item p-3">
                            <div className="d-flex gap-3">
                                {post.image_url && (
                                    <Link href={`/cmc/${post.id}`}>
                                        <img
                                            src={post.image_url}
                                            alt=""
                                            width={64}
                                            height={64}
                                            className="rounded object-fit-cover flex-shrink-0"
                                        />
                                    </Link>
                                )}
                                <div className="min-w-0 flex-grow-1">
                                    {post.text && (
                                        <Link href={`/cmc/${post.id}`} className="text-reset text-decoration-none">
                                            <div className="text-truncate-2 mb-2">{post.text}</div>
                                        </Link>
                                    )}
                                    {post.url && (
                                        <div style={{ maxWidth: 360 }}>
                                            <LinkPreviewCard
                                                url={post.url}
                                                title={post.url_title}
                                                description={post.url_description}
                                                image={post.url_image}
                                                onOpen={setOpenUrl}
                                            />
                                        </div>
                                    )}
                                    <div className="text-ava-muted text-tiny mt-2">
                                        {post.created_at}
                                        {post.creator && ` · ${t('home.postedBy', { name: post.creator.name })}`}
                                    </div>
                                </div>
                            </div>
                        </li>
                    ))}
                    {recentPosts.length === 0 && (
                        <li className="list-group-item text-center py-5">
                            <i className="bi bi-newspaper text-ava-muted icon-fs-48 d-block mb-3" />
                            <p className="text-ava-muted mb-0">{t('home.noPostsYet')}</p>
                        </li>
                    )}
                </ul>
            </div>

            <InAppLinkModal url={openUrl} onClose={() => setOpenUrl(null)} />
        </AppLayout>
    )
}
