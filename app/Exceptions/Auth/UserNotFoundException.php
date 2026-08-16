<?php

namespace App\Exceptions\Auth;

class UserNotFoundException extends \RuntimeException
{
    protected int $statusCode = 404;

    public function __construct(string $message = 'No matching user found.')
    {
        parent::__construct($message);
    }

    public function getStatusCode(): int
    {
        return $this->statusCode;
    }
}
