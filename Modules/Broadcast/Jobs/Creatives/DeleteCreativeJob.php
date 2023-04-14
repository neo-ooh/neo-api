<?php
/*
 * Copyright 2023 (c) Neo-OOH - All Rights Reserved
 * Unauthorized copying of this file, via any medium is strictly prohibited
 * Proprietary and confidential
 * Written by Valentin Dufois <vdufois@neo-ooh.com>
 *
 * @neo/api - DeleteCreativeJob.php
 */

namespace Neo\Modules\Broadcast\Jobs\Creatives;

use Neo\Modules\Broadcast\Enums\BroadcastJobType;
use Neo\Modules\Broadcast\Exceptions\InvalidBroadcasterAdapterException;
use Neo\Modules\Broadcast\Jobs\BroadcastJobBase;
use Neo\Modules\Broadcast\Models\BroadcastJob;
use Neo\Modules\Broadcast\Models\Creative;
use Neo\Modules\Broadcast\Models\ExternalResource;
use Neo\Modules\Broadcast\Services\BroadcasterAdapterFactory;
use Neo\Modules\Broadcast\Services\BroadcasterOperator;
use Neo\Modules\Broadcast\Services\BroadcasterScheduling;

/**
 * @extends BroadcastJobBase<array{resource_id: int|null}>
 */
class DeleteCreativeJob extends BroadcastJobBase {
    public function __construct(int $creativeId, int|null $resourceId = null, BroadcastJob|null $broadcastJob = null) {
        parent::__construct(BroadcastJobType::DeleteCreative, $creativeId, ["resource_id" => $resourceId], $broadcastJob);
    }

    /**
     * @inheritDoc
     * @return array|null
     * @throws InvalidBroadcasterAdapterException
     */
    protected function run(): array|null {
        /** @var Creative|null $creative */
        $creative = Creative::withTrashed()->find($this->resourceId);

        if (!$creative) {
            return null;
        }

        if (is_array($this->payload) && $this->payload["resource_id"]) {
            $externalResource  = ExternalResource::query()->find($this->payload["resource_id"]);
            $externalResources = $externalResource !== null ? [$externalResource] : [];
        } else {
            $externalResources = $creative->external_representations->whereNull("deleted_at");
        }

        /** @var ExternalResource $externalCreative */
        foreach ($externalResources as $externalCreative) {
            /** @var BroadcasterOperator&BroadcasterScheduling $broadcaster */
            $broadcaster = BroadcasterAdapterFactory::makeForBroadcaster($externalCreative->broadcaster_id);

            $broadcaster->deleteCreative($externalCreative->toResource());

            $externalCreative->delete();
        }

        return null;
    }
}
