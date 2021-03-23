<?php
/*
 * Copyright 2020 (c) Neo-OOH - All Rights Reserved
 * Unauthorized copying of this file, via any medium is strictly prohibited
 * Proprietary and confidential
 * Written by Valentin Dufois <vdufois@neo-ooh.com>
 *
 * @neo/api - Customer.php
 */

namespace Neo\BroadSign\Models;

use Carbon\Carbon;
use Illuminate\Support\Collection;
use Neo\BroadSign\Endpoint;

/**
 * A Customer represent an actual client
 *
 * @package Neo\BroadSign\Models
 *
 * @property bool   active
 * @property string affidavit_footer
 * @property int    container_id
 * @property int    default_category_id
 * @property bool   default_fullscreen
 * @property int    default_priority
 * @property int    domain_id
 * @property int    id
 * @property string insertion_footer
 * @property string locale
 * @property string name
 *
 * @method static Collection all()
 * @method static Customer   get(int $id)
 */
class Customer extends BroadSignModel {

    protected static string $unwrapKey = "customer";

    protected static function actions(): array {
        return [
            "all" => Endpoint::get("/customer/v7")->multiple()->cache(3600),
            "get" => Endpoint::get("/customer/v7/{id}")->cache(3600),
        ];
    }

    /**
     * Lists the customer's campaigns.
     * Only campaigns who have not ended are returned
     *
     * @return Collection
     */
    public function getCampaigns(): Collection {
        return Campaign::all()
                       ->filter(fn($campaign) => $campaign->parent_id === $this->id)
                        ->filter(fn($campaign) => Carbon::parse($campaign->end_date)->isAfter(Carbon::now()))
                       ->values();
    }
}
