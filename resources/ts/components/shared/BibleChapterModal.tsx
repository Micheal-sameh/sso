import { useTranslation } from 'react-i18next'
import type { BibleVerse } from '@/types'

interface BibleChapterModalProps {
    title: string | null
    verses: BibleVerse[] | null
    loading: boolean
    onClose: () => void
}

export default function BibleChapterModal({ title, verses, loading, onClose }: BibleChapterModalProps) {
    const { t } = useTranslation('common')
    const { t: tLiturgy } = useTranslation('liturgy')

    if (title === null) return null

    return (
        <div className="modal d-block modal-backdrop-custom" tabIndex={-1}>
            <div className="modal-dialog modal-dialog-centered modal-dialog-scrollable modal-lg">
                <div className="modal-content">
                    <div className="modal-header py-2">
                        <span className="fw-semibold">{title}</span>
                        <button type="button" className="btn-close" aria-label={t('actions.close')} onClick={onClose} />
                    </div>
                    <div className="modal-body">
                        {loading ? (
                            <div className="d-flex justify-content-center py-4">
                                <div className="spinner-border text-ava-secondary" role="status">
                                    <span className="visually-hidden">{t('inAppLinkModal.loading')}</span>
                                </div>
                            </div>
                        ) : verses && verses.length > 0 ? (
                            <p className="mb-0">
                                {verses.map((verse) => (
                                    <span key={verse.verse}>
                                        <sup className="text-ava-secondary fw-semibold me-1">{verse.verse}</sup>
                                        {verse.text}{' '}
                                    </span>
                                ))}
                            </p>
                        ) : (
                            <p className="text-ava-muted mb-0">{tLiturgy('textUnavailable')}</p>
                        )}
                    </div>
                </div>
            </div>
        </div>
    )
}
