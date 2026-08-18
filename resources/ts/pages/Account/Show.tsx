import type { FormEvent } from 'react'
import { useForm } from '@inertiajs/react'
import AppLayout from '@/components/layouts/AppLayout'
import AvatarInitials from '@/components/shared/AvatarInitials'
import RoleChip from '@/components/shared/RoleChip'
import StatusBadge from '@/components/shared/StatusBadge'
import { calculateAge, formatDateDMY } from '@/lib/date'
import type { User } from '@/types'

interface ShowProps {
    user: User
}

interface PasswordForm {
    current_password: string
    password: string
    password_confirmation: string
}

interface DetailRow {
    icon: string
    label: string
    value: string
    mono?: boolean
}

export default function Show({ user }: ShowProps) {
    const passwordForm = useForm<PasswordForm>({
        current_password: '',
        password: '',
        password_confirmation: '',
    })

    const submitPassword = (e: FormEvent) => {
        e.preventDefault()
        passwordForm.post('/account/password', {
            preserveScroll: true,
            onSuccess: () => passwordForm.reset(),
        })
    }

    const age = calculateAge(user.date_of_birth)

    const detailRows: DetailRow[] = [
        { icon: 'bi-envelope', label: 'Email', value: user.email },
        { icon: 'bi-telephone', label: 'Phone', value: user.phone ?? 'Not set' },
        { icon: 'bi-cake2', label: 'Date of Birth', value: formatDateDMY(user.date_of_birth) ?? 'Not set' },
        ...(age !== null ? [{ icon: 'bi-hourglass-split', label: 'Age', value: `${age} years` }] : []),
        { icon: 'bi-credit-card-2-front', label: 'Membership Code', value: user.membership_code, mono: true },
        { icon: 'bi-signpost-split', label: 'Stage', value: user.stage.name },
        { icon: 'bi-collection', label: 'Groups', value: user.groups.length > 0 ? user.groups.map((g) => g.name).join(', ') : 'None' },
    ]

    return (
        <AppLayout title="Identity">
            <div className="ava-card mb-4">
                <div className="card-accent card-accent-primary" />
                <div className="p-4 d-flex flex-column flex-sm-row align-items-sm-center gap-3">
                    {user.avatar ? (
                        <img
                            src={user.avatar}
                            alt={user.name}
                            width={72}
                            height={72}
                            className="rounded-circle object-fit-cover"
                        />
                    ) : (
                        <AvatarInitials name={user.name} size={72} />
                    )}
                    <div className="min-w-0">
                        <h4 className="mb-1">{user.name}</h4>
                        <div className="d-flex flex-wrap gap-2">
                            <RoleChip role={user.role} />
                            <StatusBadge status={user.is_active ? 'active' : 'revoked'} />
                            {user.groups.map((group) => (
                                <span key={group.id} className="scope-chip">
                                    {group.name}
                                </span>
                            ))}
                        </div>
                    </div>
                </div>
            </div>

            <div className="row g-4">
                <div className="col-lg-6">
                    <h5 className="mb-3">Account Details</h5>
                    <div className="ava-card">
                        <ul className="list-group list-group-flush">
                            {detailRows.map((row) => (
                                <li key={row.label} className="list-group-item d-flex align-items-center gap-3 p-3">
                                    <div className="icon-tile text-ava-primary flex-shrink-0">
                                        <i className={`bi ${row.icon}`} />
                                    </div>
                                    <div className="min-w-0">
                                        <div className="record-card-label mb-1">{row.label}</div>
                                        <div className={row.mono ? 'code-chip text-uppercase' : 'fw-semibold text-truncate'}>{row.value}</div>
                                    </div>
                                </li>
                            ))}
                        </ul>
                    </div>

                    <div className="app-requesting-banner mt-3 d-flex align-items-start gap-2">
                        <i className="bi bi-info-circle mt-1" />
                        <span>Your profile details and photo are managed by an administrator. Contact one to make changes.</span>
                    </div>
                </div>

                <div className="col-lg-6">
                    <h5 className="mb-1">Change Password</h5>
                    <p className="text-ava-muted text-tiny mb-3">Use a strong password that you don&apos;t reuse elsewhere.</p>

                    <div className="ava-card p-4">
                        <form onSubmit={submitPassword}>
                            <div className="mb-3">
                                <label className="form-label" htmlFor="current_password">Current Password</label>
                                <input
                                    id="current_password"
                                    type="password"
                                    className="form-control"
                                    value={passwordForm.data.current_password}
                                    onChange={(e) => passwordForm.setData('current_password', e.target.value)}
                                />
                                {passwordForm.errors.current_password && (
                                    <div className="text-danger small mt-1">{passwordForm.errors.current_password}</div>
                                )}
                            </div>

                            <div className="mb-3">
                                <label className="form-label" htmlFor="password">New Password</label>
                                <input
                                    id="password"
                                    type="password"
                                    className="form-control"
                                    value={passwordForm.data.password}
                                    onChange={(e) => passwordForm.setData('password', e.target.value)}
                                />
                                {passwordForm.errors.password && (
                                    <div className="text-danger small mt-1">{passwordForm.errors.password}</div>
                                )}
                            </div>

                            <div className="mb-4">
                                <label className="form-label" htmlFor="password_confirmation">Confirm New Password</label>
                                <input
                                    id="password_confirmation"
                                    type="password"
                                    className="form-control"
                                    value={passwordForm.data.password_confirmation}
                                    onChange={(e) => passwordForm.setData('password_confirmation', e.target.value)}
                                />
                            </div>

                            <button type="submit" className="btn btn-primary" disabled={passwordForm.processing}>
                                <i className="bi bi-shield-lock me-1" />
                                Update Password
                            </button>
                        </form>
                    </div>
                </div>
            </div>
        </AppLayout>
    )
}
