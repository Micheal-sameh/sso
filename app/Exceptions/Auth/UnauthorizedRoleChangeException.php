<?php

namespace App\Exceptions\Auth;

class UnauthorizedRoleChangeException extends \RuntimeException
{
    protected int $statusCode = 403;

    public function __construct(string $message = 'Only a super admin can assign or change the Admin and Super Admin roles.')
    {
        parent::__construct($message);
    }

    public function getStatusCode(): int
    {
        return $this->statusCode;
    }
}
