type Status = 'active' | 'revoked' | 'enabled' | 'disabled' | 'internal' | 'external'

interface StatusBadgeProps {
    status: Status
}

const LABELS: Record<Status, string> = {
    active: 'Active',
    revoked: 'Revoked',
    enabled: 'Enabled',
    disabled: 'Disabled',
    internal: 'Internal',
    external: 'External',
}

export default function StatusBadge({ status }: StatusBadgeProps) {
    return (
        <span className={`status-pill status-pill-${status}`}>
            <span className="status-pill-dot" />
            {LABELS[status]}
        </span>
    )
}
