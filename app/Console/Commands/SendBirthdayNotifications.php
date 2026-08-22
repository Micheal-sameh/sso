<?php

namespace App\Console\Commands;

use App\DTOs\NotificationDTO;
use App\Enums\NotificationTargetType;
use App\Enums\NotificationType;
use App\Repositories\Contracts\UserRepositoryContract;
use App\Services\NotificationService;
use Illuminate\Console\Command;

class SendBirthdayNotifications extends Command
{
    protected $signature = 'birthdays:notify';

    protected $description = 'Send a happy birthday notification to every active user whose birthday is today';

    public function handle(UserRepositoryContract $userRepository, NotificationService $notificationService): int
    {
        $users = $userRepository->withBirthdayToday();

        foreach ($users as $user) {
            $notificationService->send(
                new NotificationDTO(
                    message: "Happy Birthday, {$user->name}! 🎉 Wishing you a wonderful day.",
                    targetType: NotificationTargetType::Specific,
                    stageIds: [],
                    groupIds: [],
                    userIds: [$user->id],
                    image: null,
                    type: NotificationType::Birthday,
                ),
                null,
            );
        }

        $this->info("Sent birthday notifications to {$users->count()} user(s).");

        return self::SUCCESS;
    }
}
