<?php

namespace App\Repositories;

use App\Models\SSOUser;

class SSOUserRepository
{
    public function __construct(protected SSOUser $model) {}

    public function getAll($filters = [])
    {
        $query = $this->model->query();

        if (isset($filters['user_id'])) {
            $query->where('user_id', $filters['user_id']);
        }

        if (isset($filters['created_by'])) {
            $query->where('created_by', 'like', '%'.$filters['created_by'].'%');
        }

        return $query->with(['user', 'createdBy'])->paginate(10);
    }

    public function findById($id)
    {
        return $this->model->with(['user', 'createdBy'])->findOrFail($id);
    }

    public function create($data)
    {
        return $this->model->create($data);
    }

    public function delete($id)
    {
        $ssoUser = $this->findById($id);

        return $ssoUser->delete();
    }
}
