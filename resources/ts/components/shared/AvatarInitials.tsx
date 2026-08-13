interface AvatarInitialsProps {
    name: string
    size?: number
}

function getInitials(name: string): string {
    const parts = name.trim().split(/\s+/)
    const initials = parts.length > 1 ? `${parts[0][0]}${parts[parts.length - 1][0]}` : parts[0]?.slice(0, 2)

    return (initials ?? '?').toUpperCase()
}

export default function AvatarInitials({ name, size = 32 }: AvatarInitialsProps) {
    return (
        <div
            className="avatar-initials rounded-circle d-flex align-items-center justify-content-center flex-shrink-0"
            style={{ width: size, height: size, fontSize: size * 0.4 }}
        >
            {getInitials(name)}
        </div>
    )
}
