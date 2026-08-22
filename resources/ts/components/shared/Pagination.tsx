import { router } from '@inertiajs/react'
import { useTranslation } from 'react-i18next'
import type { PaginatedData } from '@/types'

interface PaginationProps {
    links: PaginatedData<unknown>['links']
}

export default function Pagination({ links }: PaginationProps) {
    const { t } = useTranslation('common')

    if (links.length <= 3) {
        return null
    }

    const visit = (url: string | null) => {
        if (!url) {
            return
        }

        router.visit(url, { preserveState: true, preserveScroll: true })
    }

    return (
        <nav aria-label={t('pagination.ariaLabel')}>
            <ul className="pagination">
                {links.map((link, index) => (
                    <li key={index} className={`page-item ${link.active ? 'active' : ''} ${!link.url ? 'disabled' : ''}`}>
                        <button
                            type="button"
                            className="page-link"
                            disabled={!link.url}
                            onClick={() => visit(link.url)}
                            dangerouslySetInnerHTML={{ __html: link.label }}
                        />
                    </li>
                ))}
            </ul>
        </nav>
    )
}
