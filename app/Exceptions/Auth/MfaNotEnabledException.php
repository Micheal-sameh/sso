<?php

namespace App\Exceptions\Auth;

class MfaNotEnabledException extends \RuntimeException
{
    protected int $statusCode = 400;

    public function __construct(string $message = 'Two-factor authentication is not enabled for this account.')
    {
        parent::__construct($message);
    }

    public function getStatusCode(): int
    {
        return $this->statusCode;
    }
}
