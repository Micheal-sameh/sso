<?php

namespace App\Events;

use App\Models\Group;
use Illuminate\Foundation\Events\Dispatchable;
use Illuminate\Queue\SerializesModels;

class GroupCreated
{
    use Dispatchable, SerializesModels;

    public $group;

    /**
     * Create a new event instance.
     */
    public function __construct(Group $group)
    {
        $this->group = $group;
    }
}
