<?php

namespace App\Exceptions\Auth;

class InvalidMfaCodeException extends \RuntimeException
{
    protected int $statusCode = 422;

    public function __construct(?string $message = null)
    {
        parent::__construct($message ?? __('messages.invalid_mfa_code'));
    }

    public function getStatusCode(): int
    {
        return $this->statusCode;
    }
}
