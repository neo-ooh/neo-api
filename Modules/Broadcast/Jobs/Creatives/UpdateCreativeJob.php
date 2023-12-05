<?php
/*
 * Copyright 2023 (c) Neo-OOH - All Rights Reserved
 * Unauthorized copying of this file, via any medium is strictly prohibited
 * Proprietary and confidential
 * Written by Valentin Dufois <vdufois@neo-ooh.com>
 *
 * @neo/api - UpdateCreativeJob.php
 */

namespace Neo\Modules\Broadcast\Jobs\Creatives;

use Neo\Modules\Broadcast\Enums\BroadcastJobType;
use Neo\Modules\Broadcast\Enums\BroadcastTagType;
use Neo\Modules\Broadcast\Exceptions\InvalidBroadcasterAdapterException;
use Neo\Modules\Broadcast\Jobs\BroadcastJobBase;
use Neo\Modules\Broadcast\Models\BroadcastJob;
use Neo\Modules\Broadcast\Models\Creative;
use Neo\Modules\Broadcast\Models\ExternalResource;
use Neo\Modules\Broadcast\Services\BroadcasterAdapterFactory;
use Neo\Modules\Broadcast\Services\BroadcasterCapability;
use Neo\Modules\Broadcast\Services\BroadcasterOperator;
use Neo\Modules\Broadcast\Services\BroadcasterScheduling;
use Neo\Modules\Broadcast\Utils\BroadcastTagsCollector;

/**
 * @extends BroadcastJobBase<array{broadcasterId: int}>
 */
class UpdateCreativeJob extends BroadcastJobBase {
	public function __construct(int $creativeId, BroadcastJob|null $broadcastJob = null) {
		parent::__construct(BroadcastJobType::UpdateCreative, $creativeId, null, $broadcastJob);
	}

	/**
	 * @inheritDoc
	 * @return array|null
	 * @throws InvalidBroadcasterAdapterException
	 */
	protected function run(): array|null {
		/** @var Creative $creative */
		$creative = Creative::withTrashed()->find($this->resourceId);

		if (!$creative) {
			return [
				"error"   => true,
				"message" => "Could not find creative",
			];
		}

		if ($creative->trashed()) {
			return [
				"error"   => true,
				"message" => "Creative is trashed",
			];
		}

		// List all active external representation of the creative
		$externalRepresentations = $creative->external_representations;
		$results                 = [
			"success" => [],
			"failed"  => [],
		];

		/** @var ExternalResource $externalRepresentation */
		foreach ($externalRepresentations as $externalRepresentation) {
			/** @var BroadcasterOperator & BroadcasterScheduling $broadcaster */
			$broadcaster = BroadcasterAdapterFactory::makeForBroadcaster($externalRepresentation->broadcaster_id);

			if (!$broadcaster->hasCapability(BroadcasterCapability::Scheduling)) {
				// Broadcaster does not support scheduling, do nothing
				return [
					"error"   => false,
					"message" => "Broadcaster does not support scheduling",
				];
			}

			// Collect creative tags
			$tags = new BroadcastTagsCollector();
			$tags->collect($creative->content->broadcast_tags, [BroadcastTagType::Targeting]);
			$tags->collect($creative->frame->broadcast_tags, [BroadcastTagType::Targeting]);
			$tags->collect($creative->broadcast_tags, [BroadcastTagType::Targeting]);

			$creativeTags = $tags->get($broadcaster->getBroadcasterId());

			// Update representation
			$result = $broadcaster->updateCreative($externalRepresentation->toResource(), $creativeTags);

			if ($result === true) {
				$results['success'][] = $externalRepresentation;
			} else {
				$results['failed'][] = $externalRepresentation;
			}
		}

		return $results;
	}
}
