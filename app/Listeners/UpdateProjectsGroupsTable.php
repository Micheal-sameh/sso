<?php

namespace App\Listeners;

use App\Events\GroupUpdated;
use App\Models\Project;
use Illuminate\Contracts\Queue\ShouldQueue;
use Illuminate\Support\Facades\Hash;
use Illuminate\Support\Facades\Http;

class UpdateProjectsGroupsTable implements ShouldQueue
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
        $group = $event->group ?? $event;
        $isUpdate = $event instanceof GroupUpdated;

        $projects = Project::whereNotNull('url')->select('url', 'secret_key')->get();
        $projects->each(function ($project) use ($group, $isUpdate) {
            if ($isUpdate) {
                // Use PUT to update existing group resource
                Http::put($project->url.'/api/groups/'.$group->id, [
                    'secret_key' => Hash::make($project->secret_key),
                    'group' => $group,
                ]);
            } else {
                // Use POST to create new group resource
                Http::post($project->url.'/api/groups', [
                    'secret_key' => Hash::make($project->secret_key),
                    'group' => $group,
                ]);
            }
        });
    }
}
