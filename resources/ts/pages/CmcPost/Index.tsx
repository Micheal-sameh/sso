import { useState } from 'react'
import { Link } from '@inertiajs/react'
import AppLayout from '@/components/layouts/AppLayout'
import InAppLinkModal from '@/components/shared/InAppLinkModal'
import LinkPreviewCard from '@/components/shared/LinkPreviewCard'
import Pagination from '@/components/shared/Pagination'
import type { CmcPost, PaginatedData } from '@/types'

interface IndexProps {
    posts: PaginatedData<CmcPost>
}

export default function Index({ posts }: IndexProps) {
    const [openUrl, setOpenUrl] = useState<string | null>(null)

    return (
        <AppLayout title="CMC Posts">
            <div className="ava-card">
                <ul className="list-group list-group-flush">
                    {posts.data.map((post) => (
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
                                        <div style={{ maxWidth: 420 }}>
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
                                        {post.creator && ` · Posted by ${post.creator.name}`}
                                    </div>
                                </div>
                            </div>
                        </li>
                    ))}
                    {posts.data.length === 0 && (
                        <li className="list-group-item text-center py-5">
                            <i className="bi bi-newspaper text-ava-muted icon-fs-48 d-block mb-3" />
                            <p className="text-ava-muted mb-0">No posts yet.</p>
                        </li>
                    )}
                </ul>
            </div>
            <div className="mt-3">
                <Pagination links={posts.links} />
            </div>

            <InAppLinkModal url={openUrl} onClose={() => setOpenUrl(null)} />
        </AppLayout>
    )
}
