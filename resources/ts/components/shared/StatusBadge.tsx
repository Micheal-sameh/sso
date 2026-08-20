type Status = 'active' | 'revoked' | 'enabled' | 'disabled' | 'internal' | 'external' | 'warning'

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
    warning: 'Needs Update',
}

export default function StatusBadge({ status }: StatusBadgeProps) {
    return (
        <span className={`status-pill status-pill-${status}`}>
            <span className="status-pill-dot" />
            {LABELS[status]}
        </span>
    )
}
