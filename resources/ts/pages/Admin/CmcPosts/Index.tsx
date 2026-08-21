import { useEffect, useState } from 'react'
import type { ChangeEvent, FormEvent } from 'react'
import { router, useForm } from '@inertiajs/react'
import AdminLayout from '@/components/layouts/AdminLayout'
import { useConfirm } from '@/components/shared/ConfirmDialog'
import InAppLinkModal from '@/components/shared/InAppLinkModal'
import LinkPreviewCard from '@/components/shared/LinkPreviewCard'
import Pagination from '@/components/shared/Pagination'
import echo from '@/lib/echo'
import type { CmcPost, PaginatedData } from '@/types'

interface IndexProps {
    posts: PaginatedData<CmcPost>
}

interface PostForm {
    text: string
    url: string
    image: File | null
}

const emptyForm: PostForm = { text: '', url: '', image: null }

export default function Index({ posts }: IndexProps) {
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
        if (await confirm('Delete this post? It will disappear for everyone.', { confirmLabel: 'Delete' })) {
            router.delete(`/admin/cmc-posts/${post.id}`)
        }
    }

    return (
        <AdminLayout title="CMC Posts">
            <div className="row g-4">
                <div className="col-lg-5">
                    <h5 className="mb-3">{editingPost ? 'Edit Post' : 'New Post'}</h5>
                    <div className="ava-card p-4">
                        <form onSubmit={submit}>
                            <div className="mb-3">
                                <label className="form-label" htmlFor="text">Text</label>
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
                                <label className="form-label" htmlFor="url">Link (optional)</label>
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
                                {editingPost && (
                                    <p className="text-ava-muted text-tiny mt-1 mb-0">
                                        Leave empty to keep the current picture.
                                    </p>
                                )}
                                {form.errors.image && <div className="text-danger small mt-1">{form.errors.image}</div>}
                            </div>

                            {!editingPost && (
                                <p className="text-ava-muted text-tiny">This will be sent as a notification to every user.</p>
                            )}

                            <div className="d-flex gap-2">
                                <button type="submit" className="btn btn-primary flex-grow-1" disabled={form.processing}>
                                    <i className={`bi ${editingPost ? 'bi-check-lg' : 'bi-send'} me-1`} />
                                    {editingPost ? 'Save Changes' : 'Publish Post'}
                                </button>
                                {editingPost && (
                                    <button type="button" className="btn btn-outline-primary" onClick={resetForm}>
                                        Cancel
                                    </button>
                                )}
                            </div>
                        </form>
                    </div>
                </div>

                <div className="col-lg-7">
                    <h5 className="mb-3">Published Posts</h5>
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
                                                Posted {post.created_at}
                                                {post.creator && ` by ${post.creator.name}`}
                                            </div>
                                        </div>
                                        <div className="d-flex flex-column gap-2 align-items-stretch">
                                            <button
                                                type="button"
                                                className="btn btn-sm btn-outline-primary text-nowrap"
                                                onClick={() => startEdit(post)}
                                            >
                                                Edit
                                            </button>
                                            <button
                                                type="button"
                                                className="btn btn-sm btn-outline-primary text-nowrap"
                                                onClick={() => remove(post)}
                                            >
                                                Delete
                                            </button>
                                        </div>
                                    </div>
                                </li>
                            ))}
                            {livePosts.length === 0 && (
                                <li className="list-group-item text-center text-ava-muted py-4">No posts yet.</li>
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
