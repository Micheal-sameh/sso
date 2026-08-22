<?php

namespace App\Services\Payments\DTOs;

readonly class GatewayCallbackResult
{
    /**
     * @param  array<string, mixed>  $raw
     */
    public function __construct(
        public string $reference,
        public bool $success,
        public array $raw,
    ) {}
}
