interface AvaLogoProps {
    size?: number
    showText?: boolean
    textColor?: string
}

export default function AvaLogo({ size = 40, showText = false, textColor = '#7B1C2E' }: AvaLogoProps) {
    return (
        <div className="d-flex align-items-center gap-2">
            <svg width={size} height={size} viewBox="0 0 100 100" xmlns="http://www.w3.org/2000/svg">
                <circle cx="50" cy="50" r="48" fill="#7B1C2E" />
                <rect x="44" y="20" width="12" height="60" fill="#B8860B" />
                <rect x="20" y="44" width="60" height="12" fill="#B8860B" />
            </svg>
            {showText && (
                <span style={{ fontFamily: 'Georgia, serif', fontSize: size * 0.5, color: textColor }}>
                    Avarewase
                </span>
            )}
        </div>
    )
}
