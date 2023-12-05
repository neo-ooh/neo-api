<?php
/*
 * Copyright 2023 (c) Neo-OOH - All Rights Reserved
 * Unauthorized copying of this file, via any medium is strictly prohibited
 * Proprietary and confidential
 * Written by Valentin Dufois <vdufois@neo-ooh.com>
 *
 * @neo/api - LoopSlot.php
 */

namespace Neo\Modules\Broadcast\Services\BroadSign\Models;

use Carbon\Traits\Date;
use Neo\Modules\Broadcast\Services\BroadSign\API\BroadSignClient;
use Neo\Modules\Broadcast\Services\BroadSign\API\BroadSignEndpoint as Endpoint;
use Neo\Modules\Broadcast\Services\BroadSign\API\Parsers\ResourceIDParser;
use Neo\Services\API\Parsers\MultipleResourcesParser;

/**
 * Class LoopPolicy
 *
 * @package Neo\BroadSign\Models
 *
 * @property bool   $active
 * @property int    $day_of_week_mask
 * @property int    $domain_id
 * @property int    $duration
 * @property Date   $end_date
 * @property string $event_occurrence
 * @property int    $id
 * @property int    $inventory_category_id
 * @property int    $parent_id
 * @property int    $priority
 * @property int    $reps_per_hour
 * @property Date   $start_date
 */
class LoopSlot extends BroadSignModel {

	protected static string $unwrapKey = "loop_slot";

	protected static array $updatable = [
		"id",
		"inventory_category_id",
		"priority",
	];

	protected static function actions(): array {
		return [
			"getByReservable" => Endpoint::get("/loop_slot/v10/by_reservable")
			                             ->unwrap(static::$unwrapKey)
			                             ->parser(new MultipleResourcesParser(static::class)),
			"update"          => Endpoint::put("/loop_slot/v10")
			                             ->domain(false)
			                             ->unwrap(static::$unwrapKey)
			                             ->parser(new ResourceIDParser()),
		];
	}

	/**
	 * @param BroadSignClient $client
	 * @param int             $campaignId
	 * @return array<LoopSlot>
	 */
	public static function forCampaign(BroadSignClient $client, int $campaignId): array {
		return [...(new static($client))->callAction("getByReservable", ["reservable_id" => $campaignId])];
	}
}
