<?php
/*
 * Copyright 2023 (c) Neo-OOH - All Rights Reserved
 * Unauthorized copying of this file, via any medium is strictly prohibited
 * Proprietary and confidential
 * Written by Valentin Dufois <vdufois@neo-ooh.com>
 *
 * @neo/api - LinkettAPIAdapter.php
 */

namespace Neo\Services\Traffic;

use Carbon\Carbon;
use Neo\Modules\Properties\Models\Property;
use Neo\Modules\Properties\Models\TrafficSourceSettingsLinkett;
use Neo\Services\API\Endpoint;

class LinkettAPIAdapter implements TrafficProviderInterface {

    protected LinkettAPIClient $client;
    protected Endpoint $trafficEndpoint;

    public function __construct(TrafficSourceSettingsLinkett $settings) {
        $this->client                   = new LinkettAPIClient($settings->api_key);
        $this->trafficEndpoint          = Endpoint::get("/v1/activity_counters/sum");
        $this->trafficEndpoint->options = [
            "http_errors" => false,
        ];
        $this->trafficEndpoint->parser(new SumResponseParser());
        $this->trafficEndpoint->base = config("modules-legacy.properties.linkett.url");
    }

    public function getTraffic(Property $property, Carbon $from, Carbon $to): int {
        return $this->client->call($this->trafficEndpoint, [
            "categories" => implode(",", config("modules-legacy.properties.linkett.categories")),
            "venues"     => $property->traffic->source[0]->pivot->uid,
            "from"       => $from->format("Y-m-d"),
            "to"         => $to->format("Y-m-d"),
        ]);
    }
}
