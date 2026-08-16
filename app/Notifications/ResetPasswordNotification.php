<?php

namespace App\Notifications;

use Illuminate\Bus\Queueable;
use Illuminate\Notifications\Messages\MailMessage;
use Illuminate\Notifications\Notification;

class ResetPasswordNotification extends Notification
{
    use Queueable;

    public function __construct(protected string $token) {}

    /**
     * @return string[]
     */
    public function via(object $notifiable): array
    {
        return ['mail'];
    }

    public function toMail(object $notifiable): MailMessage
    {
        $url = route('password.reset', ['token' => $this->token]).'?email='.urlencode($notifiable->getEmailForPasswordReset());

        return (new MailMessage)
            ->subject('Reset Your Avarewase Password')
            ->greeting("Hi {$notifiable->name},")
            ->line('We received a request to reset the password for your Avarewase account.')
            ->action('Reset Password', $url)
            ->line('This link will expire in 60 minutes.')
            ->line("If you didn't request a password reset, no further action is required.");
    }
}
