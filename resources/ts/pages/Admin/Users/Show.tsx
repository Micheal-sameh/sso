import { useState } from 'react'
import { router, usePage } from '@inertiajs/react'
import { useTranslation } from 'react-i18next'
import AdminLayout from '@/components/layouts/AdminLayout'
import AvatarInitials from '@/components/shared/AvatarInitials'
import { useConfirm } from '@/components/shared/ConfirmDialog'
import RoleChip from '@/components/shared/RoleChip'
import StatusBadge from '@/components/shared/StatusBadge'
import { calculateAge, formatDateDMY, isBirthdayToday } from '@/lib/date'
import type { SharedProps, SpecificClientAccess, Token, User, UserGroupOption } from '@/types'

interface ShowProps {
    viewedUser: User
    tokens: Token[]
    specificClients: SpecificClientAccess[]
    groups: UserGroupOption[]
}

export default function Show({ viewedUser, tokens, specificClients, groups }: ShowProps) {
    const { t } = useTranslation(['admin', 'common'])
    const { csrf_token } = usePage<SharedProps>().props
    const confirm = useConfirm()
    const dobAge = calculateAge(viewedUser.date_of_birth)
    const isBirthday = isBirthdayToday(viewedUser.date_of_birth)
    const familyMembers = viewedUser.family
        ? [
              viewedUser.family.father && { ...viewedUser.family.father, role: 'father' as const },
              viewedUser.family.mother && { ...viewedUser.family.mother, role: 'mother' as const },
              ...viewedUser.family.children.map((child) => ({ ...child, role: 'child' as const })),
          ].filter((member): member is { id: number; name: string; role: 'father' | 'mother' | 'child' } => member !== null && member !== undefined && member.id !== viewedUser.id)
        : []
    const [resetLinkBusy, setResetLinkBusy] = useState(false)
    const [resetLinkCopied, setResetLinkCopied] = useState(false)
    const [avatarLinkBusy, setAvatarLinkBusy] = useState(false)
    const [avatarLinkCopied, setAvatarLinkCopied] = useState(false)

    const fetchLink = async (endpoint: string): Promise<string | null> => {
        const response = await fetch(endpoint, {
            method: 'POST',
            headers: { 'X-CSRF-TOKEN': csrf_token, Accept: 'application/json' },
        })

        if (!response.ok) {
            return null
        }

        const data = (await response.json()) as { link: string }

        return data.link
    }

    const copyResetLink = async () => {
        setResetLinkBusy(true)
        const link = await fetchLink(`/admin/users/${viewedUser.id}/reset-link`)
        setResetLinkBusy(false)

        if (!link) {
            return
        }

        await navigator.clipboard.writeText(link)
        setResetLinkCopied(true)
        setTimeout(() => setResetLinkCopied(false), 2000)
    }

    const sendResetLinkViaWhatsApp = async () => {
        if (!viewedUser.phone) {
            return
        }

        setResetLinkBusy(true)
        const link = await fetchLink(`/admin/users/${viewedUser.id}/reset-link`)
        setResetLinkBusy(false)

        if (!link) {
            return
        }

        const message = t('users.resetLinkWhatsAppMessage', { name: viewedUser.name, link })
        const phoneDigits = viewedUser.phone.replace(/\D/g, '')
        window.open(`https://wa.me/${phoneDigits}?text=${encodeURIComponent(message)}`, '_blank', 'noopener,noreferrer')
    }

    const copyAvatarUploadLink = async () => {
        setAvatarLinkBusy(true)
        const link = await fetchLink(`/admin/users/${viewedUser.id}/avatar-upload-link`)
        setAvatarLinkBusy(false)

        if (!link) {
            return
        }

        await navigator.clipboard.writeText(link)
        setAvatarLinkCopied(true)
        setTimeout(() => setAvatarLinkCopied(false), 2000)
    }

    const sendAvatarUploadLinkViaWhatsApp = async () => {
        if (!viewedUser.phone) {
            return
        }

        setAvatarLinkBusy(true)
        const link = await fetchLink(`/admin/users/${viewedUser.id}/avatar-upload-link`)
        setAvatarLinkBusy(false)

        if (!link) {
            return
        }

        const message = t('users.avatarLinkWhatsAppMessage', { name: viewedUser.name, link })
        const phoneDigits = viewedUser.phone.replace(/\D/g, '')
        window.open(`https://wa.me/${phoneDigits}?text=${encodeURIComponent(message)}`, '_blank', 'noopener,noreferrer')
    }

    const toggleClientAccess = (client: SpecificClientAccess) => {
        if (client.has_access) {
            router.delete(`/admin/users/${viewedUser.id}/client-access/${client.id}`, { preserveScroll: true })
        } else {
            router.post(`/admin/users/${viewedUser.id}/client-access/${client.id}`, {}, { preserveScroll: true })
        }
    }

    const toggleGroup = (group: UserGroupOption) => {
        if (group.is_member) {
            router.delete(`/admin/users/${viewedUser.id}/groups/${group.id}`, { preserveScroll: true })
        } else {
            router.post(`/admin/users/${viewedUser.id}/groups/${group.id}`, {}, { preserveScroll: true })
        }
    }

    const forceLogout = async (token: Token) => {
        if (await confirm(t('users.confirmForceLogout', { name: viewedUser.name, client: token.client.name }), { confirmLabel: t('users.logOut') })) {
            router.delete(`/admin/tokens/${token.id}`, { preserveScroll: true })
        }
    }

    const forceLogoutAll = async () => {
        if (tokens.length === 0) {
            return
        }

        if (await confirm(t('users.confirmForceLogoutAll', { name: viewedUser.name, count: tokens.length }), { confirmLabel: t('users.logOutAll') })) {
            tokens.forEach((token) => router.delete(`/admin/tokens/${token.id}`, { preserveScroll: true }))
        }
    }

    const toggleActive = async () => {
        const message = viewedUser.is_active
            ? t('users.confirmDeactivateUser', { name: viewedUser.name })
            : t('users.confirmActivateUser', { name: viewedUser.name })

        if (await confirm(message, { confirmLabel: viewedUser.is_active ? t('actions.deactivate') : t('actions.activate') })) {
            router.patch(`/admin/users/${viewedUser.id}/toggle`, {}, { preserveScroll: true })
        }
    }

    const toggleRequiresDataUpdate = async () => {
        const message = viewedUser.requires_data_update
            ? t('users.confirmClearDataUpdate', { name: viewedUser.name })
            : t('users.confirmRequireDataUpdate', { name: viewedUser.name })

        if (
            await confirm(message, {
                confirmLabel: viewedUser.requires_data_update ? t('users.clearRequirement') : t('users.requireUpdate'),
            })
        ) {
            router.patch(`/admin/users/${viewedUser.id}/toggle-requires-data-update`, {}, { preserveScroll: true })
        }
    }

    const unlockLogin = async () => {
        if (
            await confirm(t('users.confirmUnlockLogin', { name: viewedUser.name }), {
                confirmLabel: t('users.unlockLogin'),
            })
        ) {
            router.post(`/admin/users/${viewedUser.id}/unlock-login`, {}, { preserveScroll: true })
        }
    }

    const resetMfa = async () => {
        if (
            await confirm(t('users.confirmResetMfa', { name: viewedUser.name }), {
                confirmLabel: t('users.resetMfa'),
            })
        ) {
            router.post(`/admin/users/${viewedUser.id}/mfa/reset`, {}, { preserveScroll: true })
        }
    }

    return (
        <AdminLayout
            title={t('users.showTitle')}
            action={
                <div className="d-flex gap-2">
                    <button
                        type="button"
                        className="btn btn-primary btn-sm"
                        onClick={() => router.visit(`/admin/users/${viewedUser.id}/edit`)}
                    >
                        <i className="bi bi-pencil me-1" />
                        {t('common:actions.edit')}
                    </button>
                    <button
                        type="button"
                        className="btn btn-outline-primary btn-sm"
                        onClick={toggleActive}
                    >
                        <i className={`bi ${viewedUser.is_active ? 'bi-slash-circle' : 'bi-check-circle'} me-1`} />
                        {viewedUser.is_active ? t('actions.deactivate') : t('actions.activate')}
                    </button>
                    <button type="button" className="btn btn-outline-primary btn-sm" onClick={() => router.visit('/admin/users')}>
                        <i className="bi bi-arrow-left me-1" />
                        {t('users.backToUsers')}
                    </button>
                </div>
            }
        >
            {isBirthday && (
                <div className="ava-card mb-4 p-3 d-flex align-items-center gap-2">
                    <i className="bi bi-balloon-heart-fill text-ava-primary fs-4" />
                    <span className="fw-semibold">{t('users.happyBirthday', { name: viewedUser.name })}</span>
                </div>
            )}

            <div className="ava-card mb-4">
                <div className="card-accent card-accent-primary" />
                <div className="p-4 d-flex flex-column flex-sm-row align-items-sm-center gap-3">
                    {viewedUser.avatar ? (
                        <img src={viewedUser.avatar} alt={viewedUser.name} width={80} height={80} className="rounded-circle object-fit-cover" />
                    ) : (
                        <AvatarInitials name={viewedUser.name} size={80} />
                    )}
                    <div className="flex-grow-1 min-w-0">
                        <h3 className="mb-2">{viewedUser.name}</h3>
                        <div className="text-ava-muted fs-6 mb-3 d-flex flex-wrap align-items-center gap-3">
                            {viewedUser.email && (
                                <span className="d-inline-flex align-items-center gap-1">
                                    {viewedUser.email}
                                    <a
                                        href={`mailto:${viewedUser.email}`}
                                        className="text-ava-muted"
                                        title={t('users.emailUser', { name: viewedUser.name })}
                                    >
                                        <i className="bi bi-envelope" />
                                    </a>
                                </span>
                            )}
                            {viewedUser.phone && (
                                <span className="d-inline-flex align-items-center gap-1">
                                    {viewedUser.phone}
                                    <a
                                        href={`https://wa.me/${viewedUser.phone.replace(/\D/g, '')}`}
                                        target="_blank"
                                        rel="noopener noreferrer"
                                        className="text-success"
                                        title={t('users.whatsappUser', { name: viewedUser.name })}
                                    >
                                        <i className="bi bi-whatsapp" />
                                    </a>
                                </span>
                            )}
                            <span className="d-inline-flex align-items-center gap-1">
                                <i className="bi bi-cake2" />
                                {formatDateDMY(viewedUser.date_of_birth) ?? t('users.dobNotSet')}
                                {dobAge !== null && <span>&nbsp;{t('users.ageYears', { age: dobAge })}</span>}
                            </span>
                        </div>
                        <div className="d-flex flex-wrap gap-2 chips-lg">
                            <span className="code-chip text-uppercase">{viewedUser.membership_code}</span>
                            <span className="role-chip">{viewedUser.stage.name}</span>
                            <RoleChip role={viewedUser.role} />
                            <StatusBadge status={viewedUser.is_active ? 'active' : 'revoked'} />
                            <StatusBadge status={viewedUser.is_admin ? 'enabled' : 'disabled'} />
                            {viewedUser.requires_data_update && <StatusBadge status="warning" />}
                            {viewedUser.groups.map((group) => (
                                <span key={group.id} className="scope-chip">
                                    {group.name}
                                </span>
                            ))}
                        </div>
                    </div>
                </div>
            </div>

            <h5 className="mb-3">{t('users.accountInformation')}</h5>
            <div className="ava-card mb-4">
                <div className="row g-0">
                    <div className="col-sm-6 col-lg-3 p-3 border-end border-bottom">
                        <div className="text-ava-muted text-tiny mb-1">{t('users.info.membershipCode')}</div>
                        <div className="fw-semibold text-uppercase">{viewedUser.membership_code}</div>
                    </div>
                    <div className="col-sm-6 col-lg-3 p-3 border-end border-bottom">
                        <div className="text-ava-muted text-tiny mb-1">{t('users.info.stage')}</div>
                        <div className="fw-semibold">
                            {viewedUser.stage.name} <span className="text-ava-muted">({viewedUser.stage.code})</span>
                        </div>
                    </div>
                    <div className="col-sm-6 col-lg-3 p-3 border-end border-bottom">
                        <div className="text-ava-muted text-tiny mb-1">{t('users.info.emailVerified')}</div>
                        <div className="fw-semibold">
                            {viewedUser.email_verified_at ? (
                                <span className="text-success">
                                    <i className="bi bi-patch-check-fill me-1" />
                                    {t('users.verified')}
                                </span>
                            ) : (
                                <span className="text-danger">
                                    <i className="bi bi-exclamation-circle me-1" />
                                    {t('users.notVerified')}
                                </span>
                            )}
                        </div>
                    </div>
                    <div className="col-sm-6 col-lg-3 p-3 border-bottom">
                        <div className="text-ava-muted text-tiny mb-1">{t('users.info.adminAccess')}</div>
                        <div className="fw-semibold">
                            {viewedUser.is_admin ? (
                                <span className="text-success">
                                    <i className="bi bi-shield-check me-1" />
                                    {t('users.granted')}
                                </span>
                            ) : (
                                <span className="text-ava-muted">
                                    <i className="bi bi-shield-slash me-1" />
                                    {t('users.none')}
                                </span>
                            )}
                        </div>
                    </div>
                    <div className="col-sm-6 col-lg-3 p-3 border-end">
                        <div className="text-ava-muted text-tiny mb-1">{t('users.info.accountCreated')}</div>
                        <div className="fw-semibold">{viewedUser.created_at ?? '—'}</div>
                    </div>
                    <div className="col-sm-6 col-lg-3 p-3 border-end">
                        <div className="text-ava-muted text-tiny mb-1">{t('users.info.lastUpdated')}</div>
                        <div className="fw-semibold">{viewedUser.updated_at ?? '—'}</div>
                    </div>
                    <div className="col-sm-6 col-lg-3 p-3 border-end">
                        <div className="text-ava-muted text-tiny mb-1">{t('users.info.groups')}</div>
                        <div className="fw-semibold">{viewedUser.groups.length > 0 ? viewedUser.groups.length : t('users.none')}</div>
                    </div>
                    <div className="col-sm-6 col-lg-3 p-3">
                        <div className="text-ava-muted text-tiny mb-1">{t('users.info.family')}</div>
                        {viewedUser.family ? (
                            <a href={`/admin/families/${viewedUser.family.id}`} className="fw-semibold">
                                {t('families.manageMembers')}
                            </a>
                        ) : (
                            <span className="text-ava-muted">{t('users.info.noFamily')}</span>
                        )}
                    </div>
                </div>
            </div>

            <h5 className="mb-3">{t('users.adminActions')}</h5>
            <div className="ava-card mb-4">
                <ul className="list-group list-group-flush">
                    <li className="list-group-item d-flex flex-wrap align-items-center gap-3 p-3">
                        <div className="icon-tile text-ava-primary flex-shrink-0">
                            <i className="bi bi-person-gear" />
                        </div>
                        <div className="min-w-0 flex-grow-1">
                            <div className="fw-semibold d-flex align-items-center gap-2">
                                {t('users.dataUpdateRequirement')}
                                {viewedUser.requires_data_update && <StatusBadge status="warning" />}
                            </div>
                            <div className="text-ava-muted text-tiny">
                                {viewedUser.requires_data_update
                                    ? t('users.dataUpdateBlocked')
                                    : t('users.dataUpdateHelp')}
                            </div>
                        </div>
                        <div className="d-flex flex-wrap gap-2">
                            <button
                                type="button"
                                className={`btn btn-sm text-nowrap ${viewedUser.requires_data_update ? 'btn-outline-primary' : 'btn-outline-danger'}`}
                                onClick={toggleRequiresDataUpdate}
                            >
                                <i className={`bi ${viewedUser.requires_data_update ? 'bi-check-circle' : 'bi-exclamation-triangle'} me-1`} />
                                {viewedUser.requires_data_update ? t('users.clearRequirement') : t('users.requireUpdate')}
                            </button>
                        </div>
                    </li>
                    <li className="list-group-item d-flex flex-wrap align-items-center gap-3 p-3">
                        <div className="icon-tile text-ava-primary flex-shrink-0">
                            <i className="bi bi-key" />
                        </div>
                        <div className="min-w-0 flex-grow-1">
                            <div className="fw-semibold">{t('users.passwordReset')}</div>
                            <div className="text-ava-muted text-tiny">{t('users.passwordResetHelp')}</div>
                        </div>
                        <div className="d-flex flex-wrap gap-2">
                            <button
                                type="button"
                                className="btn btn-outline-primary btn-sm text-nowrap"
                                disabled={resetLinkBusy}
                                onClick={copyResetLink}
                            >
                                <i className="bi bi-link-45deg me-1" />
                                {resetLinkCopied ? t('actions.copied') : t('actions.copyLink')}
                            </button>
                            {viewedUser.phone && (
                                <button
                                    type="button"
                                    className="btn btn-success btn-sm text-nowrap"
                                    disabled={resetLinkBusy}
                                    onClick={sendResetLinkViaWhatsApp}
                                >
                                    <i className="bi bi-whatsapp me-1" />
                                    {t('actions.whatsapp')}
                                </button>
                            )}
                        </div>
                    </li>
                    <li className="list-group-item d-flex flex-wrap align-items-center gap-3 p-3">
                        <div className="icon-tile text-ava-primary flex-shrink-0">
                            <i className="bi bi-shield-lock" />
                        </div>
                        <div className="min-w-0 flex-grow-1">
                            <div className="fw-semibold d-flex align-items-center gap-2">
                                {t('users.twoFactorAuth')}
                                <StatusBadge status={viewedUser.mfa_enabled ? 'enabled' : 'disabled'} />
                            </div>
                            {viewedUser.mfa_enabled ? (
                                <div className="text-ava-muted text-tiny">
                                    <div>{t('users.mfaEnrolledOn', { date: viewedUser.mfa_enabled_at })}</div>
                                    <div
                                        className={
                                            (viewedUser.mfa_recovery_codes_remaining ?? 0) <= 2 ? 'text-danger fw-semibold' : undefined
                                        }
                                    >
                                        {t('users.mfaRecoveryCodesRemaining', {
                                            remaining: viewedUser.mfa_recovery_codes_remaining ?? 0,
                                            total: viewedUser.mfa_recovery_codes_total,
                                        })}
                                    </div>
                                    <div>{t('users.mfaResetHelp')}</div>
                                </div>
                            ) : (
                                <div className="text-ava-muted text-tiny">{t('users.mfaNotEnrolled')}</div>
                            )}
                        </div>
                        <div className="d-flex flex-wrap gap-2">
                            <button
                                type="button"
                                className="btn btn-outline-danger btn-sm text-nowrap"
                                disabled={!viewedUser.mfa_enabled}
                                onClick={resetMfa}
                            >
                                <i className="bi bi-shield-x me-1" />
                                {t('users.resetMfa')}
                            </button>
                        </div>
                    </li>
                    <li className="list-group-item d-flex flex-wrap align-items-center gap-3 p-3">
                        <div className="icon-tile text-ava-primary flex-shrink-0">
                            <i className="bi bi-unlock" />
                        </div>
                        <div className="min-w-0 flex-grow-1">
                            <div className="fw-semibold">{t('users.unlockLogin')}</div>
                            <div className="text-ava-muted text-tiny">{t('users.unlockLoginHelp')}</div>
                        </div>
                        <div className="d-flex flex-wrap gap-2">
                            <button type="button" className="btn btn-outline-primary btn-sm text-nowrap" onClick={unlockLogin}>
                                <i className="bi bi-unlock me-1" />
                                {t('users.unlockLogin')}
                            </button>
                        </div>
                    </li>
                    <li className="list-group-item d-flex flex-wrap align-items-center gap-3 p-3">
                        <div className="icon-tile text-ava-primary flex-shrink-0">
                            <i className="bi bi-image" />
                        </div>
                        <div className="min-w-0 flex-grow-1">
                            <div className="fw-semibold">{t('users.profilePicture')}</div>
                            <div className="text-ava-muted text-tiny">{t('users.profilePictureHelp')}</div>
                        </div>
                        <div className="d-flex flex-wrap gap-2">
                            <button
                                type="button"
                                className="btn btn-outline-primary btn-sm text-nowrap"
                                disabled={avatarLinkBusy}
                                onClick={copyAvatarUploadLink}
                            >
                                <i className="bi bi-link-45deg me-1" />
                                {avatarLinkCopied ? t('actions.copied') : t('actions.copyLink')}
                            </button>
                            {viewedUser.phone && (
                                <button
                                    type="button"
                                    className="btn btn-success btn-sm text-nowrap"
                                    disabled={avatarLinkBusy}
                                    onClick={sendAvatarUploadLinkViaWhatsApp}
                                >
                                    <i className="bi bi-whatsapp me-1" />
                                    {t('actions.whatsapp')}
                                </button>
                            )}
                        </div>
                    </li>
                </ul>
            </div>

            <div className="row g-4">
                <div className="col-lg-6">
                    <div className="d-flex justify-content-between align-items-center mb-3">
                        <h5 className="mb-0">{t('users.loggedInApps')}</h5>
                        {tokens.length > 0 && (
                            <button type="button" className="btn btn-sm btn-outline-primary" onClick={forceLogoutAll}>
                                <i className="bi bi-box-arrow-right me-1" />
                                {t('users.forceLogoutAll')}
                            </button>
                        )}
                    </div>

                    {tokens.length === 0 ? (
                        <div className="ava-card">
                            <div className="text-center py-5">
                                <i className="bi bi-plug text-ava-muted icon-fs-48" />
                                <p className="text-ava-muted mt-3 mb-0">{t('users.notLoggedIntoApps')}</p>
                            </div>
                        </div>
                    ) : (
                        <div className="ava-card">
                            <ul className="list-group list-group-flush">
                                {tokens.map((token) => (
                                    <li
                                        key={token.id}
                                        className="list-group-item d-flex justify-content-between align-items-center p-3 gap-3"
                                    >
                                        <div className="min-w-0">
                                            <div className="fw-semibold">{token.client.name}</div>
                                            <div className="mb-1">
                                                {token.scopes.map((scope) => (
                                                    <span key={scope} className="scope-chip me-1">
                                                        {scope}
                                                    </span>
                                                ))}
                                            </div>
                                            <div className="text-ava-muted text-tiny">
                                                {t('users.signedInExpires', { created: token.created_at, expires: token.expires_at })}
                                            </div>
                                        </div>
                                        <button
                                            type="button"
                                            className="btn btn-outline-primary btn-sm text-nowrap"
                                            onClick={() => forceLogout(token)}
                                        >
                                            <i className="bi bi-box-arrow-right me-1" />
                                            {t('users.forceLogout')}
                                        </button>
                                    </li>
                                ))}
                            </ul>
                        </div>
                    )}
                </div>

                <div className="col-lg-6">
                    <h5 className="mb-3">{t('users.specificUserAppAccess')}</h5>

                    {specificClients.length === 0 ? (
                        <div className="ava-card">
                            <div className="text-center py-5">
                                <i className="bi bi-shield-lock text-ava-muted icon-fs-48" />
                                <p className="text-ava-muted mt-3 mb-0 px-3">
                                    {t('users.noSpecificClients')}
                                </p>
                            </div>
                        </div>
                    ) : (
                        <div className="ava-card">
                            <ul className="list-group list-group-flush">
                                {specificClients.map((client) => (
                                    <li
                                        key={client.id}
                                        className="list-group-item d-flex justify-content-between align-items-center p-3 gap-3"
                                    >
                                        <div className="min-w-0">
                                            <div className="fw-semibold">{client.app_name}</div>
                                            <StatusBadge status={client.has_access ? 'active' : 'disabled'} />
                                        </div>
                                        <button
                                            type="button"
                                            className={`btn btn-sm text-nowrap ${client.has_access ? 'btn-outline-primary' : 'btn-primary'}`}
                                            onClick={() => toggleClientAccess(client)}
                                        >
                                            {client.has_access ? t('users.revokeAccess') : t('users.grantAccess')}
                                        </button>
                                    </li>
                                ))}
                            </ul>
                        </div>
                    )}
                </div>

                <div className="col-lg-6">
                    <h5 className="mb-3">{t('users.info.groups')}</h5>

                    {groups.length === 0 ? (
                        <div className="ava-card">
                            <div className="text-center py-5">
                                <i className="bi bi-collection text-ava-muted icon-fs-48" />
                                <p className="text-ava-muted mt-3 mb-0 px-3">
                                    {t('search.noGroupsCreateFirst')}
                                </p>
                            </div>
                        </div>
                    ) : (
                        <div className="ava-card">
                            <ul className="list-group list-group-flush">
                                {groups.map((group) => (
                                    <li
                                        key={group.id}
                                        className="list-group-item d-flex justify-content-between align-items-center p-3 gap-3"
                                    >
                                        <div className="min-w-0">
                                            <div className="fw-semibold">{group.name}</div>
                                            <StatusBadge status={group.is_member ? 'active' : 'disabled'} />
                                        </div>
                                        <button
                                            type="button"
                                            className={`btn btn-sm text-nowrap ${group.is_member ? 'btn-outline-primary' : 'btn-primary'}`}
                                            onClick={() => toggleGroup(group)}
                                        >
                                            {group.is_member ? t('actions.remove') : t('actions.add')}
                                        </button>
                                    </li>
                                ))}
                            </ul>
                        </div>
                    )}
                </div>
            </div>

            <div className="row g-4 mt-0">
                <div className="col-12">
                    <div className="d-flex justify-content-between align-items-center mb-3">
                        <h5 className="mb-0">{t('users.info.family')}</h5>
                        {viewedUser.family && (
                            <a href={`/admin/families/${viewedUser.family.id}`} className="btn btn-sm btn-outline-primary">
                                <i className="bi bi-people me-1" />
                                {t('families.manageMembers')}
                            </a>
                        )}
                    </div>

                    {viewedUser.family && familyMembers.length > 0 ? (
                        <div className="ava-card">
                            <ul className="list-group list-group-flush">
                                {familyMembers.map((member) => (
                                    <li key={member.id} className="list-group-item d-flex justify-content-between align-items-center p-3 gap-3">
                                        <a href={`/admin/users/${member.id}`} className="fw-semibold">
                                            {member.name}
                                        </a>
                                        <span className="role-chip">{t(`familyShow.${member.role}`)}</span>
                                    </li>
                                ))}
                            </ul>
                        </div>
                    ) : viewedUser.family ? (
                        <div className="ava-card">
                            <div className="text-center py-5">
                                <p className="text-ava-muted mb-0 px-3">{t('users.info.noOtherFamilyMembers')}</p>
                            </div>
                        </div>
                    ) : (
                        <div className="ava-card">
                            <div className="text-center py-5">
                                <i className="bi bi-house-heart text-ava-muted icon-fs-48" />
                                <p className="text-ava-muted mt-3 mb-0 px-3">{t('users.info.noFamily')}</p>
                            </div>
                        </div>
                    )}
                </div>
            </div>
        </AdminLayout>
    )
}
