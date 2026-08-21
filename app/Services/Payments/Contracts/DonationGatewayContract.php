<?php

namespace App\Services\Payments\Contracts;

use App\Models\Donation;
use App\Models\DonationPlan;
use App\Services\Payments\DTOs\GatewayCallbackResult;
use App\Services\Payments\DTOs\GatewayInitiationResult;
use Illuminate\Http\Request;

/**
 * A single donation payment provider (a specific bank's merchant API, or a
 * general aggregator). Every gateway is a redirect-based flow: we start a
 * payment session, send the donor to the provider's hosted page, and the
 * provider reports the outcome back via a server-to-server callback.
 */
interface DonationGatewayContract
{
    /**
     * Start a payment session for a pending donation and return the URL the
     * donor should be redirected to in order to complete payment.
     */
    public function initiate(Donation $donation): GatewayInitiationResult;

    /**
     * Verify that an inbound callback request genuinely came from the
     * provider (signature/HMAC check) before trusting its payload.
     */
    public function verifySignature(Request $request): bool;

    /**
     * Parse a verified callback payload into a normalized result.
     */
    public function parseCallback(Request $request): GatewayCallbackResult;

    /**
     * Charge a recurring donation plan's saved token for its next
     * installment. Not every bank merchant API supports tokenized recurring
     * charges — confirm this against the provider's integration guide
     * before relying on it.
     */
    public function chargeToken(DonationPlan $plan): GatewayCallbackResult;
}
