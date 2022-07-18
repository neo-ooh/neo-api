<?php
/*
 * Copyright 2020 (c) Neo-OOH - All Rights Reserved
 * Unauthorized copying of this file, via any medium is strictly prohibited
 * Proprietary and confidential
 * Written by Valentin Dufois <vdufois@neo-ooh.com>
 *
 * @neo/api - POPTypeNetworkValues.php
 */

namespace Neo\Documents\POP;

use Carbon\Carbon;
use JetBrains\PhpStorm\ArrayShape;
use Neo\Modules\Broadcast\Models\Network;

class POPTypeNetworkValues {
    public Network $network;

    public int $network_id;
    public Carbon $start_date;
    public Carbon $end_date;
    public int $contracted_impressions;
    public float $media_value;
    public float $net_investment;
    public float $adjustment_factor;
    public float $received_impressions;

    public float $counted_impressions;

    public function __construct(#[ArrayShape([
        "network_id"             => 'integer',
        "start_date"             => 'string',
        "end_date"               => 'string',
        "contracted_impressions" => "integer",
        "media_value"            => "float",
        "net_investment"         => "float",
        "adjustment_factor"      => "float",
        "received_impressions"   => "float",
    ])] array $data) {
        $this->network_id             = $data["network_id"];
        $this->start_date             = Carbon::parse($data["start_date"]);
        $this->end_date               = Carbon::parse($data["end_date"]);
        $this->contracted_impressions = $data["contracted_impressions"];
        $this->media_value            = $data["media_value"];
        $this->net_investment         = $data["net_investment"];
        $this->adjustment_factor      = $data["adjustment_factor"];
        $this->received_impressions   = $data["received_impressions"];

        $this->network = Network::query()->find($this->network_id);

        $this->counted_impressions = $this->received_impressions * $this->adjustment_factor;
    }
}
