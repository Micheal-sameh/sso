<?php

namespace App\Exceptions\Auth;

class SelfModificationException extends \RuntimeException
{
    protected int $statusCode = 403;

    public function __construct(?string $message = null)
    {
        parent::__construct($message ?? __('messages.cannot_change_own_admin_access'));
    }

    public function getStatusCode(): int
    {
        return $this->statusCode;
    }
}
