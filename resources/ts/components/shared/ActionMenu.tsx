export interface ActionMenuItem {
    label: string
    icon?: string
    onClick: () => void
    disabled?: boolean
    title?: string
    danger?: boolean
}

interface ActionMenuProps {
    items: ActionMenuItem[]
}

/** Row actions for the admin tables, shown as inline icon buttons (label as tooltip). */
export default function ActionMenu({ items }: ActionMenuProps) {
    return (
        <div className="action-icons" role="group">
            {items.map((item) => (
                <button
                    key={item.label}
                    type="button"
                    className={`action-icon-btn ${item.danger ? 'is-danger' : ''}`}
                    disabled={item.disabled}
                    title={item.title ?? item.label}
                    aria-label={item.label}
                    onClick={item.onClick}
                >
                    <i className={`bi ${item.icon ?? 'bi-circle'}`} />
                </button>
            ))}
        </div>
    )
}
