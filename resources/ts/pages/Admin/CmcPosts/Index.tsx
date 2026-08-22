import { useEffect, useState } from 'react'
import type { ChangeEvent, FormEvent } from 'react'
import { router, useForm } from '@inertiajs/react'
import { useTranslation } from 'react-i18next'
import AdminLayout from '@/components/layouts/AdminLayout'
import { useConfirm } from '@/components/shared/ConfirmDialog'
import InAppLinkModal from '@/components/shared/InAppLinkModal'
import LinkPreviewCard from '@/components/shared/LinkPreviewCard'
import Pagination from '@/components/shared/Pagination'
import echo from '@/lib/echo'
import type { CmcPost, PaginatedData } from '@/types'

interface IndexProps {
    posts: PaginatedData<CmcPost>
    pinnedPosts: CmcPost[]
}

interface PostForm {
    text: string
    url: string
    image: File | null
}

const emptyForm: PostForm = { text: '', url: '', image: null }

export default function Index({ posts, pinnedPosts }: IndexProps) {
    const { t } = useTranslation(['admin', 'common'])
    const [imagePreview, setImagePreview] = useState<string | null>(null)
    const [openUrl, setOpenUrl] = useState<string | null>(null)
    const [editingPost, setEditingPost] = useState<CmcPost | null>(null)
    const [livePosts, setLivePosts] = useState<CmcPost[]>(posts.data)
    const form = useForm<PostForm>(emptyForm)
    const confirm = useConfirm()

    useEffect(() => {
        setLivePosts(posts.data)
    }, [posts])

    useEffect(() => {
        if (posts.current_page !== 1) return

        const channel = echo.private('cmc-posts.admin')
        channel.listen('.cmc-post.created', ({ post }: { post: CmcPost }) => {
            setLivePosts((current) => [post, ...current.filter((p) => p.id !== post.id)])
        })

        return () => {
            echo.leave('cmc-posts.admin')
        }
        // eslint-disable-next-line react-hooks/exhaustive-deps
    }, [posts.current_page])

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

    const resetForm = () => {
        form.reset()
        form.clearErrors()
        setImagePreview(null)
        setEditingPost(null)
    }

    const startEdit = (post: CmcPost) => {
        setEditingPost(post)
        form.setData({ text: post.text ?? '', url: post.url ?? '', image: null })
        setImagePreview(post.image_url)
    }

    const submit = (e: FormEvent) => {
        e.preventDefault()

        if (editingPost) {
            form.transform((data) => ({ ...data, _method: 'put' }))
            form.post(`/admin/cmc-posts/${editingPost.id}`, {
                forceFormData: true,
                onSuccess: resetForm,
            })
            return
        }

        form.transform((data) => data)
        form.post('/admin/cmc-posts', {
            forceFormData: true,
            onSuccess: resetForm,
        })
    }

    const remove = async (post: CmcPost) => {
        if (await confirm(t('cmcPosts.confirmDelete'), { confirmLabel: t('common:actions.delete') })) {
            router.delete(`/admin/cmc-posts/${post.id}`)
        }
    }

    const togglePin = (post: CmcPost) => {
        router.patch(`/admin/cmc-posts/${post.id}/toggle-pin`, {}, { preserveScroll: true })
    }

    const moveUp = (post: CmcPost) => {
        router.patch(`/admin/cmc-posts/${post.id}/move-up`, {}, { preserveScroll: true })
    }

    const moveDown = (post: CmcPost) => {
        router.patch(`/admin/cmc-posts/${post.id}/move-down`, {}, { preserveScroll: true })
    }

    return (
        <AdminLayout title={t('cmcPosts.title')}>
            <div className="row g-4">
                <div className="col-lg-5">
                    <h5 className="mb-3">{editingPost ? t('cmcPosts.editPost') : t('cmcPosts.newPost')}</h5>
                    <div className="ava-card p-4">
                        <form onSubmit={submit}>
                            <div className="mb-3">
                                <label className="form-label" htmlFor="text">{t('cmcPosts.text')}</label>
                                <textarea
                                    id="text"
                                    className="form-control"
                                    rows={4}
                                    maxLength={5000}
                                    value={form.data.text}
                                    onChange={(e) => form.setData('text', e.target.value)}
                                />
                                {form.errors.text && <div className="text-danger small mt-1">{form.errors.text}</div>}
                            </div>

                            <div className="mb-3">
                                <label className="form-label" htmlFor="url">{t('cmcPosts.linkOptional')}</label>
                                <input
                                    id="url"
                                    type="url"
                                    className="form-control"
                                    placeholder="https://..."
                                    value={form.data.url}
                                    onChange={(e) => form.setData('url', e.target.value)}
                                />
                                {form.errors.url && <div className="text-danger small mt-1">{form.errors.url}</div>}
                            </div>

                            <div className="mb-3">
                                <label className="form-label" htmlFor="image">{t('cmcPosts.pictureOptional')}</label>
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
                                {editingPost && (
                                    <p className="text-ava-muted text-tiny mt-1 mb-0">
                                        {t('cmcPosts.keepCurrentPicture')}
                                    </p>
                                )}
                                {form.errors.image && <div className="text-danger small mt-1">{form.errors.image}</div>}
                            </div>

                            {!editingPost && (
                                <p className="text-ava-muted text-tiny">{t('cmcPosts.notifyEveryone')}</p>
                            )}

                            <div className="d-flex gap-2">
                                <button type="submit" className="btn btn-primary flex-grow-1" disabled={form.processing}>
                                    <i className={`bi ${editingPost ? 'bi-check-lg' : 'bi-send'} me-1`} />
                                    {editingPost ? t('actions.saveChanges') : t('cmcPosts.publishPost')}
                                </button>
                                {editingPost && (
                                    <button type="button" className="btn btn-outline-primary" onClick={resetForm}>
                                        {t('common:actions.cancel')}
                                    </button>
                                )}
                            </div>
                        </form>
                    </div>
                </div>

                <div className="col-lg-7">
                    {pinnedPosts.length > 0 && (
                        <div className="mb-4">
                            <h5 className="mb-3">
                                <i className="bi bi-pin-angle-fill me-2" />
                                {t('cmcPosts.pinnedPosts')}
                            </h5>
                            <div className="ava-card">
                                <ul className="list-group list-group-flush">
                                    {pinnedPosts.map((post, index) => (
                                        <li key={post.id} className="list-group-item p-3">
                                            <div className="d-flex gap-3">
                                                {post.image_url && (
                                                    <img
                                                        src={post.image_url}
                                                        alt=""
                                                        width={48}
                                                        height={48}
                                                        className="rounded object-fit-cover flex-shrink-0"
                                                    />
                                                )}
                                                <div className="min-w-0 flex-grow-1">
                                                    {post.text && <div className="mb-2 text-truncate-2">{post.text}</div>}
                                                    <div className="text-ava-muted text-tiny mt-2">
                                                        {post.creator
                                                            ? t('cmcPosts.postedBy', { date: post.created_at, name: post.creator.name })
                                                            : t('cmcPosts.posted', { date: post.created_at })}
                                                    </div>
                                                </div>
                                                <div className="d-flex flex-column gap-2 align-items-stretch flex-shrink-0">
                                                    <div className="d-flex gap-1">
                                                        <button
                                                            type="button"
                                                            className="btn btn-sm btn-outline-primary"
                                                            onClick={() => moveUp(post)}
                                                            disabled={index === 0}
                                                            aria-label={t('cmcPosts.moveUp')}
                                                        >
                                                            <i className="bi bi-arrow-up" />
                                                        </button>
                                                        <button
                                                            type="button"
                                                            className="btn btn-sm btn-outline-primary"
                                                            onClick={() => moveDown(post)}
                                                            disabled={index === pinnedPosts.length - 1}
                                                            aria-label={t('cmcPosts.moveDown')}
                                                        >
                                                            <i className="bi bi-arrow-down" />
                                                        </button>
                                                    </div>
                                                    <button
                                                        type="button"
                                                        className="btn btn-sm btn-outline-primary text-nowrap"
                                                        onClick={() => togglePin(post)}
                                                    >
                                                        {t('cmcPosts.unpin')}
                                                    </button>
                                                </div>
                                            </div>
                                        </li>
                                    ))}
                                </ul>
                            </div>
                        </div>
                    )}

                    <div className="d-flex align-items-center justify-content-between mb-3">
                        <h5 className="mb-0">{t('cmcPosts.publishedPosts')}</h5>
                        <a href="/cmc" target="_blank" rel="noopener noreferrer" className="btn btn-sm btn-outline-primary">
                            <i className="bi bi-box-arrow-up-right me-1" />
                            {t('cmcPosts.viewAllPosts')}
                        </a>
                    </div>
                    <div className="ava-card">
                        <ul className="list-group list-group-flush">
                            {livePosts.map((post) => (
                                <li key={post.id} className={`list-group-item p-3 ${editingPost?.id === post.id ? 'notification-unread' : ''}`}>
                                    <div className="d-flex gap-3">
                                        {post.image_url && (
                                            <img
                                                src={post.image_url}
                                                alt=""
                                                width={48}
                                                height={48}
                                                className="rounded object-fit-cover flex-shrink-0"
                                            />
                                        )}
                                        <div className="min-w-0 flex-grow-1">
                                            {post.text && <div className="mb-2 text-truncate-2">{post.text}</div>}
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
                                                {post.creator
                                                    ? t('cmcPosts.postedBy', { date: post.created_at, name: post.creator.name })
                                                    : t('cmcPosts.posted', { date: post.created_at })}
                                            </div>
                                        </div>
                                        <div className="d-flex flex-column gap-2 align-items-stretch">
                                            <button
                                                type="button"
                                                className="btn btn-sm btn-outline-primary text-nowrap"
                                                onClick={() => togglePin(post)}
                                            >
                                                {post.is_pinned ? t('cmcPosts.unpin') : t('cmcPosts.pin')}
                                            </button>
                                            <button
                                                type="button"
                                                className="btn btn-sm btn-outline-primary text-nowrap"
                                                onClick={() => startEdit(post)}
                                            >
                                                {t('common:actions.edit')}
                                            </button>
                                            <button
                                                type="button"
                                                className="btn btn-sm btn-outline-primary text-nowrap"
                                                onClick={() => remove(post)}
                                            >
                                                {t('common:actions.delete')}
                                            </button>
                                        </div>
                                    </div>
                                </li>
                            ))}
                            {livePosts.length === 0 && (
                                <li className="list-group-item text-center text-ava-muted py-4">{t('cmcPosts.empty')}</li>
                            )}
                        </ul>
                    </div>
                    <div className="mt-3">
                        <Pagination links={posts.links} />
                    </div>
                </div>
            </div>

            <InAppLinkModal url={openUrl} onClose={() => setOpenUrl(null)} />
        </AdminLayout>
    )
}
