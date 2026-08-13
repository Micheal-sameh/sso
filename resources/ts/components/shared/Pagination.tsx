import { router } from '@inertiajs/react'
import type { PaginatedData } from '@/types'

interface PaginationProps {
    links: PaginatedData<unknown>['links']
}

export default function Pagination({ links }: PaginationProps) {
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
        <nav aria-label="Pagination">
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
