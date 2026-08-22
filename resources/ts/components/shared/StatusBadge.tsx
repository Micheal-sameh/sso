import { useTranslation } from 'react-i18next'

type Status = 'active' | 'revoked' | 'enabled' | 'disabled' | 'internal' | 'external' | 'warning'

interface StatusBadgeProps {
    status: Status
}

export default function StatusBadge({ status }: StatusBadgeProps) {
    const { t } = useTranslation('common')

    return (
        <span className={`status-pill status-pill-${status}`}>
            <span className="status-pill-dot" />
            {t(`statusBadge.${status}`)}
        </span>
    )
}
