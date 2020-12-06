<?php
/*
 * Copyright 2020 (c) Neo-OOH - All Rights Reserved
 * Unauthorized copying of this file, via any medium is strictly prohibited
 * Proprietary and confidential
 * Written by Valentin Dufois <Valentin Dufois>
 *
 * @neo/api - Campaign.php
 */

namespace Neo\BroadSign\Models;

use Illuminate\Support\Collection;
use Neo\BroadSign\Endpoint;

/**
 * Class Campaigns
 * @package Neo\BroadSign\Models
 *
 * @property bool   active
 * @property bool   auto_synchronize_bundles
 * @property int    bmb_host_id
 * @property int    booking_state
 * @property string booking_state_calculated_on
 * @property int    container_id
 * @property string creation_tm
 * @property int    creation_user_id
 * @property int    day_of_week_mask
 * @property string default_attributes
 * @property int    default_bundle_weight
 * @property int     default_category_id
 * @property bool   default_fullscreen
 * @property int    default_interactivity_timeout
 * @property string default_interactivity_trigger_id
 * @property int    default_schedule_id
 * @property int    default_secondary_sep_category_ids
 * @property int    default_segment_category_id
 * @property int    default_trigger_category_id
 * @property int    domain_id
 * @property int    duration_msec
 * @property string end_date
 * @property string end_time
 * @property int    estimated_reps
 * @property int    goal_amount
 * @property string goal_reached_on_tm
 * @property int    goal_unit
 * @property bool   has_goal
 * @property int    id
 * @property int    media_package_id
 * @property int    name
 * @property int    pacing_period
 * @property int    pacing_target
 * @property int    parent_id
 * @property int    promoter_user_id
 * @property string promotion_time
 * @property string reps_calculated_on
 * @property int    saturation
 * @property string  start_date
 * @property string  start_time
 * @property int     state
 *
 * @method static Campaign get(int $broadsign_reservation_id)
 * @method static int addSkinSlots(array $params)
 * @method static int dropSkinSlots(array $params)
 * @method static int addResourceCriteria(array $params)
 * @method static int promoteSkinSlots(array $params)
 */
class Campaign extends BroadSignModel {

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

    protected static function actions (): array {
        return [
            "all"                 => Endpoint::get("/reservation/v21")->multiple(),
            "create"              => Endpoint::post("/reservation/v21/add")->id(),
            "get"                 => Endpoint::get("/reservation/v21/{id}"),
            "update"              => Endpoint::put("/reservation/v21")->id(),
            "rebook"              => Endpoint::post("/reservation/v21/rebook")->id(),
            "confirm_rebook"      => Endpoint::post("/reservation/v21/rebook_confirm")->id(),
            "addSkinSlots"        => Endpoint::post("/reservation/v21/add_skin_slots")->id(),
            "promoteSkinSlots"    => Endpoint::post("/reservation/v21/promote_skin_slots")->id(),
            "dropSkinSlots"       => Endpoint::post("/reservation/v21/batch_drop_skin_slots")->id(),
            "addResourceCriteria" => Endpoint::post("/resource_criteria/v7/add")->ignore(),
        ];
    }

    /**
     * Get all locations (display_unit) associated with this campaign
     * @return Collection
     */
    public function locations (): Collection {
        return Location::byReservable(["reservable_id" => $this->id]);
    }

    public function addLocations (Collection $display_units_ids): void {
        static::addSkinSlots([
            "id"           => $this->id,
            "sub_elements" => [
                "display_unit"      => $display_units_ids->map(fn ($du) => ["id" => $du])->toArray(),
                "frame_or_criteria" => [
                    ["id" => config("broadsign.advertising-criteria")],
                ],
            ],
        ]);

        // Load the campaign skin slots
        $skinSlots = SkinSlot::forCampaign(["reservable_id" => $this->id]);
        $skinSlotsID = $skinSlots->filter(fn ($skinSlot) => (bool)$skinSlot->active)->map(fn($skinSlot) => $skinSlot->id);

        static::promoteSkinSlots([
            "id" => $this->id,
            "skin_slot_ids" => $skinSlotsID->join(','),
        ]);
    }

    public function addCriteria (int $criteriaID, int $type): void {
        static::addResourceCriteria([
            "active"      => true,
            "criteria_id" => $criteriaID,
            "parent_id"   => $this->id,
            "type"        => $type,
        ]);
    }

    public function removeLocations (Collection $display_units_ids): void {
        static::dropSkinSlots([
            "id"           => $this->id,
            "sub_elements" => [
                "display_unit" => $display_units_ids->map(fn ($du) => ["id" => $du])->toArray(),
            ],
        ]);
    }

    /*
    |--------------------------------------------------------------------------
    | Others
    |--------------------------------------------------------------------------
    */


    public function rebook (): void {
        $rebookableProperties = [
            "day_of_week_mask",
            "domain_id",
            "duration_msec",
            "end_date",
            "end_time",
            "id",
            "start_date",
            "start_time",
        ];

        $properties = array_filter($this->attributes,
            static fn ($key) => in_array($key, $rebookableProperties, true),
            ARRAY_FILTER_USE_KEY);

        $transactionID = $this->callAction("rebook", $properties);
        $this->id = $this->callAction("confirm_rebook",
            [
                "id"                  => $this->id,
                "slot_transaction_id" => $transactionID,
            ]);
    }
}
