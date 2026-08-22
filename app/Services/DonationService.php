<?php

namespace App\Services;

use App\DTOs\DonationDTO;
use App\DTOs\NotificationDTO;
use App\Enums\DonationGateway as DonationGatewayEnum;
use App\Enums\DonationPlanStatus;
use App\Enums\DonationStatus;
use App\Enums\NotificationTargetType;
use App\Models\Donation;
use App\Models\User;
use App\Repositories\Contracts\DonationRepositoryContract;
use App\Repositories\Contracts\UserRepositoryContract;
use App\Services\Payments\DonationGatewayFactory;
use App\Services\Payments\DTOs\GatewayCallbackResult;
use Illuminate\Contracts\Pagination\LengthAwarePaginator;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\DB;
use Illuminate\Support\Str;

class DonationService
{
    public function __construct(
        protected DonationRepositoryContract $donationRepository,
        protected DonationGatewayFactory $gatewayFactory,
        protected UserRepositoryContract $userRepository,
        protected NotificationService $notificationService,
    ) {}

    /**
     * Opt the user into monthly donation reminders, anchored to today's day of month.
     */
    public function enableMonthlyReminder(User $user): void
    {
        $this->userRepository->update($user, [
            'wants_donation_reminders' => true,
            'donation_reminder_day' => now()->day,
        ]);
    }

    public function disableMonthlyReminder(User $user): void
    {
        $this->userRepository->update($user, [
            'wants_donation_reminders' => false,
            'donation_reminder_day' => null,
        ]);
    }

    public function hasDonatedThisMonth(User $user): bool
    {
        return $this->donationRepository->hasPaidDonationBetween(
            $user->id,
            now()->startOfMonth(),
            now()->endOfMonth(),
        );
    }

    /**
     * Notify every user whose reminder day is today, unless they've already
     * donated this month. Intended to run daily via the scheduler.
     */
    public function sendMonthlyDonationReminders(): int
    {
        $sent = 0;

        foreach ($this->userRepository->wantingMonthlyDonationReminderToday() as $user) {
            if ($this->hasDonatedThisMonth($user)) {
                continue;
            }

            $this->sendDonationReminder(
                $user,
                "Hi {$user->name}, it's your monthly donation reminder — thank you for your generosity!"
            );

            $sent++;
        }

        return $sent;
    }

    /**
     * Follow-up nudge for opted-in users who still haven't donated this month,
     * once their reminder day has already passed. Intended to run every Friday.
     */
    public function sendFridayDonationReminders(): int
    {
        $today = now();
        $sent = 0;

        foreach ($this->userRepository->wantingDonationReminders() as $user) {
            $cycleStartDay = min($user->donation_reminder_day, $today->daysInMonth);

            if ($today->day < $cycleStartDay || $this->hasDonatedThisMonth($user)) {
                continue;
            }

            $this->sendDonationReminder(
                $user,
                "Hi {$user->name}, just a friendly reminder — you haven't made your donation for this month yet."
            );

            $sent++;
        }

        return $sent;
    }

    private function sendDonationReminder(User $user, string $message): void
    {
        $this->notificationService->send(
            new NotificationDTO(
                message: $message,
                targetType: NotificationTargetType::Specific,
                stageIds: [],
                groupIds: [],
                userIds: [$user->id],
                image: null,
            ),
            null,
        );
    }

    /**
     * Create a pending donation (and, for recurring gifts, its plan), then
     * start a payment session with the chosen gateway.
     *
     * @return array{donation: Donation, redirect_url: string}
     */
    public function createDonation(DonationDTO $dto): array
    {
        return DB::transaction(function () use ($dto) {
            $plan = null;

            if ($dto->isRecurring) {
                $plan = $this->donationRepository->createPlan([
                    'uuid' => (string) Str::uuid(),
                    'fund_id' => $dto->fundId,
                    'user_id' => $dto->userId,
                    'amount' => $dto->amount,
                    'currency' => 'EGP',
                    'gateway' => $dto->gateway->value,
                    'frequency' => 'monthly',
                    'status' => DonationPlanStatus::Active->value,
                ]);
            }

            $donation = $this->donationRepository->create([
                'uuid' => (string) Str::uuid(),
                'fund_id' => $dto->fundId,
                'user_id' => $dto->userId,
                'donation_plan_id' => $plan?->id,
                'amount' => $dto->amount,
                'currency' => 'EGP',
                'gateway' => $dto->gateway->value,
                'status' => DonationStatus::Pending->value,
            ]);

            $gateway = $this->gatewayFactory->make($dto->gateway);
            $initiation = $gateway->initiate($donation);

            $donation = $this->donationRepository->update($donation, [
                'gateway_reference' => $initiation->reference,
            ]);

            $this->donationRepository->logTransaction([
                'donation_id' => $donation->id,
                'donation_plan_id' => $plan?->id,
                'gateway' => $dto->gateway->value,
                'type' => 'initiate',
                'status' => DonationStatus::Pending->value,
                'payload' => ['reference' => $initiation->reference],
            ]);

            return ['donation' => $donation, 'redirect_url' => $initiation->redirectUrl];
        });
    }

    /**
     * Handle a server-to-server payment callback/webhook from a real bank
     * gateway: verify it actually came from the bank, then apply it.
     */
    public function handleCallback(DonationGatewayEnum $gateway, Request $request): Donation
    {
        $adapter = $this->gatewayFactory->make($gateway);

        abort_unless($adapter->verifySignature($request), 400, 'Invalid signature.');

        return $this->applyCallbackResult($gateway, $adapter->parseCallback($request));
    }

