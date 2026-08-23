import { useState } from 'react'
import { Link, router } from '@inertiajs/react'
import { useTranslation } from 'react-i18next'
import AppLayout from '@/components/layouts/AppLayout'
import FontSizeControl from '@/components/shared/FontSizeControl'
import InAppLinkModal from '@/components/shared/InAppLinkModal'
import ReadingTextModal from '@/components/shared/ReadingTextModal'
import { useReadingFontSize } from '@/lib/readingFontSize'
import type { CopticCalendar, CopticReading } from '@/types'

interface ShowProps {
    coptic: CopticCalendar
    date: string
}

export default function Show({ coptic, date }: ShowProps) {
    const { t, i18n } = useTranslation('coptic')
    const { t: tCommon } = useTranslation('common')
    const [openUrl, setOpenUrl] = useState<string | null>(null)
    const [openReading, setOpenReading] = useState<CopticReading | null>(null)
    const { fontSize, increase, decrease, canIncrease, canDecrease } = useReadingFontSize()
    const dir = i18n.language === 'ar' ? 'rtl' : 'ltr'
    const today = new Date().toISOString().slice(0, 10)

    const goToDate = (newDate: string) => {
        router.get('/coptic', newDate === today ? {} : { date: newDate }, { preserveState: true, preserveScroll: true })
    }

    return (
        <AppLayout title={t('coptic.title')}>
            <div className="ava-card p-3 mb-3" dir={dir}>
                <div className="row g-3 align-items-end">
                    <div className="col-12 col-sm-6">
                        <label className="form-label text-ava-muted text-tiny mb-1" htmlFor="coptic-date-picker">
                            {t('coptic.gregorianDate')}
                        </label>
                        <input
                            id="coptic-date-picker"
                            type="date"
                            className="form-control"
                            value={date}
                            onChange={(e) => e.target.value && goToDate(e.target.value)}
                        />
                    </div>
                    <div className="col-12 col-sm-auto">
                        {date !== today && (
                            <button type="button" className="btn btn-outline-primary" onClick={() => goToDate(today)}>
                                {t('coptic.today')}
                            </button>
                        )}
                    </div>
                </div>

                <hr />

                <h5 className="mb-1">{t('coptic.copticDate')}</h5>
                <p className="mb-0 fs-5">{coptic.date ?? t('coptic.unavailable')}</p>
            </div>

            <div className="ava-card p-3 mb-3" dir={dir}>
                <div className="d-flex align-items-center justify-content-between mb-3">
                    <h5 className="mb-0">{t('coptic.readings')}</h5>
                    <FontSizeControl canDecrease={canDecrease} canIncrease={canIncrease} onDecrease={decrease} onIncrease={increase} />
                </div>
                {coptic.readings.length > 0 ? (
                    coptic.readings.map((section, sectionIndex) => (
                        <div key={sectionIndex} className={sectionIndex > 0 ? 'mt-4' : undefined}>
                            {section.title && <h6 className="text-ava-primary mb-2">{section.title}</h6>}
                            {section.subSections.map((subSection, subSectionIndex) => (
                                <div key={subSectionIndex} className="mb-3">
                                    {subSection.title && (
                                        <div className="text-ava-muted text-tiny mb-1">{subSection.title}</div>
                                    )}
                                    {subSection.readings.map((reading, readingIndex) => (
                                        <div key={readingIndex} className="mb-1">
                                            {reading.reference && reading.text ? (
                                                <button
                                                    type="button"
                                                    className="btn btn-link p-0 align-baseline text-start"
                                                    style={{ fontSize }}
                                                    onClick={() => setOpenReading(reading)}
                                                >
                                                    {reading.reference}
                                                </button>
                                            ) : (
                                                reading.reference && <span style={{ fontSize }}>{reading.reference}</span>
                                            )}
                                        </div>
                                    ))}
                                </div>
                            ))}
                        </div>
                    ))
                ) : (
                    <p className="text-ava-muted mb-0">{t('coptic.unavailable')}</p>
                )}
            </div>

            <div className="ava-card p-3 mb-3" dir={dir}>
                <h5 className="mb-2">{t('coptic.synaxarium')}</h5>
                {coptic.commemorations.length > 0 ? (
                    <ul className={`mb-0 ${dir === 'rtl' ? 'pe-3' : 'ps-3'}`}>
                        {coptic.commemorations.map((commemoration, index) => (
                            <li key={index} className="mb-1">
                                {commemoration.url ? (
                                    <button
                                        type="button"
                                        className="btn btn-link p-0 align-baseline"
                                        onClick={() => setOpenUrl(commemoration.url)}
                                    >
                                        {commemoration.name}
                                    </button>
                                ) : (
                                    commemoration.name
                                )}
                            </li>
                        ))}
                    </ul>
                ) : (
                    <p className="text-ava-muted mb-0">{t('coptic.noCommemoration')}</p>
                )}
            </div>

            <Link href="/home" className="btn btn-link p-0">
                <i className="bi bi-arrow-left me-1" />
                {tCommon('nav.home')}
            </Link>

            <InAppLinkModal url={openUrl} onClose={() => setOpenUrl(null)} />
            <ReadingTextModal reading={openReading} fontSize={fontSize} onClose={() => setOpenReading(null)} />
        </AppLayout>
    )
}
