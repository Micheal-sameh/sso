<?php

namespace App\Repositories\Contracts;

use App\Enums\FamilyRole;
use App\Models\Family;
use App\Models\User;
use Illuminate\Support\Collection;

interface FamilyRepositoryContract
{
    /**
     * @return Collection<int, Family>
     */
    public function all(): Collection;

    public function findById(int $id): Family;

    /**
     * Create a new, empty family. Members are assigned afterwards.
     */
    public function create(): Family;

    public function delete(Family $family): bool;

    /**
     * Assign a user to a family with the given role, moving them out of any
     * family they previously belonged to.
     *
     * @throws \App\Exceptions\Family\DuplicateFamilyRoleException if the
     *                                                              role is Father or Mother and another user already holds it in this family.
     */
    public function assignMember(Family $family, User $user, FamilyRole $role): void;

    /**
     * Remove a user from their family.
     */
    public function removeMember(User $user): void;

    /**
     * Flag every user who belongs to any family as requiring a data update.
     *
     * @return int number of users updated
     */
    public function requireDataUpdateForAllFamilies(): int;

    /**
     * Flag every member of the given family as requiring a data update.
     *
     * @return int number of users updated
     */
    public function requireDataUpdateForFamily(Family $family): int;
}
