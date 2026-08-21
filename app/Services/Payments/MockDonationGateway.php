<?php

namespace App\Services\Payments;

use App\Models\Donation;
use App\Models\DonationPlan;
use App\Services\Payments\Contracts\DonationGatewayContract;
use App\Services\Payments\DTOs\GatewayCallbackResult;
use App\Services\Payments\DTOs\GatewayInitiationResult;
use Illuminate\Http\Request;
use Illuminate\Support\Str;

/**
 * Sandbox stand-in for a bank gateway, used while NBE/CIB merchant
 * onboarding is pending (see DonationGatewayFactory). Sends the donor to an
 * in-app "fake bank" checkout page instead of a real hosted payment page, so
 * the full donate -> pay -> callback -> paid flow can be built and tested
 * end-to-end today, then swapped for the real gateway later with no other
 * code changes.
 */
class MockDonationGateway implements DonationGatewayContract
{
    public function initiate(Donation $donation): GatewayInitiationResult
    {
        $reference = 'MOCK-'.Str::upper(Str::random(16));

        return new GatewayInitiationResult(
            redirectUrl: route('donations.sandbox', $donation->uuid),
            reference: $reference,
        );
    }

    public function verifySignature(Request $request): bool
    {
        return true;
    }

    public function parseCallback(Request $request): GatewayCallbackResult
    {
        return new GatewayCallbackResult(
            reference: $request->string('reference')->toString(),
            success: $request->boolean('success', true),
            raw: $request->all(),
        );
    }

    public function chargeToken(DonationPlan $plan): GatewayCallbackResult
    {
        return new GatewayCallbackResult(
            reference: 'MOCK-'.Str::upper(Str::random(16)),
            success: true,
            raw: ['plan_id' => $plan->id, 'simulated' => true],
        );
    }
}
