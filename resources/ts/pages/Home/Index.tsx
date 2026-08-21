import { useState } from 'react'
import { Link, usePage } from '@inertiajs/react'
import AppLayout from '@/components/layouts/AppLayout'
import InAppLinkModal from '@/components/shared/InAppLinkModal'
import type { CmcPost, CopticCalendar, SharedProps } from '@/types'

interface IndexProps {
    coptic: CopticCalendar
    recentPosts: CmcPost[]
}

export default function Index({ coptic, recentPosts }: IndexProps) {
    const { auth } = usePage<SharedProps>().props
    const [openUrl, setOpenUrl] = useState<string | null>(null)

    return (
        <AppLayout>
            <h3 className="mb-4">Welcome, {auth.user?.name}</h3>

            <div className="ava-card p-3 mb-3" dir="rtl">
                <h5 className="mb-2">
                    <i className="bi bi-calendar-event me-2" />
                    التاريخ القبطي
                </h5>
                <p className="mb-0">{coptic.date ?? 'غير متاح حالياً.'}</p>
            </div>

            <div className="ava-card p-3 mb-3" dir="rtl">
                <h5 className="mb-2">تذكار اليوم من السنكسار</h5>
                {coptic.commemorations.length > 0 ? (
                    <ul className="mb-0 pe-3">
                        {coptic.commemorations.map((commemoration, index) => (
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
                ) : (
                    <p className="text-ava-muted mb-0">لا يوجد تذكار متاح حالياً.</p>
                )}
            </div>

            <div className="ava-card">
                <div className="d-flex justify-content-between align-items-center p-3 pb-0">
                    <h5 className="mb-0">Latest CMC Posts</h5>
                    <Link href="/cmc" className="text-ava-secondary text-decoration-none">
                        See all
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
                                    <div className="text-ava-muted text-tiny mt-2">
                                        {post.created_at}
                                        {post.creator && ` · Posted by ${post.creator.name}`}
                                    </div>
                                </div>
                            </div>
                        </li>
                    ))}
                    {recentPosts.length === 0 && (
                        <li className="list-group-item text-center py-5">
                            <i className="bi bi-newspaper text-ava-muted icon-fs-48 d-block mb-3" />
                            <p className="text-ava-muted mb-0">No posts yet.</p>
                        </li>
                    )}
                </ul>
            </div>

            <InAppLinkModal url={openUrl} onClose={() => setOpenUrl(null)} />
        </AppLayout>
    )
}
