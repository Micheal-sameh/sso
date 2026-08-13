<?php

namespace App\Exceptions\Auth;

class InvalidMfaCodeException extends \RuntimeException
{
    protected int $statusCode = 422;

    public function __construct(string $message = 'The verification code is invalid.')
    {
        parent::__construct($message);
    }

    public function getStatusCode(): int
    {
        return $this->statusCode;
    }
}
