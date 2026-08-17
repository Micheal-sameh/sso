interface AvaLogoProps {
    size?: number
    showText?: boolean
    textColor?: string
}

export default function AvaLogo({ size = 40, showText = false, textColor = '#1B2A4A' }: AvaLogoProps) {
    return (
        <div className="d-flex align-items-center gap-2">
            <svg width={size} height={size} viewBox="0 0 100 100" xmlns="http://www.w3.org/2000/svg">
                <circle cx="50" cy="50" r="48" fill="#1B2A4A" />
                <rect x="44" y="20" width="12" height="60" fill="#3B82F6" />
                <rect x="20" y="44" width="60" height="12" fill="#3B82F6" />
            </svg>
            {showText && (
                <span
                    className="fw-bold"
                    style={{ fontFamily: 'var(--font-sans)', fontSize: size * 0.5, color: textColor, letterSpacing: '-0.02em' }}
                >
                    Avarewase
                </span>
            )}
        </div>
    )
}
