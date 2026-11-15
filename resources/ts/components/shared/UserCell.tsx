import AvatarInitials from '@/components/shared/AvatarInitials'

interface UserCellProps {
    user: { name: string; email?: string | null; avatar?: string | null }
    size?: number
}

/** Avatar with the name and email stacked beside it, as in the Figma tables. */
export default function UserCell({ user, size = 36 }: UserCellProps) {
    return (
        <div className="user-cell">
            {user.avatar ? (
                <img src={user.avatar} alt="" width={size} height={size} className="rounded-circle" />
            ) : (
                <AvatarInitials name={user.name} size={size} />
            )}
            <div className="min-w-0">
                <div className="fw-semibold">{user.name}</div>
                {user.email && <div className="user-cell-email cell-truncate-220 text-truncate">{user.email}</div>}
            </div>
        </div>
    )
}
