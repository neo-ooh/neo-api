<?php
/*
 * Copyright 2020 (c) Neo-OOH - All Rights Reserved
 * Unauthorized copying of this file, via any medium is strictly prohibited
 * Proprietary and confidential
 * Written by Valentin Dufois <vdufois@neo-ooh.com>
 *
 * @neo/api - ImportCreativeJob.php
 */

namespace Neo\Modules\Broadcast\Jobs\Creatives;

use Neo\Modules\Broadcast\Enums\BroadcastJobType;
use Neo\Modules\Broadcast\Exceptions\InvalidBroadcasterAdapterException;
use Neo\Modules\Broadcast\Jobs\BroadcastJobBase;
use Neo\Modules\Broadcast\Models\Creative;
use Neo\Modules\Broadcast\Models\Network;
use Neo\Modules\Broadcast\Services\BroadcasterAdapterFactory;
use Neo\Modules\Broadcast\Services\BroadcasterCapability;
use Neo\Modules\Broadcast\Services\BroadcasterOperator;
use Neo\Modules\Broadcast\Services\BroadcasterScheduling;
use Neo\Modules\Broadcast\Services\Resources\CreativeStorageType;
use Neo\Modules\Broadcast\Services\Resources\ExternalBroadcasterResourceId;
use Spatie\DataTransferObject\Exceptions\UnknownProperties;

/**
 * @extends BroadcastJobBase<array{broadcasterId: int}>
 */
class ImportCreativeJob extends BroadcastJobBase {
    public function __construct(int $creativeId, int $broadcasterId) {
        parent::__construct(BroadcastJobType::ImportCreative, $creativeId, ["broadcasterId" => $broadcasterId]);
    }

    /**
     * @return ExternalBroadcasterResourceId|null
     */
    public function getLastAttemptResult(): ExternalBroadcasterResourceId|null {
        $result = $this->broadcastJob->last_attempt_result;

        if (count($result) === 1) {
            return $result[0];
        }

        return null;
    }

    /**
     * @inheritDoc
     * @return array|null
     * @throws UnknownProperties
     * @throws InvalidBroadcasterAdapterException
     */
    protected function run(): array|null {
        /** @var Creative $creative */
        $creative = Creative::withTrashed()->find($this->resourceId);

        if ($creative->trashed()) {
            return [
                "error"   => true,
                "message" => "Creative is trashed"
            ];
        }

        /** @var Network $network */
        $network = Network::query()->with(["broadcaster_connection"])
                          ->where("connection_id", "=", $this->payload["broadcasterId"])
                          ->first();

        /** @var BroadcasterOperator & BroadcasterScheduling $broadcaster */
        $broadcaster = BroadcasterAdapterFactory::make($network->broadcaster_connection, $network);

        if (!$broadcaster->hasCapability(BroadcasterCapability::Scheduling)) {
            // Broadcaster does not support scheduling, do nothing
            return [];
        }

        $creativeExternalId = $broadcaster->importCreative($creative->toResource($broadcaster->getBroadcasterId()), CreativeStorageType::Link);

        return [$creativeExternalId];
    }
}
