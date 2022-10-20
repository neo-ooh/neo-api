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
use Neo\Modules\Broadcast\Enums\BroadcastTagType;
use Neo\Modules\Broadcast\Enums\CreativeType;
use Neo\Modules\Broadcast\Enums\ExternalResourceType;
use Neo\Modules\Broadcast\Exceptions\InvalidBroadcasterAdapterException;
use Neo\Modules\Broadcast\Jobs\BroadcastJobBase;
use Neo\Modules\Broadcast\Models\BroadcastJob;
use Neo\Modules\Broadcast\Models\Creative;
use Neo\Modules\Broadcast\Models\ExternalResource;
use Neo\Modules\Broadcast\Models\Network;
use Neo\Modules\Broadcast\Models\StructuredColumns\ExternalResourceData;
use Neo\Modules\Broadcast\Services\BroadcasterAdapterFactory;
use Neo\Modules\Broadcast\Services\BroadcasterCapability;
use Neo\Modules\Broadcast\Services\BroadcasterOperator;
use Neo\Modules\Broadcast\Services\BroadcasterScheduling;
use Neo\Modules\Broadcast\Services\Resources\CreativeStorageType;
use Neo\Modules\Broadcast\Services\Resources\ExternalBroadcasterResourceId;
use Neo\Modules\Broadcast\Utils\BroadcastTagsCollector;
use Spatie\DataTransferObject\Exceptions\UnknownProperties;

/**
 * @extends BroadcastJobBase<array{broadcasterId: int}>
 */
class ImportCreativeJob extends BroadcastJobBase {
    public function __construct(int $creativeId, int $broadcasterId, BroadcastJob|null $broadcastJob = null) {
        parent::__construct(BroadcastJobType::ImportCreative, $creativeId, ["broadcasterId" => $broadcasterId], $broadcastJob);
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
                "message" => "Creative is trashed",
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

        // Check if the creative already has an external ID for this broadcaster
        $externalRepresentation = $creative->getExternalRepresentation($broadcaster->getBroadcasterId());

        if ($externalRepresentation !== null) {
            return [
                "error"   => false,
                "message" => "Creative is already represented in this broadcaster",
            ];
        }

        $tags = new BroadcastTagsCollector();
        $tags->collect($creative->frame->broadcast_tags, [BroadcastTagType::Targeting]);
        $creativeTags = $tags->get($broadcaster->getBroadcasterId());

        $importType = $creative->type === CreativeType::Url ? CreativeStorageType::Link : CreativeStorageType::File;

        $creativeExternalId = $broadcaster->importCreative($creative->toResource(), $importType, $creativeTags);

        $externalResource = new ExternalResource([
            "resource_id"    => $creative->getKey(),
            "broadcaster_id" => $broadcaster->getBroadcasterId(),
            "type"           => ExternalResourceType::Creative,
            "data"           => new ExternalResourceData([
                "external_id" => $creativeExternalId->external_id,
            ]),
        ]);
        $externalResource->save();

        return [$creativeExternalId];
    }
}
