interface AvaLogoProps {
    size?: number
}

const LOGO_ASPECT_RATIO = 1400 / 1136

export default function AvaLogo({ size = 40 }: AvaLogoProps) {
    return (
        <img
            src="/images/logo.png"
            alt="Avarewase"
            width={size * LOGO_ASPECT_RATIO}
            height={size}
            style={{ objectFit: 'contain' }}
        />
    )
}
