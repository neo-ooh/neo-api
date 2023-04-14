<?php
/*
 * Copyright 2023 (c) Neo-OOH - All Rights Reserved
 * Unauthorized copying of this file, via any medium is strictly prohibited
 * Proprietary and confidential
 * Written by Valentin Dufois <vdufois@neo-ooh.com>
 *
 * @neo/api - Campaign.php
 */

namespace Neo\Modules\Broadcast\Services\BroadSign\Models;

use Illuminate\Support\Collection;
use Neo\Modules\Broadcast\Enums\ExternalResourceType;
use Neo\Modules\Broadcast\Services\BroadSign\API\BroadSignClient;
use Neo\Modules\Broadcast\Services\BroadSign\API\BroadSignEndpoint as Endpoint;
use Neo\Modules\Broadcast\Services\BroadSign\API\Parsers\ResourceIDParser;
use Neo\Modules\Broadcast\Services\BroadSign\API\Parsers\SingleResourcesParser;
use Neo\Modules\Broadcast\Services\BroadSign\BroadSignReservationState;
use Neo\Modules\Broadcast\Services\ResourceCastable;
use Neo\Modules\Broadcast\Services\Resources\Campaign as CampaignResource;
use Neo\Modules\Broadcast\Services\Resources\ExternalBroadcasterResourceId;
use Neo\Services\API\Parsers\MultipleResourcesParser;

/**
 * Class Campaigns
 *
 * @implements ResourceCastable<CampaignResource>
 *
 * @property bool   $active
 * @property bool   $auto_synchronize_bundles
 * @property int    $bmb_host_id
 * @property int    $booking_state
 * @property string $booking_state_calculated_on
 * @property int    $container_id
 * @property string $creation_tm
 * @property int    $creation_user_id
 * @property int    $day_of_week_mask
 * @property string $default_attributes
 * @property int    $default_bundle_weight
 * @property int    $default_category_id
 * @property bool   $default_fullscreen
 * @property int    $default_interactivity_timeout
 * @property string $default_interactivity_trigger_id
 * @property int    $default_schedule_id
 * @property int    $default_secondary_sep_category_ids
 * @property int    $default_segment_category_id
 * @property int    $default_trigger_category_id
 * @property int    $domain_id
 * @property int    $duration_msec
 * @property string $end_date
 * @property string $end_time
 * @property int    $estimated_reps
 * @property int    $goal_amount
 * @property string $goal_reached_on_tm
 * @property int    $goal_unit
 * @property bool   $has_goal
 * @property int    $id
 * @property int    $media_package_id
 * @property string $name
 * @property int    $pacing_period
 * @property int    $pacing_target
 * @property int    $parent_id ID of the customer owning this campaign
 * @property int    $priority
 * @property int    $promoter_user_id
 * @property string $promotion_time
 * @property string $reps_calculated_on
 * @property float  $saturation
 * @property string $start_date
 * @property string $start_time
 * @property int    $state
 *
 * @method static Collection<static> all(BroadSignClient $client)
 * @method static Collection<static> currents(BroadSignClient $client)
 * @method static static|null get(BroadSignClient $client, int $external_id)
 * @method static Collection<static> byId(BroadSignClient $client, array $external_ids)
 *
 * @method void addSkinSlots(array $properties)
 * @method void promoteSkinSlots(array $properties)
 * @method void dropSkinSlots(array $properties)
 * @method void addResourceCriteria(array $properties)
 */
class Campaign extends BroadSignModel implements ResourceCastable {

    protected static string $unwrapKey = "reservation";

    protected static array $updatable = [
        "active",
        "auto_synchronize_bundles",
        "bmb_host_id",
        "container_id",
        "default_attributes",
        "default_bundle_weight",
        "default_category_id",
        "default_fullscreen",
        "default_interactivity_timeout",
        "default_interactivity_trigger_id",
        "default_secondary_sep_category_ids",
        "default_segment_category_id",
        "default_trigger_category_id",
        "domain_id",
        "goal_amount",
        "goal_unit",
        "has_goal",
        "id",
        "name",
        "pacing_period",
        "pacing_target",
        "parent_id",
        "state",
    ];

