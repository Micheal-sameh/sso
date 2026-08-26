import { useState } from 'react'
import { router } from '@inertiajs/react'
import { useTranslation } from 'react-i18next'
import AdminLayout from '@/components/layouts/AdminLayout'
import { useConfirm } from '@/components/shared/ConfirmDialog'
import type { Family, FamilyMemberOption } from '@/types'

interface ShowProps {
    family: Family
    users: FamilyMemberOption[]
}

/**
 * Membership codes follow "{familyBase}NR{n}" (e.g. "e1c1f1nr1"), where every
 * member of the same family shares the same base and n identifies the role:
 * 1 = father, 2 = mother, 3+ = children.
 */
function parseFamilyCode(membershipCode: string): { base: string; role: 'father' | 'mother' | 'child' } | null {
    const match = membershipCode.toLowerCase().match(/^(.+f\d+)nr(\d+)$/)

    if (!match) {
        return null
    }

    const n = Number(match[2])

    return { base: match[1], role: n === 1 ? 'father' : n === 2 ? 'mother' : 'child' }
}

export default function Show({ family, users }: ShowProps) {
    const { t } = useTranslation(['admin', 'common'])
    const confirm = useConfirm()
    const [pickerFor, setPickerFor] = useState<'father' | 'mother' | 'child' | null>(null)
    const [search, setSearch] = useState('')

    const memberCount = (family.father ? 1 : 0) + (family.mother ? 1 : 0) + (family.children?.length ?? 0)

    const requireDataUpdate = async () => {
        if (await confirm(t('familyShow.confirmRequireDataUpdate'), { confirmLabel: t('familyShow.requireDataUpdate') })) {
            router.post(`/admin/families/${family.id}/require-data-update`, {}, { preserveScroll: true })
        }
    }

    const familyBase = [family.father, family.mother, ...(family.children ?? [])]
        .map((member) => member && parseFamilyCode(member.membership_code)?.base)
        .find((base): base is string => base !== undefined && base !== null)

    const assign = (userId: number, role: 'father' | 'mother' | 'child') => {
        router.post(
            `/admin/families/${family.id}/members`,
            { user_id: userId, role },
            {
                preserveScroll: true,
                preserveState: true,
                only: ['family', 'users', 'flash'],
                onSuccess: () => setPickerFor(null),
            },
        )
    }

    const remove = (userId: number) => {
        router.delete(`/admin/families/${family.id}/members/${userId}`, {
            preserveScroll: true,
            preserveState: true,
            only: ['family', 'users', 'flash'],
        })
    }

    const filteredUsers = users
        .filter((u) => u.current_family_name === null)
        .filter((u) => familyBase === undefined || parseFamilyCode(u.membership_code)?.base === familyBase)
        .filter(
            (u) =>
                u.name.toLowerCase().includes(search.toLowerCase()) ||
                (u.email ?? '').toLowerCase().includes(search.toLowerCase()) ||
                u.membership_code.toLowerCase().includes(search.toLowerCase()),
        )

    const openPicker = (role: 'father' | 'mother' | 'child') => {
        setSearch('')
        setPickerFor(pickerFor === role ? null : role)
    }

    const renderPicker = (role: 'father' | 'mother' | 'child') => (
        <div className="mt-2">
            {familyBase !== undefined && (
                <p className="text-ava-muted text-tiny mb-2">{t('familyShow.restrictedToCode', { base: familyBase })}</p>
            )}
            <input
                type="text"
                className="form-control mb-2"
                placeholder={t('familyShow.searchPlaceholder')}
                value={search}
                onChange={(e) => setSearch(e.target.value)}
            />
            <div className="permission-list scroll-picker">
                <div className="list-group">
                    {filteredUsers.map((u) => (
                        <button
                            key={u.id}
                            type="button"
                            className="list-group-item list-group-item-action d-flex justify-content-between align-items-center"
                            onClick={() => assign(u.id, role)}
                        >
                            {u.name}
                            <span className="text-ava-muted text-tiny ms-1">{u.email}</span>
                        </button>
                    ))}
                </div>
                {filteredUsers.length === 0 && (
                    <p className="text-ava-muted text-tiny mb-0 p-2">{t('search.noUsersMatch')}</p>
                )}
            </div>
        </div>
    )

    return (
        <AdminLayout
            title={t('families.manageMembers')}
            action={
                <button type="button" className="btn btn-outline-primary btn-sm" onClick={() => router.visit('/admin/families')}>
                    <i className="bi bi-arrow-left me-1" />
                    {t('familyShow.backToFamilies')}
                </button>
            }
        >
            <div className="ava-card mb-4">
                <div className="card-accent card-accent-primary" />
                <div className="p-4 d-flex flex-wrap justify-content-between align-items-center gap-2">
                    <div>
                        <h3 className="mb-1">{family.display_name}</h3>
                        <span className="role-chip">{t('familyShow.memberCount', { count: memberCount })}</span>
                    </div>
                    {memberCount > 0 && (
                        <button type="button" className="btn btn-outline-danger btn-sm" onClick={requireDataUpdate}>
                            <i className="bi bi-exclamation-triangle me-1" />
                            {t('familyShow.requireDataUpdate')}
                        </button>
                    )}
                </div>
            </div>

            <div className="row g-3">
                <div className="col-md-6">
                    <div className="ava-card p-3 h-100">
                        <div className="d-flex justify-content-between align-items-center mb-2">
                            <h5 className="mb-0">{t('familyShow.father')}</h5>
                            <button type="button" className="btn btn-sm btn-outline-primary" onClick={() => openPicker('father')}>
                                {t('familyShow.change')}
                            </button>
                        </div>
                        {family.father ? (
                            <div className="d-flex justify-content-between align-items-center">
                                <a href={`/admin/users/${family.father.id}`} className="fw-semibold">
                                    {family.father.name}
                                </a>
                                <button type="button" className="btn btn-sm btn-link text-danger" onClick={() => remove(family.father!.id)}>
                                    {t('actions.remove')}
                                </button>
                            </div>
                        ) : (
                            <p className="text-ava-muted mb-0">{t('familyShow.notAssigned')}</p>
                        )}
                        {pickerFor === 'father' && renderPicker('father')}
                    </div>
                </div>

                <div className="col-md-6">
                    <div className="ava-card p-3 h-100">
                        <div className="d-flex justify-content-between align-items-center mb-2">
                            <h5 className="mb-0">{t('familyShow.mother')}</h5>
                            <button type="button" className="btn btn-sm btn-outline-primary" onClick={() => openPicker('mother')}>
                                {t('familyShow.change')}
                            </button>
                        </div>
                        {family.mother ? (
                            <div className="d-flex justify-content-between align-items-center">
                                <a href={`/admin/users/${family.mother.id}`} className="fw-semibold">
                                    {family.mother.name}
                                </a>
                                <button type="button" className="btn btn-sm btn-link text-danger" onClick={() => remove(family.mother!.id)}>
                                    {t('actions.remove')}
                                </button>
                            </div>
                        ) : (
                            <p className="text-ava-muted mb-0">{t('familyShow.notAssigned')}</p>
                        )}
                        {pickerFor === 'mother' && renderPicker('mother')}
                    </div>
                </div>

                <div className="col-12">
                    <div className="ava-card p-3">
                        <div className="d-flex justify-content-between align-items-center mb-2">
                            <h5 className="mb-0">{t('familyShow.children')}</h5>
                            <button type="button" className="btn btn-sm btn-outline-primary" onClick={() => openPicker('child')}>
                                {t('actions.add')}
                            </button>
                        </div>
                        {family.children && family.children.length > 0 ? (
                            <ul className="list-group list-group-flush">
                                {family.children.map((child) => (
                                    <li key={child.id} className="list-group-item d-flex justify-content-between align-items-center px-0">
                                        <a href={`/admin/users/${child.id}`}>{child.name}</a>
                                        <button type="button" className="btn btn-sm btn-link text-danger" onClick={() => remove(child.id)}>
                                            {t('actions.remove')}
                                        </button>
                                    </li>
                                ))}
                            </ul>
                        ) : (
                            <p className="text-ava-muted mb-0">{t('familyShow.noChildren')}</p>
                        )}
                        {pickerFor === 'child' && renderPicker('child')}
                    </div>
                </div>
            </div>
        </AdminLayout>
    )
}
