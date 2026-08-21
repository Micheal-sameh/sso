<?php

namespace App\Services\Payments\DTOs;

readonly class GatewayInitiationResult
{
    public function __construct(
        public string $redirectUrl,
        public string $reference,
    ) {}
}