    protected static function actions(): array {
        return [
            "all"                 => Endpoint::get("/reservation/v22")
                                             ->unwrap(static::$unwrapKey)
                                             ->parser(new MultipleResourcesParser(static::class))
                                             ->cache(3600),
            "currents"            => Endpoint::get("/reservation/v22?current_only=True")
                                             ->unwrap(static::$unwrapKey)
                                             ->parser(new MultipleResourcesParser(static::class))
                                             ->cache(3600),
            "create"              => Endpoint::post("/reservation/v22/add")
                                             ->unwrap(static::$unwrapKey)
                                             ->parser(new ResourceIDParser()),
            "get"                 => Endpoint::get("/reservation/v22/{id}")
                                             ->unwrap(static::$unwrapKey)
                                             ->parser(new SingleResourcesParser(static::class))
                                             ->cache(3600),
            "byId"                => Endpoint::get("/reservation/v22/by_id")
                                             ->unwrap(static::$unwrapKey)
                                             ->parser(new MultipleResourcesParser(static::class)),
            "by_container"        => Endpoint::get("/reservation/v22/by_container")
                                             ->unwrap(static::$unwrapKey)
                                             ->parser(new MultipleResourcesParser(static::class)),
            "update"              => Endpoint::put("/reservation/v22")
                                             ->unwrap(static::$unwrapKey)
                                             ->parser(new ResourceIDParser()),
            "addSkinSlots"        => Endpoint::post("/reservation/v22/add_skin_slots")
                                             ->unwrap(static::$unwrapKey)
                                             ->parser(new ResourceIDParser()),
            "promoteSkinSlots"    => Endpoint::post("/reservation/v22/promote_skin_slots")
                                             ->unwrap(static::$unwrapKey)
                                             ->parser(new ResourceIDParser()),
            "dropSkinSlots"       => Endpoint::post("/reservation/v22/batch_drop_skin_slots")
                                             ->unwrap(static::$unwrapKey)
                                             ->parser(new ResourceIDParser()),
            "addResourceCriteria" => Endpoint::post("/resource_criteria/v7/add")
                                             ->unwrap("resource_criteria")
                                             ->parser(new ResourceIDParser()),
        ];
    }

    /**
     * Get all locations (display_unit) associated with this campaign
     *
     * @return Collection<DisplayUnit>
     */
    public function locations(): Collection {
        return DisplayUnit::byReservable($this->api, $this->getKey());
    }

    /**
     * @return Collection<ResourceCriteria>
     */
    public function criteria(): Collection {
        return ResourceCriteria::for($this->api, $this->getKey());
    }

    public function addCriteria(int $criteriaID, int $type): void {
        $this->addResourceCriteria([
                                       "active"      => true,
                                       "criteria_id" => $criteriaID,
                                       "parent_id"   => $this->id,
                                       "type"        => $type,
                                   ]);
    }

    /**
     * @param array<int> $display_units_ids
     * @param array<int> $criteria
     * @return void
     */
    public function addLocations(array $display_units_ids, array $criteria): void {
        $request = [
            "id"           => $this->id,
            "sub_elements" => [
                "display_unit" => array_map(static fn($du) => ["id" => $du], $display_units_ids),
            ],
        ];

        if (count($criteria) > 0) {
            $request["sub_elements"]["frame_or_criteria"] = array_map(static fn($frame) => ["id" => $frame], $criteria);
        }

        $this->addSkinSlots($request);

        // Load the campaign skin slots
        $skinSlots   = SkinSlot::forCampaign($this->api, ["reservable_id" => $this->id]);
        $skinSlotsID = $skinSlots->filter(fn($skinSlot) => (bool)$skinSlot->active)
                                 ->map(fn($skinSlot) => $skinSlot->id);

        if ($skinSlotsID->count() === 0) {
            // Nothing to promote
            return;
        }

        $this->promoteSkinSlots([
                                    "id"            => $this->id,
                                    "skin_slot_ids" => $skinSlotsID->join(','),
                                ]);
    }

    /**
     * @param Collection<int> $display_units_ids
     * @return void
     */
    public function removeLocations(Collection $display_units_ids): void {
        $this->dropSkinSlots([
                                 "id"           => $this->id,
                                 "sub_elements" => [
                                     "display_unit" => $display_units_ids->map(fn($du) => ["id" => $du])->values()->toArray(),
                                 ],
                             ]);
    }

    /*
    |--------------------------------------------------------------------------
    | Others
    |--------------------------------------------------------------------------
    */

    /**
     * @return CampaignResource
     */
    public function toResource(): CampaignResource {
        $isEnabled = $this->active &&
            $this->state !== BroadSignReservationState::Cancelled->value &&
            $this->state !== BroadSignReservationState::HeldCancelled->value;

        return new CampaignResource(
            enabled            : $isEnabled,
            name               : $this->name,
            start_date         : $this->start_date,
            start_time         : $this->start_time,
            end_date           : $this->end_date,
            end_time           : $this->end_time,
            broadcast_days     : $this->day_of_week_mask,
            priority           : -1,
            occurrences_in_loop: $this->saturation < 1 ? -1 / $this->saturation : $this->saturation,
            advertiser         : $this->parent_id ? new ExternalBroadcasterResourceId(
                                     broadcaster_id: $this->getBroadcasterId(),
                                     external_id   : $this->parent_id,
                                     type          : ExternalResourceType::Advertiser,
                                 ) : null,
            duration_msec      : $this->duration_msec,
        );
    }
}
