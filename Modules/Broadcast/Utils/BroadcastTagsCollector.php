<?php
/*
 * Copyright 2020 (c) Neo-OOH - All Rights Reserved
 * Unauthorized copying of this file, via any medium is strictly prohibited
 * Proprietary and confidential
 * Written by Valentin Dufois <vdufois@neo-ooh.com>
 *
 * @neo/api - BroadcastTagsCollector.php
 */

namespace Neo\Modules\Broadcast\Utils;

use Illuminate\Support\Collection;
use Neo\Modules\Broadcast\Enums\BroadcastTagType;
use Neo\Modules\Broadcast\Models\BroadcastTag;
use Neo\Modules\Broadcast\Services\Resources\Tag as TagResource;

class BroadcastTagsCollector {
    /**
     * @var Collection<BroadcastTag>
     */
    protected Collection $tags;

    public function __construct() {
        $this->tags = new Collection();
    }

    /**
     * @param array<BroadcastTag>|Collection<BroadcastTag> $tags
     * @return void
     */
    public function collect(array|Collection $tags, array|null $types = null): void {
        if (is_array($tags)) {
            $tags = collect($tags);
        }

        if ($types) {
            $tags = $tags->filter(fn(BroadcastTag $tag) => in_array($tag->type, $types, true));
        }

        $this->tags = $this->tags->merge($tags);
    }

    /**
     * @param int                     $broadcasterId
     * @param array<BroadcastTagType> $types
     * @return array<TagResource>
     */
    public function get(int $broadcasterId): array {
        return $this->tags->unique("id")
                          ->map(fn(BroadcastTag $tag) => $tag->toResource($broadcasterId))
                          ->where("external_id", "!==", "-1")
                          ->all();
    }
}
