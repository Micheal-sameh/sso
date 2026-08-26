<?php

namespace App\Exceptions\Family;

class DuplicateFamilyRoleException extends \RuntimeException
{
    protected int $statusCode = 409;

    public function __construct(?string $message = null)
    {
        parent::__construct($message ?? __('messages.family_role_taken'));
    }

    public function getStatusCode(): int
    {
        return $this->statusCode;
    }
}
