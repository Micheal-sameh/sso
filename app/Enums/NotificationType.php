<?php

namespace App\Enums;

enum NotificationType: string
{
    case Manual = 'manual';
    case Cmc = 'cmc';
    case Youtube = 'youtube';
    case DonationReminder = 'donation_reminder';
    case Birthday = 'birthday';
    case SpiritualNoteReminder = 'spiritual_note_reminder';

    public function label(): string
    {
        return match ($this) {
            self::Manual => 'Manual',
            self::Cmc => 'CMC Post',
            self::Youtube => 'YouTube Video',
            self::DonationReminder => 'Donation Reminder',
            self::Birthday => 'Birthday',
            self::SpiritualNoteReminder => 'Spiritual Note Reminder',
        };
    }
}
