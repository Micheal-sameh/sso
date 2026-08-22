<?php

namespace App\Repositories\Eloquent;

use App\Models\Fund;
use App\Repositories\Contracts\FundRepositoryContract;
use Illuminate\Support\Collection;

class FundRepository implements FundRepositoryContract
{
    public function all(): Collection
    {
        return Fund::withCount('donations')->orderBy('name')->get();
    }

    public function active(): Collection
    {
        return Fund::where('is_active', true)->orderBy('name')->get();
    }

    public function create(array $data): Fund
    {
        return Fund::create($data);
    }

    public function update(Fund $fund, array $data): Fund
    {
        $fund->update($data);

        return $fund->fresh();
    }

    public function delete(Fund $fund): bool
    {
        return (bool) $fund->delete();
    }
}
