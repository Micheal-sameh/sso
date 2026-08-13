<?php

namespace App\Enums;

enum MfaStatus: string
{
    case Enabled = 'enabled';
    case Disabled = 'disabled';
}
