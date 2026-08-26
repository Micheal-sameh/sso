<?php

namespace App\Repositories\Eloquent;

use App\Enums\FamilyRole;
use App\Exceptions\Family\DuplicateFamilyRoleException;
use App\Models\Family;
use App\Models\User;
use App\Repositories\Contracts\FamilyRepositoryContract;
use Illuminate\Support\Collection;

class FamilyRepository implements FamilyRepositoryContract
{
    /**
     * List all families, newest first, with member counts and roles loaded.
     *
     * @return Collection<int, Family>
     */
    public function all(): Collection
    {
        return Family::withCount('users')
            ->with(['father', 'mother', 'children'])
            ->latest()
            ->get();
    }

    public function findById(int $id): Family
    {
        return Family::with(['father', 'mother', 'children'])->findOrFail($id);
    }

    public function create(): Family
    {
        return Family::create();
    }

    public function delete(Family $family): bool
    {
        return (bool) $family->delete();
    }

    public function assignMember(Family $family, User $user, FamilyRole $role): void
    {
        if (in_array($role, [FamilyRole::Father, FamilyRole::Mother], true)) {
            $holder = $family->users()->where('family_role', $role)->where('id', '!=', $user->id)->first();

            if ($holder !== null) {
                throw new DuplicateFamilyRoleException;
            }
        }

        $user->forceFill([
            'family_id' => $family->id,
            'family_role' => $role,
        ])->save();
    }

    public function removeMember(User $user): void
    {
        $user->forceFill([
            'family_id' => null,
            'family_role' => null,
        ])->save();
    }

    public function requireDataUpdateForAllFamilies(): int
    {
        return User::whereNotNull('family_id')->update(['requires_data_update' => true]);
    }

    public function requireDataUpdateForFamily(Family $family): int
    {
        return $family->users()->update(['requires_data_update' => true]);
    }
}
