<?php
/*
 * Copyright 2020 (c) Neo-OOH - All Rights Reserved
 * Unauthorized copying of this file, via any medium is strictly prohibited
 * Proprietary and confidential
 * Written by Valentin Dufois <vdufois@neo-ooh.com>
 *
 * @neo/api - Inventory.php
 */

namespace Neo\BroadSign\Models;

use Illuminate\Support\Collection;
use Neo\BroadSign\Endpoint;
use Neo\Models\Report;

/**
 * Class Inventory
 *
 * @package Neo\BroadSign\Models
 *
 * @property int        domain_id
 * @property int        id
 * @property int        skin_id
 * @property int        year
 * @property int        inventory_size
 *
 * @property Collection inventory
 *
 * @property Skin       skin
 * @property LoopPolicy loop_policy
 *
 * @method static Inventory[] all(array $params)
 */
class Inventory extends BroadSignModel {

    protected static string $unwrapKey = "inventory";

    protected static function actions (): array {
        return [
            "all" => Endpoint::get("/inventory/v1")->customTransform("processInventory"),
        ];
    }

    public static function processInventory ($inventory): Collection {
        /** @var Collection<Report> $reports */
        $reports = static::asMultipleSelf($inventory);

        $reports->map(fn(Inventory $inventory) => $inventory->processReport());

        return $reports;
    }

    protected function processReport (): void {
        // Parse the inventory string to an array
        $inventory = rtrim(ltrim($this->inventory, '{'), '}');
        $this->inventory = collect(explode(',', $inventory));

        // Transform each value to an appropriate number
        $this->inventory = $this->inventory->map(fn ($val) => (int)$val / 10_000.0);
        $this->inventory_size = $this->inventory->count();
    }

    public static function forDisplayUnit(int $displayUnitId, int $year) {
        // Load the frames for the display unit
        $skins = Skin::byDisplayUnit(["display_unit_id" => $displayUnitId]);

        // Load the current inventory state
        /** @var Collection<Inventory> $inventory */
        $inventory = static::all(["year" => $year]);

        // Load loop policies for each skin
        /** @var Skin $skin */
        foreach ($skins as $skin) {
            $skin->loop_policy = LoopPolicy::get($skin->loop_policy_id);

            // Calculate the maximum booking
            $skin->loop_policy->max_booking = $skin->loop_policy->max_duration_msec / $skin->loop_policy->default_slot_duration;

            // Inject the inventory state
            $skin->inventory = $inventory->first(fn($inventory) => $inventory->skin_id === $skin->id);
        }

        return $skins;
    }
}
