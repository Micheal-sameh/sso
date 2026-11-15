interface EmptyStateProps {
    icon: string
    message: string
}

/** Dashed empty panel shown in place of a table with no rows. */
export default function EmptyState({ icon, message }: EmptyStateProps) {
    return (
        <div className="empty-panel">
            <div className="empty-illustration">
                <i className={`bi ${icon}`} />
            </div>
            <p className="text-ava-muted mb-0">{message}</p>
        </div>
    )
}
