<?php

namespace App\Listeners;

use App\Models\Project;
use Illuminate\Contracts\Queue\ShouldQueue;
use Illuminate\Support\Facades\Hash;
use Illuminate\Support\Facades\Http;

class GroupUsersUpdated implements ShouldQueue
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
        $users = $event->users;
        $group = $event->group;
        $projects = Project::whereNotNull('url')->select('name', 'url', 'secret_key')->get();
        $projects->each(function ($project) use ($group, $users) {

            Http::put($project->url.'/api/groups/users', [
                'secret_key' => Hash::make($project->secret_key),
                'group' => $group,
                'users' => [
                    $users->map(function ($user) use ($project) {
                        return $user->arToken->{$project->name} ?? null;
                    }),
                ],
            ]);
        });
    }
}
