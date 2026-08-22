import { useTranslation } from 'react-i18next'
import type { User } from '@/types'

interface RoleChipProps {
    role: User['role']
}

const CLASSES: Record<User['role'], string> = {
    superadmin: 'role-chip-super',
    admin: 'role-chip-admin',
    secretary: 'role-chip-secretary',
    user: '',
}

export default function RoleChip({ role }: RoleChipProps) {
    const { t } = useTranslation('common')

    return <span className={`role-chip ${CLASSES[role]}`}>{t(`roleChip.${role}`)}</span>
}
