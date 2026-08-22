<?php

namespace App\Http\Controllers\Api;

use App\Enums\DonationGateway;
use App\Http\Controllers\BaseController;
use App\Services\DonationService;
use Illuminate\Http\JsonResponse;
use Illuminate\Http\Request;

class DonationCallbackController extends BaseController
{
    public function __construct(
        protected DonationService $donationService,
    ) {}

    /**
     * Server-to-server payment notification from a bank gateway. Not
     * session-authenticated — trust is established purely by
     * DonationGatewayContract::verifySignature() inside the service.
     */
    public function handle(Request $request, DonationGateway $gateway): JsonResponse
    {
        $this->donationService->handleCallback($gateway, $request);

        return response()->json(['status' => 'ok']);
    }
}
