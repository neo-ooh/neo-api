<?php
/*
 * Copyright 2023 (c) Neo-OOH - All Rights Reserved
 * Unauthorized copying of this file, via any medium is strictly prohibited
 * Proprietary and confidential
 * Written by Valentin Dufois <vdufois@neo-ooh.com>
 *
 * @neo/api - Bundle.php
 */

namespace Neo\Modules\Broadcast\Services\BroadSign\Models;

use Neo\Modules\Broadcast\Services\BroadSign\API\BroadSignClient;
use Neo\Modules\Broadcast\Services\BroadSign\API\BroadSignEndpoint;
use Neo\Modules\Broadcast\Services\BroadSign\API\Parsers\ResourceIDParser;
use Neo\Modules\Broadcast\Services\BroadSign\API\Parsers\SingleResourcesParser;
use Neo\Services\API\Parsers\MultipleResourcesParser;

/**
 * A Bundle is Broadsign terminology for a Contents
 *
 * @package Neo\BroadSign\Models
 *
 * @property bool   $active
 * @property bool   $allow_custom_duration
 * @property string $attributes
 * @property bool   $auto_synchronized
 * @property int    $category_id
 * @property int    $container_id
 * @property int    $domain_id
 * @property bool   $fullscreen
 * @property int    $id
 * @property int    $interactivity_timeout
 * @property int    $interactivity_trigger_id
 * @property int    $loop_category_id
 * @property int    $loop_positions
 * @property int    $loop_weight
 * @property int    $max_duration_msec
 * @property string $name
 * @property int    $parent_id
 * @property int    $position
 * @property string $secondary_sep_category_ids
 * @property int    $trigger_category_id
 *
 * @method static static|null get(BroadSignClient $client, int $bundleId)
 * @method void addResourceCriteria(array $properties)
 */
class Bundle extends BroadSignModel {
	protected static string $unwrapKey = "bundle";

	protected static function actions(): array {
		return [
			"all"                 => BroadSignEndpoint::get("/bundle/v12")
			                                          ->unwrap(static::$unwrapKey)
			                                          ->parser(new MultipleResourcesParser(static::class)),
			"get"                 => BroadSignEndpoint::get("/bundle/v12/{id}")
			                                          ->unwrap(static::$unwrapKey)
			                                          ->parser(new SingleResourcesParser(static::class)),
			"create"              => BroadSignEndpoint::post("/bundle/v12/add")
			                                          ->unwrap(static::$unwrapKey)
			                                          ->parser(new ResourceIDParser()),
			"update"              => BroadSignEndpoint::put("/bundle/v12")
			                                          ->unwrap(static::$unwrapKey)
			                                          ->parser(new SingleResourcesParser(static::class)),
			"associate"           => BroadSignEndpoint::post("/bundle_content/v5/add")
			                                          ->unwrap('bundle_content')
			                                          ->parser(new ResourceIDParser()),
			"bySchedule"          => BroadSignEndpoint::get("/bundle/v12/by_schedule")
			                                          ->unwrap(static::$unwrapKey)
			                                          ->parser(new MultipleResourcesParser(static::class)),
			"byReservable"        => BroadSignEndpoint::get("/bundle/v12/by_reservable")
			                                          ->unwrap(static::$unwrapKey)
			                                          ->parser(new MultipleResourcesParser(static::class)),
			"addResourceCriteria" => BroadSignEndpoint::post("/resource_criteria/v7/add")
			                                          ->unwrap("resource_criteria")
			                                          ->parser(new ResourceIDParser()),
		];
	}

	protected static array $updatable = [
		"active",
		"allow_custom_duration",
		"attributes",
		"auto_synchronized",
		"category_id",
		"domain_id",
		"fullscreen",
		"id",
		"interactivity_timeout",
		"interactivity_trigger_id",
		"loop_category_id",
		"loop_positions",
		"loop_weight",
		"max_duration_msec",
		"name",
		"position",
		"secondary_sep_category_ids",
		"trigger_category_id",
	];


	public function addCriteria(int $criteriaID, int $type) {
		return $this->addResourceCriteria([
			                                  "active"      => true,
			                                  "criteria_id" => $criteriaID,
			                                  "parent_id"   => $this->id,
			                                  "type"        => $type,
		                                  ]);
	}
}
