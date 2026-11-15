import { useTranslation } from 'react-i18next'

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

/** The "⋯" row-action dropdown used by the admin tables. */
export default function ActionMenu({ items }: ActionMenuProps) {
    const { t } = useTranslation('common')

    return (
        <div className="dropdown">
            <button
                type="button"
                className="action-menu-btn dropdown-toggle"
                data-bs-toggle="dropdown"
                // `fixed` so the menu isn't clipped by the table's scroll container.
                data-bs-config='{"popperConfig":{"strategy":"fixed"}}'
                aria-expanded="false"
                aria-label={t('brand.moreActions')}
            >
                <i className="bi bi-three-dots" />
            </button>
            <ul className="dropdown-menu dropdown-menu-end shadow">
                {items.map((item) => (
                    <li key={item.label}>
                        <button
                            type="button"
                            className={`dropdown-item ${item.danger ? 'text-danger' : ''}`}
                            disabled={item.disabled}
                            title={item.title}
                            onClick={item.onClick}
                        >
                            {item.icon && <i className={`bi ${item.icon} me-2`} />}
                            {item.label}
                        </button>
                    </li>
                ))}
            </ul>
        </div>
    )
}
