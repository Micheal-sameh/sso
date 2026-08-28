import { router } from '@inertiajs/react'
import { useTranslation } from 'react-i18next'
import type { PaginatedData } from '@/types'

interface PaginationProps {
    pagination: Pick<PaginatedData<unknown>, 'links' | 'current_page' | 'last_page'>
}

const FIRST_BLOCK_SIZE = 4
const SIBLING_COUNT = 3

function urlForPage(links: PaginatedData<unknown>['links'], page: number): string | null {
    const sample = links.find((link) => link.url !== null)
    if (!sample?.url) {
        return null
    }

    const url = new URL(sample.url, window.location.origin)
    url.searchParams.set('page', String(page))
    return url.toString()
}

function visit(url: string | null) {
    if (!url) {
        return
    }

    router.visit(url, { preserveState: true, preserveScroll: true })
}

/** Full link list straight from Laravel's own paginator windowing. */
function DesktopPagination({ links }: { links: PaginatedData<unknown>['links'] }) {
    const { t } = useTranslation('common')

    return (
        <nav aria-label={t('pagination.ariaLabel')} className="pagination-scroll d-none d-lg-block">
            <ul className="pagination flex-nowrap mb-0">
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

/** Condensed 1-4 … current±3 window, sized to fit small screens. */
function MobilePagination({
    links,
    currentPage,
    lastPage,
}: {
    links: PaginatedData<unknown>['links']
    currentPage: number
    lastPage: number
}) {
    const { t } = useTranslation('common')

    const previous = links[0]
    const next = links[links.length - 1]

    const firstBlock: number[] = []
    for (let page = 1; page <= Math.min(FIRST_BLOCK_SIZE, lastPage); page++) {
        firstBlock.push(page)
    }

    const windowStart = Math.max(1, currentPage - SIBLING_COUNT)
    const windowEnd = Math.min(lastPage, currentPage + SIBLING_COUNT)
    const currentBlock: number[] = []
    for (let page = windowStart; page <= windowEnd; page++) {
        if (!firstBlock.includes(page)) {
            currentBlock.push(page)
        }
    }

    const hasGap = currentBlock.length > 0 && currentBlock[0] > firstBlock[firstBlock.length - 1] + 1
    const pages: (number | 'gap')[] = [...firstBlock, ...(hasGap ? (['gap'] as const) : []), ...currentBlock]

    return (
        <nav aria-label={t('pagination.ariaLabel')} className="pagination-scroll d-lg-none">
            <ul className="pagination flex-nowrap mb-0">
                <li className={`page-item ${!previous.url ? 'disabled' : ''}`}>
                    <button
                        type="button"
                        className="page-link"
                        disabled={!previous.url}
                        onClick={() => visit(previous.url)}
                        dangerouslySetInnerHTML={{ __html: previous.label }}
                    />
                </li>
                {pages.map((page, index) =>
                    page === 'gap' ? (
                        <li key={`gap-${index}`} className="page-item disabled">
                            <span className="page-link">…</span>
                        </li>
                    ) : (
                        <li key={page} className={`page-item ${page === currentPage ? 'active' : ''}`}>
                            <button type="button" className="page-link" onClick={() => visit(urlForPage(links, page))}>
                                {page}
                            </button>
                        </li>
                    ),
                )}
                <li className={`page-item ${!next.url ? 'disabled' : ''}`}>
                    <button
                        type="button"
                        className="page-link"
                        disabled={!next.url}
                        onClick={() => visit(next.url)}
                        dangerouslySetInnerHTML={{ __html: next.label }}
                    />
                </li>
            </ul>
        </nav>
    )
}

export default function Pagination({ pagination }: PaginationProps) {
    const { links, current_page: currentPage, last_page: lastPage } = pagination

    if (lastPage <= 1) {
        return null
    }

    return (
        <>
            <MobilePagination links={links} currentPage={currentPage} lastPage={lastPage} />
            <DesktopPagination links={links} />
        </>
    )
}
