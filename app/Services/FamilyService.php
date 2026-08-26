<?php

namespace App\Services;

use App\Enums\FamilyRole;
use App\Models\Family;
use App\Models\User;
use App\Repositories\Contracts\FamilyRepositoryContract;
use Illuminate\Support\Collection;

class FamilyService
{
    public function __construct(
        protected FamilyRepositoryContract $familyRepository,
    ) {}

    /**
     * List all families with their members, for the admin index page.
     */
    public function listAll(): Collection
    {
        return $this->familyRepository->all();
    }

    public function find(int $id): Family
    {
        return $this->familyRepository->findById($id);
    }

    /**
     * Create a new, empty family. Members are assigned afterwards.
     */
    public function create(): Family
    {
        return $this->familyRepository->create();
    }

    /**
     * Delete a family. Members are simply unlinked (family_id set to null),
     * not deleted themselves.
     */
    public function delete(Family $family): void
    {
        $this->familyRepository->delete($family);
    }

    /**
     * Assign a user to a family with the given role.
     *
     * @throws \App\Exceptions\Family\DuplicateFamilyRoleException
     */
    public function addMember(Family $family, User $user, FamilyRole $role): void
    {
        $this->familyRepository->assignMember($family, $user, $role);
    }

    /**
     * Remove a user from a family.
     */
    public function removeMember(User $user): void
    {
        $this->familyRepository->removeMember($user);
    }

    /**
     * Flag every user who belongs to any family as requiring a data update.
     *
     * @return int number of users updated
     */
    public function requireDataUpdateForAllFamilies(): int
    {
        return $this->familyRepository->requireDataUpdateForAllFamilies();
    }

    /**
     * Flag every member of the given family as requiring a data update.
     *
     * @return int number of users updated
     */
    public function requireDataUpdateForFamily(Family $family): int
    {
        return $this->familyRepository->requireDataUpdateForFamily($family);
    }
}
