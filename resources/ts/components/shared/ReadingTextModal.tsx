import { useTranslation } from 'react-i18next'
import type { CopticReading } from '@/types'

interface ReadingTextModalProps {
    reading: CopticReading | null
    onClose: () => void
}

export default function ReadingTextModal({ reading, onClose }: ReadingTextModalProps) {
    const { t } = useTranslation('common')

    if (!reading) return null

    return (
        <div className="modal d-block modal-backdrop-custom" tabIndex={-1}>
            <div className="modal-dialog modal-dialog-centered modal-dialog-scrollable modal-lg">
                <div className="modal-content">
                    <div className="modal-header py-2">
                        <span className="fw-semibold">{reading.reference}</span>
                        <button type="button" className="btn-close" aria-label={t('actions.close')} onClick={onClose} />
                    </div>
                    <div className="modal-body">
                        <p className="mb-0" style={{ whiteSpace: 'pre-wrap' }}>
                            {reading.text}
                        </p>
                    </div>
                </div>
            </div>
        </div>
    )
}
