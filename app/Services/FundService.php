<?php

namespace App\Services;

use App\Exceptions\Donations\FundInUseException;
use App\Models\Fund;
use App\Repositories\Contracts\FundRepositoryContract;
use Illuminate\Database\QueryException;
use Illuminate\Support\Collection;

class FundService
{
    public function __construct(
        protected FundRepositoryContract $fundRepository,
    ) {}

    public function listAll(): Collection
    {
        return $this->fundRepository->all();
    }

    public function listActive(): Collection
    {
        return $this->fundRepository->active();
    }

    public function create(array $data): Fund
    {
        return $this->fundRepository->create($data);
    }

    public function update(Fund $fund, array $data): Fund
    {
        return $this->fundRepository->update($fund, $data);
    }

    /**
     * @throws FundInUseException
     */
    public function delete(Fund $fund): void
    {
        try {
            $this->fundRepository->delete($fund);
        } catch (QueryException $e) {
            throw new FundInUseException;
        }
    }
}
