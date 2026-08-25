<?php

namespace App\Repositories\Eloquent;

use App\Enums\DonationStatus;
use App\Models\Donation;
use App\Models\DonationPlan;
use App\Models\DonationTransaction;
use App\Models\User;
use App\Repositories\Contracts\DonationRepositoryContract;
use Illuminate\Contracts\Pagination\LengthAwarePaginator;
use Illuminate\Database\Eloquent\Builder;
use Illuminate\Support\Collection;

class DonationRepository implements DonationRepositoryContract
{
    public function create(array $data): Donation
    {
        return Donation::create($data);
    }

    public function update(Donation $donation, array $data): Donation
    {
        $donation->update($data);

        return $donation->fresh();
    }

    public function findByReference(string $reference): ?Donation
    {
        return Donation::where('gateway_reference', $reference)->first();
    }

    public function paginateAll(array $filters, int $perPage = 20): LengthAwarePaginator
    {
        return $this->applyFilters(Donation::with(['fund', 'user']), $filters)
            ->latest()
            ->paginate($perPage)
            ->withQueryString();
    }

    public function all(array $filters): Collection
    {
        return $this->applyFilters(Donation::with(['fund', 'user']), $filters)->latest()->get();
    }

    public function forUser(User $user, int $perPage = 20): LengthAwarePaginator
    {
        return Donation::with(['fund', 'user'])
            ->where('user_id', $user->id)
            ->latest()
            ->paginate($perPage)
            ->withQueryString();
    }

    public function totalPaid(array $filters = []): float
    {
        return (float) $this->applyFilters(Donation::query(), $filters)
            ->where('status', DonationStatus::Paid->value)
            ->sum('amount');
    }

    public function hasPaidDonationBetween(int $userId, \DateTimeInterface $from, \DateTimeInterface $to): bool
    {
        return Donation::where('user_id', $userId)
            ->where('status', DonationStatus::Paid->value)
            ->whereBetween('paid_at', [$from, $to])
            ->exists();
    }

    public function userIdsWithPaidDonationBetween(array $userIds, \DateTimeInterface $from, \DateTimeInterface $to): Collection
    {
        return Donation::whereIn('user_id', $userIds)
            ->where('status', DonationStatus::Paid->value)
            ->whereBetween('paid_at', [$from, $to])
            ->pluck('user_id')
            ->unique();
    }

    public function createPlan(array $data): DonationPlan
    {
        return DonationPlan::create($data);
    }

    public function updatePlan(DonationPlan $plan, array $data): DonationPlan
    {
        $plan->update($data);

        return $plan->fresh();
    }

    public function duePlans(): Collection
    {
        return DonationPlan::with(['fund', 'user'])
            ->where('status', 'active')
            ->whereNotNull('gateway_token')
            ->where('next_charge_at', '<=', now())
            ->get();
    }

    public function logTransaction(array $data): void
    {
        DonationTransaction::create($data);
    }

    /**
     * @param  array{status?: string, fund_id?: int, gateway?: string, from?: string, to?: string}  $filters
     */
    private function applyFilters(Builder $query, array $filters): Builder
    {
        return $query
            ->when($filters['status'] ?? null, fn (Builder $q, string $status) => $q->where('status', $status))
            ->when($filters['fund_id'] ?? null, fn (Builder $q, int $fundId) => $q->where('fund_id', $fundId))
            ->when($filters['gateway'] ?? null, fn (Builder $q, string $gateway) => $q->where('gateway', $gateway))
            ->when($filters['from'] ?? null, fn (Builder $q, string $from) => $q->whereDate('created_at', '>=', $from))
            ->when($filters['to'] ?? null, fn (Builder $q, string $to) => $q->whereDate('created_at', '<=', $to));
    }
}
