<?php

namespace App\Enums;

enum AdminPermission: string
{
    case AccessAdminPanel = 'access admin panel';
    case ManageAdminAccess = 'manage admin access';
}
