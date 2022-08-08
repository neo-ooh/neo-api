<?php
/*
 * Copyright 2020 (c) Neo-OOH - All Rights Reserved
 * Unauthorized copying of this file, via any medium is strictly prohibited
 * Proprietary and confidential
 * Written by Valentin Dufois <vdufois@neo-ooh.com>
 *
 * @neo/api - DeleteCreativeJob.php
 */

namespace Neo\Modules\Broadcast\Jobs\Creatives;

use Illuminate\Database\Eloquent\Collection;
use Neo\Modules\Broadcast\Enums\BroadcastJobType;
use Neo\Modules\Broadcast\Exceptions\InvalidBroadcasterAdapterException;
use Neo\Modules\Broadcast\Jobs\BroadcastJobBase;
use Neo\Modules\Broadcast\Models\Creative;
use Neo\Modules\Broadcast\Models\ExternalResource;
use Neo\Modules\Broadcast\Models\Network;
use Neo\Modules\Broadcast\Services\BroadcasterAdapterFactory;
use Neo\Modules\Broadcast\Services\BroadcasterOperator;
use Neo\Modules\Broadcast\Services\BroadcasterScheduling;
use Spatie\DataTransferObject\Exceptions\UnknownProperties;

/**
 * @extends BroadcastJobBase<array>
 */
class DeleteCreativeJob extends BroadcastJobBase {
    public function __construct(int $creativeId) {
        parent::__construct(BroadcastJobType::DeleteCreative, $creativeId);
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

        /** @var Collection<ExternalResource> $externalRepresentations */
        $externalRepresentations = $creative->external_representations;

        /** @var ExternalResource $externalCreative */
        foreach ($externalRepresentations as $externalCreative) {
            /** @var Network $network */
            $network = Network::query()->where("connection_id", "=", $externalCreative->broadcaster_id)
                              ->with(["broadcaster_connection"])
                              ->first();

            /** @var BroadcasterOperator&BroadcasterScheduling $broadcaster */
            $broadcaster = BroadcasterAdapterFactory::make($network->broadcaster_connection, $network);

            $broadcaster->deleteCreative($externalCreative->toResource());

            $externalCreative->delete();
        }

        return null;
    }
}
