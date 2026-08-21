<?php

use App\Enums\AdminPermission;
use Illuminate\Support\Facades\Broadcast;

Broadcast::channel('App.Models.User.{id}', function ($user, $id) {
    return (int) $user->id === (int) $id;
});

Broadcast::channel('cmc-posts.admin', function ($user) {
    return $user->can(AdminPermission::ManageCmcPosts->value);
});