    /**
     * Simulate a bank callback for the sandbox "fake bank" checkout page,
     * bypassing a real network round trip while going through the same
     * status-update path a genuine callback would.
     */
    public function markSandboxResult(Donation $donation, bool $success): Donation
    {
        $result = new GatewayCallbackResult(
            reference: $donation->gateway_reference,
            success: $success,
            raw: ['source' => 'sandbox'],
        );

        return $this->applyCallbackResult(DonationGatewayEnum::from($donation->gateway), $result);
    }

    /**
     * Attempt to charge every recurring plan due today. Intended to run
     * daily via the scheduler (see routes/console.php). Failures — including
     * a bank gateway that isn't implemented yet — are logged per-plan
     * rather than aborting the whole run.
     */
    public function processRecurringPlans(): void
    {
        foreach ($this->donationRepository->duePlans() as $plan) {
            $gatewayEnum = DonationGatewayEnum::from($plan->gateway);
            $gateway = $this->gatewayFactory->make($gatewayEnum);

            try {
                $result = $gateway->chargeToken($plan);
            } catch (\Throwable $e) {
                $this->donationRepository->logTransaction([
                    'donation_plan_id' => $plan->id,
                    'gateway' => $plan->gateway,
                    'type' => 'recharge',
                    'status' => DonationStatus::Failed->value,
                    'payload' => ['error' => $e->getMessage()],
                ]);

                continue;
            }

            $donation = $this->donationRepository->create([
                'uuid' => (string) Str::uuid(),
                'fund_id' => $plan->fund_id,
                'user_id' => $plan->user_id,
                'donation_plan_id' => $plan->id,
                'amount' => $plan->amount,
                'currency' => $plan->currency,
                'gateway' => $plan->gateway,
                'status' => $result->success ? DonationStatus::Paid->value : DonationStatus::Failed->value,
                'gateway_reference' => $result->reference,
                'paid_at' => $result->success ? now() : null,
            ]);

            $this->donationRepository->logTransaction([
                'donation_id' => $donation->id,
                'donation_plan_id' => $plan->id,
                'gateway' => $plan->gateway,
                'type' => 'recharge',
                'status' => $donation->status,
                'payload' => $result->raw,
            ]);

            $this->donationRepository->updatePlan($plan, [
                'next_charge_at' => now()->addMonthNoOverflow(),
                'last_charged_at' => now(),
            ]);
        }
    }

    public function listForAdmin(array $filters, int $perPage = 20): LengthAwarePaginator
    {
        return $this->donationRepository->paginateAll($filters, $perPage);
    }

    public function listForUser(User $user, int $perPage = 20): LengthAwarePaginator
    {
        return $this->donationRepository->forUser($user, $perPage);
    }

    /**
     * @return array{total_paid: float, this_month: float, this_year: float}
     */
    public function dashboardTotals(): array
    {
        return [
            'total_paid' => $this->donationRepository->totalPaid(),
            'this_month' => $this->donationRepository->totalPaid([
                'from' => now()->startOfMonth()->toDateString(),
                'to' => now()->toDateString(),
            ]),
            'this_year' => $this->donationRepository->totalPaid([
                'from' => now()->startOfYear()->toDateString(),
                'to' => now()->toDateString(),
            ]),
        ];
    }

    public function exportCsvRows(array $filters): array
    {
        return $this->donationRepository->all($filters)
            ->map(fn (Donation $donation) => [
                $donation->created_at->format('Y-m-d H:i'),
                $donation->fund?->name,
                $donation->user?->name,
                $donation->amount,
                $donation->currency,
                $donation->gateway,
                $donation->status,
                $donation->donation_plan_id ? 'recurring' : 'one-time',
            ])
            ->all();
    }

    /**
     * @return array{id: int, uuid: string, amount: float, currency: string, gateway: string, status: string, is_recurring: bool, fund: ?array, donor: ?array, paid_at: ?string, created_at: string}
     */
    public function toArray(Donation $donation): array
    {
        return [
            'id' => $donation->id,
            'uuid' => $donation->uuid,
            'amount' => (float) $donation->amount,
            'currency' => $donation->currency,
            'gateway' => $donation->gateway,
            'status' => $donation->status,
            'is_recurring' => $donation->donation_plan_id !== null,
            'fund' => $donation->fund ? ['id' => $donation->fund->id, 'name' => $donation->fund->name] : null,
            'donor' => $donation->user ? ['id' => $donation->user->id, 'name' => $donation->user->name] : null,
            'paid_at' => $donation->paid_at?->format('d M Y, H:i'),
            'created_at' => $donation->created_at->format('d M Y, H:i'),
        ];
    }

    private function applyCallbackResult(DonationGatewayEnum $gateway, GatewayCallbackResult $result): Donation
    {
        $donation = $this->donationRepository->findByReference($result->reference);

        abort_if($donation === null, 404, "Unknown donation reference: {$result->reference}");

        $status = $result->success ? DonationStatus::Paid : DonationStatus::Failed;

        $donation = $this->donationRepository->update($donation, [
            'status' => $status->value,
            'paid_at' => $result->success ? now() : null,
        ]);

        $this->donationRepository->logTransaction([
            'donation_id' => $donation->id,
            'donation_plan_id' => $donation->donation_plan_id,
            'gateway' => $gateway->value,
            'type' => 'callback',
            'status' => $status->value,
            'payload' => $result->raw,
        ]);

        if ($result->success && $donation->donation_plan_id) {
            // First successful payment on a recurring plan activates its
            // schedule and stores the gateway's reference as the reusable
            // charge token for future installments.
            $this->donationRepository->updatePlan($donation->donationPlan, [
                'gateway_token' => $donation->gateway_reference,
                'next_charge_at' => now()->addMonthNoOverflow(),
                'last_charged_at' => now(),
            ]);
        }

        return $donation;
    }
}
