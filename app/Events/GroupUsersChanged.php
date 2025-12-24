<?php

namespace App\Events;

use Illuminate\Broadcasting\InteractsWithSockets;
use Illuminate\Foundation\Events\Dispatchable;
use Illuminate\Queue\SerializesModels;

class GroupUsersChanged
{
    use Dispatchable, InteractsWithSockets, SerializesModels;

    public $group;

    public $users;

    /**
     * Create a new event instance.
     */
    public function __construct($group, $users)
    {
        $this->group = $group;
        $this->users = $users;
    }
}
