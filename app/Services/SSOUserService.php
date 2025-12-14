<?php

namespace App\Services;

use App\Repositories\SSOUserRepository;

class SSOUserService
{
    public function __construct(protected SSOUserRepository $ssoUserRepository) {}

    public function getAllSSOUsers($filters = [])
    {
        return $this->ssoUserRepository->getAll($filters);
    }

    public function getById($id)
    {
        return $this->ssoUserRepository->findById($id);
    }

    public function create($data)
    {
        return $this->ssoUserRepository->create($data);
    }

    public function delete($id)
    {
        return $this->ssoUserRepository->delete($id);
    }
}
