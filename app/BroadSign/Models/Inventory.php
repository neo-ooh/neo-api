<?php
/*
 * Copyright 2020 (c) Neo-OOH - All Rights Reserved
 * Unauthorized copying of this file, via any medium is strictly prohibited
 * Proprietary and confidential
 * Written by Valentin Dufois <Valentin Dufois>
 *
 * @neo/api - $file.filePath
 */

namespace Neo\BroadSign\Models;

use Illuminate\Database\Eloquent\Collection;
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
 * @property Frame      skin
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

    protected static function processInventory ($inventory): Collection {
        /** @var Collection<Report> $reports */
        $reports = static::asMultipleSelf($inventory);

        $reports->map(fn(Inventory $inventory) => $inventory->processReport());

        return $reports;
    }

    public function processReport (): void {
        // Parse the inventory string to an array
        $inventory = ltrim($this->inventory, '{');
        $inventory = rtrim($inventory, '}');
        $this->inventory = collect(explode(',', $inventory));

        // Transform each value to an appropriate number
        $this->inventory = $this->inventory->map(fn ($val) => (int)$val / 10_000.0);
        $this->inventory_size = $this->inventory->count();

        // Load the skin and the loop slot
        $this->skin = Frame::get($this->skin_id);
        $this->loop_policy = LoopPolicy::get($this->skin->loop_policy_id);

        // Calculate the maximum booking
        $this->loop_policy->max_booking = $this->loop_policy->max_duration_msec / $this->loop_policy->default_slot_duration;
    }
}
