<?php
/*
 * Copyright 2020 (c) Neo-OOH - All Rights Reserved
 * Unauthorized copying of this file, via any medium is strictly prohibited
 * Proprietary and confidential
 * Written by Valentin Dufois <Valentin Dufois>
 *
 * @neo/api - BroadSign.php
 */

namespace Neo\BroadSign;

use Neo\BroadSign\Models\Inventory;

class BroadSign {
    /*
    |--------------------------------------------------------------------------
    | Properties
    |--------------------------------------------------------------------------
    */

    /**
     * - 'domain_id' => The domain ID identifies resources on the Broadsign network as part of the Neo Network.  It can
     * be omitted without repercussion, but we make sure to include it to be as pedantic as possible while using the
     * API.
     * - customer_id
     * - campaign_length
     * - advertising_criteria_id
     */
    public static function getDefaults(): array {
        return [
            "domain_id"               => config("broadsign.domain-id"),
            "customer_id"             => config("broadsign.customer-id"),
            "category_separation_id"  => config("broadsign.category-separation-id"),
            "campaign_length"         => config("broadsign.default-campaign-length"),
            "advertising_criteria_id" => config("broadsign.advertising-criteria"),
            "left_frame_criteria_id"  => config("broadsign.left-frame-criteria"),
            "right_frame_criteria_id" => config("broadsign.right-frame-criteria"),
        ];
    }

    /*
    |--------------------------------------------------------------------------
    | Inventory
    |--------------------------------------------------------------------------
    */

    /**
     * @param int $year
     *
     * @return Inventory[]
     *
     */
    public function getInventoryReport(int $year): array {
        return Inventory::all(["year" => $year]);
    }
}
