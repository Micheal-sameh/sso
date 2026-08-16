<?php

namespace App\Exceptions\Auth;

class PasswordResetException extends \RuntimeException
{
    protected int $statusCode = 422;

    public function getStatusCode(): int
    {
        return $this->statusCode;
    }
}
