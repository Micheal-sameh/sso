import type { FormEvent } from 'react'
import { useState } from 'react'
import { useForm } from '@inertiajs/react'
import { useTranslation } from 'react-i18next'
import AppLayout from '@/components/layouts/AppLayout'
import AvatarInitials from '@/components/shared/AvatarInitials'
import RoleChip from '@/components/shared/RoleChip'
import StatusBadge from '@/components/shared/StatusBadge'
import { calculateAge, formatDateDMY, isBirthdayToday } from '@/lib/date'
import type { User } from '@/types'

interface ShowProps {
    user: User
}

interface PasswordForm {
    current_password: string
    password: string
    password_confirmation: string
    code: string
}

interface DetailRow {
    icon: string
    label: string
    value: string
    mono?: boolean
}

export default function Show({ user }: ShowProps) {
    const { t } = useTranslation('account')
    const [showPasswordModal, setShowPasswordModal] = useState(false)
    const passwordForm = useForm<PasswordForm>({
        current_password: '',
        password: '',
        password_confirmation: '',
        code: '',
    })

    const closePasswordModal = () => {
        setShowPasswordModal(false)
        passwordForm.reset()
        passwordForm.clearErrors()
    }

    const submitPassword = (e: FormEvent) => {
        e.preventDefault()
        passwordForm.post('/account/password', {
            preserveScroll: true,
            onSuccess: () => {
                setShowPasswordModal(false)
                passwordForm.reset()
            },
        })
    }

    const detailRowRenderer = (row: DetailRow) => (
        <li key={row.label} className="list-group-item d-flex align-items-center gap-3 p-3">
            <div className="icon-tile text-ava-primary flex-shrink-0">
                <i className={`bi ${row.icon}`} />
            </div>
            <div className="min-w-0">
                <div className="record-card-label mb-1">{row.label}</div>
                <div className={row.mono ? 'code-chip text-uppercase' : 'fw-semibold text-truncate'}>{row.value}</div>
            </div>
        </li>
    )

    const age = calculateAge(user.date_of_birth)
    const isBirthday = isBirthdayToday(user.date_of_birth)

    const detailRows: DetailRow[] = [
        { icon: 'bi-credit-card-2-front', label: t('profile.membershipCode'), value: user.membership_code, mono: true },
        { icon: 'bi-telephone', label: t('profile.phone'), value: user.phone ?? t('profile.notSet') },
        { icon: 'bi-envelope', label: t('profile.email'), value: user.email ?? t('profile.notSet') },
        { icon: 'bi-cake2', label: t('profile.dateOfBirth'), value: formatDateDMY(user.date_of_birth) ?? t('profile.notSet') },
        ...(age !== null ? [{ icon: 'bi-hourglass-split', label: t('profile.age'), value: t('profile.ageYears', { count: age }) }] : []),
        { icon: 'bi-signpost-split', label: t('profile.stage'), value: user.stage.name },
        {
            icon: 'bi-collection',
            label: t('profile.groups'),
            value: user.groups.length > 0 ? user.groups.map((g) => g.name).join(', ') : t('profile.none'),
        },
        {
            icon: 'bi-patch-check',
            label: t('profile.emailVerified'),
            value: user.email_verified_at
                ? formatDateDMY(user.email_verified_at.slice(0, 10)) ?? t('profile.verified')
                : t('profile.notVerified'),
        },
        {
            icon: 'bi-shield-lock',
            label: t('profile.twoFactorAuthentication'),
            value: user.mfa_enabled ? t('profile.enabled') : t('profile.disabled'),
        },
        { icon: 'bi-calendar-plus', label: t('profile.accountCreated'), value: user.created_at ?? t('profile.unknown') },
        { icon: 'bi-clock-history', label: t('profile.lastUpdated'), value: user.updated_at ?? t('profile.unknown') },
    ]
    const detailRowsMidpoint = Math.ceil(detailRows.length / 2)
    const detailRowsLeft = detailRows.slice(0, detailRowsMidpoint)
    const detailRowsRight = detailRows.slice(detailRowsMidpoint)

    return (
        <AppLayout title={t('profile.title')}>
            {user.requires_data_update && (
                <div className="ava-card mb-4 p-3 d-flex align-items-start gap-2 border-warning">
                    <i className="bi bi-exclamation-triangle text-warning mt-1" />
                    <span>{t('profile.requiresDataUpdate')}</span>
                </div>
            )}

            {isBirthday && (
                <div className="ava-card mb-4 p-3 d-flex align-items-center gap-2">
                    <i className="bi bi-balloon-heart-fill text-ava-primary fs-4" />
                    <span className="fw-semibold">🎉 {t('profile.happyBirthday', { name: user.name })}</span>
                </div>
            )}

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

            <div className="d-flex justify-content-between align-items-center mb-3">
                <h5 className="mb-0">{t('profile.accountDetails')}</h5>
                <button type="button" className="btn btn-outline-primary btn-sm" onClick={() => setShowPasswordModal(true)}>
                    <i className="bi bi-shield-lock me-1" />
                    {t('profile.changePassword')}
                </button>
            </div>
            <div className="ava-card">
                <div className="row g-0">
                    <div className="col-lg-6 border-end">
                        <ul className="list-group list-group-flush">{detailRowsLeft.map(detailRowRenderer)}</ul>
                    </div>
                    <div className="col-lg-6">
                        <ul className="list-group list-group-flush">{detailRowsRight.map(detailRowRenderer)}</ul>
                    </div>
                </div>
            </div>

            <div className="app-requesting-banner mt-3 d-flex align-items-start gap-2">
                <i className="bi bi-info-circle mt-1" />
                <span>{t('profile.managedByChurchSecretary')}</span>
            </div>

            {showPasswordModal && (
                <div className="modal d-block modal-backdrop-custom" tabIndex={-1}>
                    <div className="modal-dialog modal-dialog-centered">
                        <div className="modal-content">
                            <form onSubmit={submitPassword}>
                                <div className="modal-header">
                                    <h5 className="modal-title">{t('profile.changePassword')}</h5>
                                    <button type="button" className="btn-close" aria-label={t('profile.close')} onClick={closePasswordModal} />
                                </div>
                                <div className="modal-body">
                                    <p className="text-ava-muted text-tiny mb-3">{t('profile.strongPasswordHint')}</p>

                                    <div className="mb-3">
                                        <label className="form-label" htmlFor="current_password">{t('profile.currentPassword')}</label>
                                        <input
                                            id="current_password"
                                            type="password"
                                            className="form-control"
                                            autoFocus
                                            value={passwordForm.data.current_password}
                                            onChange={(e) => passwordForm.setData('current_password', e.target.value)}
                                        />
                                        {passwordForm.errors.current_password && (
                                            <div className="text-danger small mt-1">{passwordForm.errors.current_password}</div>
                                        )}
                                    </div>

                                    <div className="mb-3">
                                        <label className="form-label" htmlFor="password">{t('profile.newPassword')}</label>
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

                                    <div className={user.mfa_enabled ? 'mb-3' : 'mb-1'}>
                                        <label className="form-label" htmlFor="password_confirmation">{t('profile.confirmNewPassword')}</label>
                                        <input
                                            id="password_confirmation"
                                            type="password"
                                            className="form-control"
                                            value={passwordForm.data.password_confirmation}
                                            onChange={(e) => passwordForm.setData('password_confirmation', e.target.value)}
                                        />
                                    </div>

                                    {user.mfa_enabled && (
                                        <div className="mb-1">
                                            <label className="form-label" htmlFor="code">{t('profile.authenticationCode')}</label>
                                            <p className="text-ava-muted text-tiny mb-2">{t('profile.authenticationCodeHint')}</p>
                                            <input
                                                id="code"
                                                type="text"
                                                inputMode="numeric"
                                                autoComplete="one-time-code"
                                                className="form-control"
                                                value={passwordForm.data.code}
                                                onChange={(e) => passwordForm.setData('code', e.target.value)}
                                            />
                                            {passwordForm.errors.code && (
                                                <div className="text-danger small mt-1">{passwordForm.errors.code}</div>
                                            )}
                                        </div>
                                    )}
                                </div>
                                <div className="modal-footer">
                                    <button type="button" className="btn btn-outline-primary" onClick={closePasswordModal}>
                                        {t('common:actions.cancel')}
                                    </button>
                                    <button type="submit" className="btn btn-primary" disabled={passwordForm.processing}>
                                        <i className="bi bi-shield-lock me-1" />
                                        {t('profile.updatePassword')}
                                    </button>
                                </div>
                            </form>
                        </div>
                    </div>
                </div>
            )}
        </AppLayout>
    )
}
