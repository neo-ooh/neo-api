<?php

namespace Neo\Services\Traffic;

use Arr;
use Carbon\Carbon;
use Carbon\Traits\Date;
use Neo\Models\Property;
use Neo\Models\TrafficSourceSettingsLinkett;
use Neo\Services\API\Endpoint;

class LinkettAPIAdapter implements TrafficProviderInterface {

    protected LinkettAPIClient $client;
    protected Endpoint $trafficEndpoint;

    public function __construct(TrafficSourceSettingsLinkett $settings) {
        $this->client = new LinkettAPIClient($settings->api_key);
        $this->trafficEndpoint = Endpoint::get("/v1/activity_counters/sum");
        $this->trafficEndpoint->options = [];
        $this->trafficEndpoint->parser(new SumResponseParser());
        $this->trafficEndpoint->base = config("linkett.url");
    }

    public function getTraffic(Property $property, Carbon $from, Carbon $to): int {
        return $this->client->call($this->trafficEndpoint, [
            "categories" => implode(",", config("linkett.categories")),
            "venues" => $property->traffic->source[0]->pivot->uid,
            "from" => $from->format("Y-m-d"),
            "to" => $to->format("Y-m-d")
        ]);
    }
}
