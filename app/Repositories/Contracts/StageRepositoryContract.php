<?php

namespace App\Repositories\Contracts;

use App\Models\Stage;
use Illuminate\Support\Collection;

interface StageRepositoryContract
{
    public function all(): Collection;

    public function create(array $data): Stage;

    public function update(Stage $stage, array $data): Stage;

    public function delete(Stage $stage): bool;
}
