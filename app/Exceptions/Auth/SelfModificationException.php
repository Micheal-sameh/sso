<?php

namespace App\Exceptions\Auth;

class SelfModificationException extends \RuntimeException
{
    protected int $statusCode = 403;

    public function __construct(string $message = 'You cannot change your own admin access.')
    {
        parent::__construct($message);
    }

    public function getStatusCode(): int
    {
        return $this->statusCode;
    }
}
