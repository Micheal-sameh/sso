<?php

namespace App\Jobs;

use App\Models\CmcPost;
use App\Services\CmcPostService;
use Illuminate\Bus\Queueable;
use Illuminate\Contracts\Queue\ShouldQueue;
use Illuminate\Foundation\Bus\Dispatchable;
use Illuminate\Queue\InteractsWithQueue;
use Illuminate\Queue\SerializesModels;
use Illuminate\Support\Facades\Storage;

class UpdateCmcPostJob implements ShouldQueue
{
    use Dispatchable, InteractsWithQueue, Queueable, SerializesModels;

    /**
     * @param  string|null  $imagePath  path (on the "local" disk) of the image temporarily stored by the controller
     */
    public function __construct(
        protected CmcPost $cmcPost,
        protected ?string $text,
        protected ?string $url,
        protected ?string $imagePath,
    ) {}

    public function handle(CmcPostService $cmcPostService): void
    {
        $cmcPostService->update(
            cmcPost: $this->cmcPost,
            text: $this->text,
            url: $this->url,
            image: $this->imagePath ? Storage::path($this->imagePath) : null,
        );

        if ($this->imagePath) {
            Storage::delete($this->imagePath);
        }
    }
}
