<?php

namespace App\Enums;

enum AdminPermission: string
{
    case AccessAdminPanel = 'access admin panel';
    case ManageAdminAccess = 'manage admin access';
    case ManageCmcPosts = 'manage cmc posts';
    case ManageDonations = 'manage donations';
    case ManageSpiritualNotes = 'manage spiritual notes';
}
