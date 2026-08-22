import { useState } from 'react'
import { useTranslation } from 'react-i18next'
import AppLayout from '@/components/layouts/AppLayout'
import InAppLinkModal from '@/components/shared/InAppLinkModal'
import LinkPreviewCard from '@/components/shared/LinkPreviewCard'
import type { CmcPost } from '@/types'

interface ShowProps {
    post: CmcPost
}

export default function Show({ post }: ShowProps) {
    const { t } = useTranslation('cmcPost')
    const [openUrl, setOpenUrl] = useState<string | null>(null)

    return (
        <AppLayout title={t('show.title')}>
            <div className="ava-card p-4" style={{ maxWidth: 720, margin: '0 auto' }}>
                <div className="d-flex align-items-center justify-content-between mb-3">
                    <span className="text-ava-muted text-tiny">
                        {post.creator ? t('show.postedBy', { name: post.creator.name }) : t('show.posted')} · {post.created_at}
                    </span>
                </div>

                {post.text && <p className="mb-3" style={{ whiteSpace: 'pre-wrap' }}>{post.text}</p>}

                {post.image_url && (
                    <img src={post.image_url} alt="" className="w-100 rounded mb-3 object-fit-cover" style={{ maxHeight: 400 }} />
                )}

                {post.url && (
                    <LinkPreviewCard
                        url={post.url}
                        title={post.url_title}
                        description={post.url_description}
                        image={post.url_image}
                        onOpen={setOpenUrl}
                    />
                )}
            </div>

            <InAppLinkModal url={openUrl} onClose={() => setOpenUrl(null)} />
        </AppLayout>
    )
}
