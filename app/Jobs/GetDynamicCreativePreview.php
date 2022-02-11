<?php

namespace Neo\Jobs;

use Illuminate\Bus\Queueable;
use Illuminate\Contracts\Queue\ShouldQueue;
use Illuminate\Foundation\Bus\Dispatchable;
use Illuminate\Queue\InteractsWithQueue;
use Illuminate\Queue\SerializesModels;
use Illuminate\Support\Facades\Http;
use Illuminate\Support\Facades\Storage;
use Illuminate\Support\Str;
use Neo\Models\Creative;

class GetDynamicCreativePreview implements ShouldQueue {
    use Dispatchable, InteractsWithQueue, Queueable, SerializesModels;

    protected int $creativeId;
    protected bool $force;

    /**
     * Create a new job instance.
     *
     * @return void
     */
    public function __construct(int $creativeId, bool $force = false) {
        $this->creativeId = $creativeId;
        $this->force      = $force;
    }

    /**
     * Execute the job.
     *
     * @return void
     */
    public function handle() {
        // Get the creative
        /** @var Creative $creative */
        $creative = Creative::query()->find($this->creativeId);

        // Make sure the creative exist and is a dynamic one
        if (!$creative || $creative->type !== Creative::TYPE_DYNAMIC) {
            return;
        }

        // Only get a new thumbnail if there isn't already one or if the force flag is set
        if ($creative->properties->thumbnail_path && !$this->force) {
            return; // Creative is good
        }

        if ($creative->properties->thumbnail_path) {
            // Delete existing creative
            Storage::disk("public")->delete($creative->properties->thumbnail_path);
        }

        // Get the link target and validate its type
        $file = Http::get($creative->properties->url);

        if ($file->failed()) {
            // try again later
            $this->release(300);
            return;
        }

        // Check the file is an image
        if (!Str::startsWith($file->header("Content-Type"), "image/")) {
            // Not an image, fallback
            //  TODO: Finish this
        }
    }
}
