<?php

namespace App\DTOs;

use App\Enums\AdminPermission;
use App\Enums\AdminRole;
use App\Enums\UserRole;
use App\Models\User;

readonly class UserDTO
{
    public function __construct(
        public int $id,
        public string $uuid,
        public string $membershipCode,
        public string $name,
        public string $email,
        public ?string $phone,
        public ?string $avatar,
        public UserRole $role,
        public bool $is_active,
        public bool $isAdmin,
        public bool $canManageAdminAccess,
        public int $stageId,
        public string $stageName,
        public string $stageCode,
        public ?string $email_verified_at,
    ) {}

    public static function fromModel(User $user): self
    {
        $user->loadMissing('roles', 'stage');

        return new self(
            id: $user->id,
            uuid: $user->uuid,
            membershipCode: $user->membership_code,
            name: $user->name,
            email: $user->email,
            phone: $user->phone,
            avatar: $user->avatar_url,
            role: $user->role,
            is_active: $user->is_active,
            isAdmin: $user->hasAnyRole([AdminRole::SuperAdmin->value, AdminRole::Admin->value]),
            canManageAdminAccess: $user->can(AdminPermission::ManageAdminAccess->value),
            stageId: $user->stage_id,
            stageName: $user->stage->name,
            stageCode: $user->stage->code,
            email_verified_at: $user->email_verified_at?->toIso8601String(),
        );
    }

    /**
     * Shape used for Inertia shared props / account views.
     */
    public function toArray(): array
    {
        return [
            'id' => $this->id,
            'uuid' => $this->uuid,
            'membership_code' => $this->membershipCode,
            'name' => $this->name,
            'email' => $this->email,
            'phone' => $this->phone,
            'avatar' => $this->avatar,
            'role' => $this->role->value,
            'is_active' => $this->is_active,
            'is_admin' => $this->isAdmin,
            'can_manage_admin_access' => $this->canManageAdminAccess,
            'stage' => [
                'id' => $this->stageId,
                'name' => $this->stageName,
                'code' => $this->stageCode,
            ],
            'email_verified_at' => $this->email_verified_at,
        ];
    }

    /**
     * Shape used by the /api/userinfo OIDC endpoint.
     */
    public function toOidcArray(): array
    {
        return [
            'sub' => $this->uuid,
            'name' => $this->name,
            'email' => $this->email,
            'email_verified' => $this->email_verified_at !== null,
            'picture' => $this->avatar,
            'updated_at' => $this->email_verified_at,
        ];
    }
}
