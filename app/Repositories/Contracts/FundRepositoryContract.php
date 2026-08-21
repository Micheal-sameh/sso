<?php

namespace App\Repositories\Contracts;

use App\Models\Fund;
use Illuminate\Support\Collection;

interface FundRepositoryContract
{
    public function all(): Collection;

    public function active(): Collection;

    public function create(array $data): Fund;

    public function update(Fund $fund, array $data): Fund;

    public function delete(Fund $fund): bool;
}
