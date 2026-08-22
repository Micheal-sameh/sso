<?php

namespace App\Exceptions\Auth;

class UserNotFoundException extends \RuntimeException
{
    protected int $statusCode = 404;

    public function __construct(?string $message = null)
    {
        parent::__construct($message ?? __('messages.user_not_found'));
    }

    public function getStatusCode(): int
    {
        return $this->statusCode;
    }
}
