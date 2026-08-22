<?php

namespace App\Exceptions\Auth;

class UnauthorizedRoleChangeException extends \RuntimeException
{
    protected int $statusCode = 403;

    public function __construct(?string $message = null)
    {
        parent::__construct($message ?? __('messages.unauthorized_role_change'));
    }

    public function getStatusCode(): int
    {
        return $this->statusCode;
    }
}
