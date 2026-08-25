import { useState } from 'react'
import { router } from '@inertiajs/react'
import { useTranslation } from 'react-i18next'
import AppLayout from '@/components/layouts/AppLayout'
import SpiritualNoteProgressRing from '@/components/shared/SpiritualNoteProgressRing'
import type { SpiritualNoteEntry } from '@/types'

interface ShowProps {
    entries: SpiritualNoteEntry[]
    date: string
    today: string
}

export default function Show({ entries: initialEntries, date, today }: ShowProps) {
    const { t, i18n } = useTranslation('spiritualNotes')
    const dir = i18n.language === 'ar' ? 'rtl' : 'ltr'
    const isToday = date === today
    const [entries, setEntries] = useState<SpiritualNoteEntry[]>(initialEntries)
    const [saving, setSaving] = useState(false)
    const checkedCount = entries.filter((entry) => entry.is_checked).length

    const goToDate = (newDate: string) => {
        router.get('/spiritual-notes', newDate === today ? {} : { date: newDate }, { preserveScroll: true })
    }

    const toggleChecked = (labelId: number) => {
        if (!isToday) return
        setEntries((prev) => prev.map((entry) => (entry.label_id === labelId ? { ...entry, is_checked: !entry.is_checked } : entry)))
    }

    const updateNote = (labelId: number, note: string) => {
        if (!isToday) return
        setEntries((prev) => prev.map((entry) => (entry.label_id === labelId ? { ...entry, note } : entry)))
    }

    const save = () => {
        setSaving(true)
        router.post(
            '/spiritual-notes',
            { entries: entries.map(({ label_id, is_checked, note }) => ({ label_id, is_checked, note })) },
            { preserveScroll: true, onFinish: () => setSaving(false) },
        )
    }

    return (
        <AppLayout title={t('title')}>
            <div className="ava-card p-3 mb-3" dir={dir}>
                <div className="row g-3 align-items-end">
                    <div className="col-12 col-sm-6">
                        <label className="form-label text-ava-muted text-tiny mb-1" htmlFor="spiritual-note-date-picker">
                            {t('date')}
                        </label>
                        <input
                            id="spiritual-note-date-picker"
                            type="date"
                            className="form-control"
                            max={today}
                            value={date}
                            onChange={(e) => e.target.value && goToDate(e.target.value)}
                        />
                    </div>
                    <div className="col-12 col-sm-auto">
                        {!isToday && (
                            <button type="button" className="btn btn-outline-primary" onClick={() => goToDate(today)}>
                                {t('today')}
                            </button>
                        )}
                    </div>
                </div>
            </div>

            <div className="ava-card p-3 mb-3" dir={dir}>
                <div className="d-flex align-items-center gap-3 mb-3">
                    <SpiritualNoteProgressRing checked={checkedCount} total={entries.length} />
                    <div>
                        <h5 className="mb-0">{isToday ? t('todayTitle') : date}</h5>
                        <p className="mb-0 text-ava-muted small">
                            {entries.length > 0 && t('progressCount', { checked: checkedCount, total: entries.length })}
                        </p>
                    </div>
                </div>

                {!isToday && <p className="text-ava-muted small mb-3">{t('viewOnlyPastDay')}</p>}

                {entries.length === 0 && <p className="text-ava-muted mb-0">{t('empty')}</p>}

                {entries.map((entry) => (
                    <div key={entry.label_id} className="mb-3 pb-3 border-bottom">
                        <div className="form-check mb-2">
                            <input
                                id={`label-${entry.label_id}`}
                                type="checkbox"
                                className="form-check-input"
                                checked={entry.is_checked}
                                disabled={!isToday}
                                onChange={() => toggleChecked(entry.label_id)}
                            />
                            <label className="form-check-label fw-semibold" htmlFor={`label-${entry.label_id}`}>
                                {entry.label_name}
                            </label>
                        </div>
                        {isToday ? (
                            <textarea
                                className="form-control"
                                rows={2}
                                placeholder={t('notePlaceholder')}
                                value={entry.note ?? ''}
                                onChange={(e) => updateNote(entry.label_id, e.target.value)}
                            />
                        ) : (
                            entry.note && <p className="text-ava-muted mb-0">{entry.note}</p>
                        )}
                    </div>
                ))}

                {isToday && entries.length > 0 && (
                    <button type="button" className="btn btn-primary" onClick={save} disabled={saving}>
                        {saving ? t('saving') : t('save')}
                    </button>
                )}
            </div>
        </AppLayout>
    )
}
