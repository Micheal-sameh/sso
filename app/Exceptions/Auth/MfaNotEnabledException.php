<?php

namespace App\Exceptions\Auth;

class MfaNotEnabledException extends \RuntimeException
{
    protected int $statusCode = 400;

    public function __construct(?string $message = null)
    {
        parent::__construct($message ?? __('messages.mfa_not_enabled'));
    }

    public function getStatusCode(): int
    {
        return $this->statusCode;
    }
}
