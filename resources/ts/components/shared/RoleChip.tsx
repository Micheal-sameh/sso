import type { User } from '@/types'

interface RoleChipProps {
    role: User['role']
}

const LABELS: Record<User['role'], string> = {
    superadmin: 'Super Admin',
    admin: 'Admin',
    user: 'User',
}

const CLASSES: Record<User['role'], string> = {
    superadmin: 'role-chip-super',
    admin: 'role-chip-admin',
    user: '',
}

export default function RoleChip({ role }: RoleChipProps) {
    return <span className={`role-chip ${CLASSES[role]}`}>{LABELS[role]}</span>
}
