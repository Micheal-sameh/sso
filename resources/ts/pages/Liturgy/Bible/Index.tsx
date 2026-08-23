import { useState } from 'react'
import { Link } from '@inertiajs/react'
import { useTranslation } from 'react-i18next'
import AppLayout from '@/components/layouts/AppLayout'
import BibleChapterModal from '@/components/shared/BibleChapterModal'
import FontSizeControl from '@/components/shared/FontSizeControl'
import { useReadingFontSize } from '@/lib/readingFontSize'
import type { BibleBook, BibleVerse } from '@/types'

interface IndexProps {
    books: BibleBook[]
}

interface OpenChapter {
    title: string
    verses: BibleVerse[] | null
}

export default function Index({ books }: IndexProps) {
    const { t, i18n } = useTranslation('liturgy')
    const dir = i18n.language === 'ar' ? 'rtl' : 'ltr'
    const chevronIcon = i18n.language === 'ar' ? 'bi-chevron-left' : 'bi-chevron-right'
    const [selectedBook, setSelectedBook] = useState<BibleBook | null>(null)
    const [openChapter, setOpenChapter] = useState<OpenChapter | null>(null)
    const [loadingChapter, setLoadingChapter] = useState(false)
    const { fontSize, increase, decrease, canIncrease, canDecrease } = useReadingFontSize()

    const openChapterText = async (book: BibleBook, chapter: number) => {
        const title = `${book.name} ${chapter}`
        setOpenChapter({ title, verses: null })
        setLoadingChapter(true)

        try {
            const response = await fetch(`/liturgy/bible/chapter?book=${book.id}&chapter=${chapter}`, {
                headers: { Accept: 'application/json' },
                credentials: 'same-origin',
            })
            const data = response.ok ? await response.json() : null
            setOpenChapter({ title, verses: data?.verses ?? [] })
        } catch {
            setOpenChapter({ title, verses: [] })
        } finally {
            setLoadingChapter(false)
        }
    }

    return (
        <AppLayout title={t('bible')}>
            {selectedBook && (
                <button type="button" className="btn btn-link p-0 mb-3" onClick={() => setSelectedBook(null)}>
                    <i className={`bi ${dir === 'rtl' ? 'bi-arrow-right' : 'bi-arrow-left'} me-1`} />
                    {t('allBooks')}
                </button>
            )}

            {!selectedBook ? (
                <div className="ava-card" dir={dir}>
                    {books.length > 0 ? (
                        <ul className="list-group list-group-flush">
                            {books.map((book) => (
                                <li key={book.id} className="list-group-item p-0">
                                    <button
                                        type="button"
                                        className="btn d-flex align-items-center gap-3 p-3 w-100 text-start text-reset"
                                        onClick={() => setSelectedBook(book)}
                                    >
                                        <span className="flex-grow-1">{book.name}</span>
                                        <i className={`bi ${chevronIcon} text-ava-muted`} />
                                    </button>
                                </li>
                            ))}
                        </ul>
                    ) : (
                        <p className="text-ava-muted p-3 mb-0">{t('textUnavailable')}</p>
                    )}
                </div>
            ) : (
                <div className="ava-card p-3" dir={dir}>
                    <div className="d-flex align-items-center justify-content-between mb-3">
                        <h5 className="mb-0">{selectedBook.name}</h5>
                        <FontSizeControl canDecrease={canDecrease} canIncrease={canIncrease} onDecrease={decrease} onIncrease={increase} />
                    </div>
                    <div className="d-flex flex-wrap gap-2">
                        {Array.from({ length: selectedBook.chapters }, (_, i) => i + 1).map((chapter) => (
                            <button
                                key={chapter}
                                type="button"
                                className="btn btn-outline-secondary btn-sm"
                                style={{ minWidth: '2.75rem' }}
                                onClick={() => openChapterText(selectedBook, chapter)}
                            >
                                {chapter}
                            </button>
                        ))}
                    </div>
                </div>
            )}

            <Link href="/liturgy" className="btn btn-link p-0 mt-3">
                <i className={`bi ${dir === 'rtl' ? 'bi-arrow-right' : 'bi-arrow-left'} me-1`} />
                {t('title')}
            </Link>

            <BibleChapterModal
                title={openChapter?.title ?? null}
                verses={openChapter?.verses ?? null}
                loading={loadingChapter}
                fontSize={fontSize}
                onClose={() => setOpenChapter(null)}
            />
        </AppLayout>
    )
}
