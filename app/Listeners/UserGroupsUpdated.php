<?php

namespace App\Listeners;

use App\Models\Project;
use Illuminate\Support\Facades\Http;

class UserGroupsUpdated
{
    /**
     * Create the event listener.
     */
    public function __construct()
    {
        //
    }

    /**
     * Handle the event.
     */
    public function handle($event): void
    {
        $user = $event->user;
        $groupIds = $event->groupIds;

        $projects = Project::whereNotNull('url')->select('name', 'url', 'secret_key')->get();
        $projects->each(function ($project) use ($groupIds, $user) {
            Http::put($project->url.'/api/users/groups', [
                'secret_key' => base64_encode($project->secret_key),
                'groupIds' => $groupIds,
                'user' => [
                    'id' => $user->id,
                    'membership_code' => $user->membership_code,
                    'arToken' => $user->arToken->{$project->name} ?? null,
                ],
            ]);
        });
    }
}
