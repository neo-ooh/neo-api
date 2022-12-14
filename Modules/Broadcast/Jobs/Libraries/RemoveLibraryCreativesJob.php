<?php
/*
 * Copyright 2022 (c) Neo-OOH - All Rights Reserved
 * Unauthorized copying of this file, via any medium is strictly prohibited
 * Proprietary and confidential
 * Written by Valentin Dufois <vdufois@neo-ooh.com>
 *
 * @neo/api - RemoveLibraryCreativesJob.php
 */

namespace Neo\Modules\Broadcast\Jobs\Libraries;

use Illuminate\Bus\Queueable;
use Illuminate\Contracts\Queue\ShouldQueue;
use Illuminate\Foundation\Bus\Dispatchable;
use Illuminate\Queue\InteractsWithQueue;
use Illuminate\Queue\SerializesModels;
use Neo\Modules\Broadcast\Models\Content;
use Neo\Modules\Broadcast\Models\Library;

class RemoveLibraryCreativesJob implements ShouldQueue {
    use Dispatchable, InteractsWithQueue, Queueable, SerializesModels;

    public function __construct(protected int $libraryId) {
    }

    public function handle() {
        /** @var Library $library */
        $library = Library::withTrashed()->findOrFail($this->libraryId);

        /** @var Content $content */
        foreach ($library->contents as $content) {
            $content->delete();
        }
    }
}
