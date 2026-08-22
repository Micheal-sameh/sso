<?php

namespace App\Enums;

enum DonationPlanStatus: string
{
    case Active = 'active';
    case Cancelled = 'cancelled';
}
