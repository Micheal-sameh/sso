interface AvaLogoProps {
    size?: number
    showText?: boolean
    textColor?: string
}

export default function AvaLogo({ size = 40, showText = false, textColor = '#1B2A4A' }: AvaLogoProps) {
    return (
        <div className="d-flex align-items-center gap-2">
            <img
                src="/images/logo.png"
                alt="Avarewase"
                width={size}
                height={size}
                style={{ objectFit: 'contain' }}
            />
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
