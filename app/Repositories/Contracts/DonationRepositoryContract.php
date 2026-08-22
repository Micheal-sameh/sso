<?php

namespace App\Repositories\Contracts;

use App\Models\Donation;
use App\Models\DonationPlan;
use App\Models\User;
use Illuminate\Contracts\Pagination\LengthAwarePaginator;
use Illuminate\Support\Collection;

interface DonationRepositoryContract
{
    public function create(array $data): Donation;

    public function update(Donation $donation, array $data): Donation;

    public function findByReference(string $reference): ?Donation;

    /**
     * @param  array{status?: string, fund_id?: int, gateway?: string, from?: string, to?: string}  $filters
     */
    public function paginateAll(array $filters, int $perPage = 20): LengthAwarePaginator;

    /**
     * @param  array{status?: string, fund_id?: int, gateway?: string, from?: string, to?: string}  $filters
     */
    public function all(array $filters): Collection;

    public function forUser(User $user, int $perPage = 20): LengthAwarePaginator;

    public function totalPaid(array $filters = []): float;

    public function createPlan(array $data): DonationPlan;

    public function updatePlan(DonationPlan $plan, array $data): DonationPlan;

    public function duePlans(): Collection;

    public function logTransaction(array $data): void;
}
