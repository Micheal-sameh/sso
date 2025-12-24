<?php

namespace App\Events;

use Illuminate\Broadcasting\InteractsWithSockets;
use Illuminate\Foundation\Events\Dispatchable;
use Illuminate\Queue\SerializesModels;

class UserGroupsChanged
{
    use Dispatchable, InteractsWithSockets, SerializesModels;

    public $groupIds;

    public $user;

    /**
     * Create a new event instance.
     */
    public function __construct($groupIds, $user)
    {
        $this->groupIds = $groupIds;
        $this->user = $user;
    }
}
