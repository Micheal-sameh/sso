<?php

namespace App\Repositories\Contracts;

use App\Models\Group;
use Illuminate\Support\Collection;

interface GroupRepositoryContract
{
    public function all(): Collection;

    public function create(array $data): Group;

    public function update(Group $group, array $data): Group;

    public function delete(Group $group): bool;
}
