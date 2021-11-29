<?php
/*
 * Copyright 2020 (c) Neo-OOH - All Rights Reserved
 * Unauthorized copying of this file, via any medium is strictly prohibited
 * Proprietary and confidential
 * Written by Valentin Dufois <vdufois@neo-ooh.com>
 *
 * @neo/api - ImpressionsController.php
 */

namespace Neo\Http\Controllers;

use http\Env\Response;
use http\Exception\InvalidArgumentException;
use Neo\Http\Requests\Impressions\ExportBroadsignImpressionsRequest;
use Neo\Models\Location;
use Neo\Models\Product;
use Neo\Models\Property;
use Neo\Services\Broadcast\Broadcast;
use Neo\Services\Broadcast\BroadSign\API\BroadsignClient;
use Neo\Services\Broadcast\BroadSign\BroadSignConfig;
use Neo\Services\Broadcast\BroadSign\Models\LoopPolicy;
use Neo\Services\Broadcast\BroadSign\Models\Skin;

class ImpressionsController {
    public function broadsignDisplayUnit(ExportBroadsignImpressionsRequest $request, int $displayUnitId) {
        /** @var Location|null $location */
        $location = Location::query()->where("external_id", "=", $displayUnitId);

        if (!$location) {
            throw new InvalidArgumentException("The provided Display Unit Id is not registered on Connect.");
        }

        $config = Broadcast::network($location->network_id)->getConfig();

        if (!($config instanceof BroadSignConfig)) {
            throw new InvalidArgumentException("The provided Display Unit Id is not a BroadSign Display Unit.");
        }

        $client = new BroadsignClient($config);

        // We need to generate a file for each week of the year, for each frame of the display unit
        // Load the property, impressions data and traffic data attached with this location
        /** @var Product|null $product */
        $product = $location->products()->with(["impressions_models", "category.impressions_models"])->first();

        if (!$product) {
            throw new InvalidArgumentException("The Display Unit is not associated with a product.");
        }

        $property = Property::query()->with(["opening_hours", "traffic.weekly_data"])->find($product->property_id);

        // Load all the frames, of the display unit, and load their loop policies as well
        $frames = Skin::byDisplayUnit($client, ["display_unit_id" => $location->external_id]);
        $frames->each(/**
         * @param Skin $frame
         */ function ($frame) use ($client) {
            $frame->loop_policy = LoopPolicy::get($client, $frame->loop_policy_id);
        });

        return new Response([
            "frames" => $frames
        ]);
    }
}
