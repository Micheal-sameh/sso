<?php

namespace App\Observers;

use App\Events\GroupUpdated;
use App\Models\Group;

class GroupObserver
{
    /**
     * Handle the Group "updated" event.
     */
    public function updated(Group $group): void
    {
        event(new GroupUpdated($group));
    }
}
