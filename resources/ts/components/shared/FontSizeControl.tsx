import { useTranslation } from 'react-i18next'

interface FontSizeControlProps {
    canDecrease: boolean
    canIncrease: boolean
    onDecrease: () => void
    onIncrease: () => void
}

export default function FontSizeControl({ canDecrease, canIncrease, onDecrease, onIncrease }: FontSizeControlProps) {
    const { t } = useTranslation('common')

    return (
        <div className="d-flex align-items-center gap-2">
            <span className="text-ava-muted text-tiny">{t('fontSize.label')}</span>
            <button
                type="button"
                className="btn btn-sm btn-outline-secondary"
                aria-label={t('fontSize.decrease')}
                disabled={!canDecrease}
                onClick={onDecrease}
            >
                A-
            </button>
            <button
                type="button"
                className="btn btn-sm btn-outline-secondary"
                aria-label={t('fontSize.increase')}
                disabled={!canIncrease}
                onClick={onIncrease}
            >
                A+
            </button>
        </div>
    )
}
