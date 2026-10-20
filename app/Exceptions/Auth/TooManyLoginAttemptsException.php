<?php

namespace App\Exceptions\Auth;

class TooManyLoginAttemptsException extends AuthenticationException
{
    protected int $statusCode = 429;

    public function __construct(public readonly int $retryAfter)
    {
        parent::__construct(__('messages.login_locked', ['seconds' => $retryAfter]));
    }
}
