interface SpiritualNoteProgressRingProps {
    checked: number
    total: number
    size?: number
}

export default function SpiritualNoteProgressRing({ checked, total, size = 56 }: SpiritualNoteProgressRingProps) {
    const pct = total > 0 ? Math.round((checked / total) * 100) : 0
    const isComplete = total > 0 && checked === total
    const color = pct >= 100 ? '#198754' : pct >= 50 ? '#ffc107' : '#dc3545'
    const innerSize = size - 12

    return (
        <div
            className="rounded-circle d-flex align-items-center justify-content-center flex-shrink-0"
            style={{
                width: size,
                height: size,
                background: isComplete ? color : `conic-gradient(${color} ${pct * 3.6}deg, var(--bs-tertiary-bg, #e9ecef) 0deg)`,
                transition: 'background 0.3s ease',
            }}
        >
            <div
                className="rounded-circle d-flex align-items-center justify-content-center"
                style={{ width: innerSize, height: innerSize, background: '#fff' }}
            >
                {isComplete ? (
                    <span style={{ fontSize: innerSize * 0.45, lineHeight: 1 }}>🎉</span>
                ) : (
                    <span className="fw-semibold small">{pct}%</span>
                )}
            </div>
        </div>
    )
}
