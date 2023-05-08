<?php
/*
 * Copyright 2023 (c) Neo-OOH - All Rights Reserved
 * Unauthorized copying of this file, via any medium is strictly prohibited
 * Proprietary and confidential
 * Written by Valentin Dufois <vdufois@neo-ooh.com>
 *
 * @neo/api - VistarAdapter.php
 */

namespace Neo\Modules\Properties\Services\Vistar;

use Carbon\Carbon;
use GuzzleHttp\Exception\GuzzleException;
use Illuminate\Http\Client\RequestException;
use Illuminate\Support\LazyCollection;
use Neo\Modules\Properties\Enums\MediaType;
use Neo\Modules\Properties\Enums\PriceType;
use Neo\Modules\Properties\Enums\ProductType;
use Neo\Modules\Properties\Models\InventoryProvider;
use Neo\Modules\Properties\Services\Exceptions\IncompatibleResourceAndInventoryException;
use Neo\Modules\Properties\Services\InventoryAdapter;
use Neo\Modules\Properties\Services\InventoryCapability;
use Neo\Modules\Properties\Services\InventoryConfig;
use Neo\Modules\Properties\Services\Resources\BroadcastLocation;
use Neo\Modules\Properties\Services\Resources\BroadcastPlayer;
use Neo\Modules\Properties\Services\Resources\Enums\InventoryResourceType;
use Neo\Modules\Properties\Services\Resources\IdentifiableProduct;
use Neo\Modules\Properties\Services\Resources\InventoryResourceId;
use Neo\Modules\Properties\Services\Resources\ProductResource;
use Neo\Modules\Properties\Services\Vistar\Models\Attributes\VenueImpressions;
use Neo\Modules\Properties\Services\Vistar\Models\Attributes\VenueOperatingMinutes;
use Neo\Modules\Properties\Services\Vistar\Models\Venue;
use Neo\Services\API\APIAuthenticationError;
use RuntimeException;
use Traversable;

/**
 * @extends InventoryAdapter<VistarConfig>
 */
class VistarAdapter extends InventoryAdapter {
    protected array $capabilities = [
        InventoryCapability::ProductsRead,
        InventoryCapability::ProductsWrite,
    ];

    /**
     * @inheritDoc
     */
    public static function buildConfig(InventoryProvider $provider): InventoryConfig {
        return new VistarConfig(
            name         : $provider->name,
            inventoryID  : $provider->id,
            inventoryUUID: $provider->uuid,
            api_url      : $provider->settings->api_url,
            api_username : $provider->settings->api_username,
            api_key      : $provider->settings->api_key,
        );
    }

    public function listProducts(?Carbon $ifModifiedSince = null): Traversable {
        return LazyCollection::make(function () {
            $venues = Venue::all($this->getConfig()->getClient());

            foreach ($venues as $venue) {
                yield ResourceFactory::makeIdentifiableProduct($venue, $this->getConfig());
            }
        });
    }

    public function getProduct(InventoryResourceId $productId): IdentifiableProduct {
        $venueId = array_values($productId->context["venues"] ?? [])[0]["id"] ?? null;

        if (!$venueId) {
            throw new RuntimeException("Product has invalid context: No venue id could be found");
        }

        $venue = Venue::find($this->getConfig()->getClient(), $venueId);
        return ResourceFactory::makeIdentifiableProduct($venue, $this->getConfig());
    }

    protected function fillVenue(Venue $venue, BroadcastPlayer $player, ProductResource $product, array $context, float $impressionsShare) {
        $impressionsPerPlay = (collect($product->weekdays_spot_impressions)->sum() / 7) * $impressionsShare;

        $venue->name             = "[TEST] " . trim($player->name);  // TODO: Remove `[TEST]`
        $venue->venue_type       = $context["venue_type"] ?? null;
        $venue->network_id       = $context["network_id"];
        $venue->partner_venue_id = "connect_" . $product->property_connect_id . "_" . $product->product_connect_id . "_" . $player->external_id->external_id;
        $venue->activation_date  = $venue->activation_date ?? null;
//        $venue->excluded_buy_types = $venue->activation_date ?? [];
        $venue->industry_id = $venue->industry_id ?? null;

        $venue->longitude = $product->geolocation->longitude;
        $venue->latitude  = $product->geolocation->latitude;
        $venue->address   = $product->address->full;

        $venue->operating_minutes       = VenueOperatingMinutes::buildFromOperatingHours($product->operating_hours->all());
        $venue->cpm_floor_cents         = (int)round($product->price * 100);
        $venue->impressions             = new VenueImpressions(
            per_spot  : max(1, floor($impressionsPerPlay * 10000) / 10000), // Impressions rounded to 4 decimals
            per_second: 0,
        );
        $venue->registration_id         = $player->external_id->external_id . "_test"; // TODO: Remove `test`
        $venue->video_supported         = in_array(MediaType::Video, $product->allowed_media_types);
        $venue->static_supported        = in_array(MediaType::Image, $product->allowed_media_types);
        $venue->static_duration_seconds = $product->loop_configuration->spot_length_ms / 1_000;
        $venue->height_px               = $product->screen_height_px;
        $venue->width_px                = $product->screen_width_px;
    }

    /**
     * @param ProductResource $product
     * @param array           $context
     * @return InventoryResourceId|null
     * @throws GuzzleException
     * @throws IncompatibleResourceAndInventoryException
     * @throws RequestException
     */
    public function createProduct(ProductResource $product, array $context): InventoryResourceId|null {
        // First, validate the product is compatible with Reach
        if ($product->type !== ProductType::Digital || $product->price_type !== PriceType::CPM) {
            throw new IncompatibleResourceAndInventoryException(0, $this->getInventoryID(), $this->getInventoryType());
        }

        /** @var VistarClient $client */
        $client = $this->getConfig()->getClient();

        $venueIds = [];

        $screensCount = collect($product->broadcastLocations)->sum("screen_count");

        /** @var BroadcastLocation $broadcastLocation */
        foreach ($product->broadcastLocations as $broadcastLocation) {
            /** @var BroadcastPlayer $broadcastPlayer */
            foreach ($broadcastLocation->players as $broadcastPlayer) {
                $impressionsShare = $broadcastPlayer->screen_count / $screensCount;

                $venue = new Venue($client);
                $this->fillVenue($venue, $broadcastPlayer, $product, $context, $impressionsShare);
                $venue->save();

                $venueIds[$broadcastPlayer->id] = ["id" => $venue->getKey(), "name" => $venue->name];
            }
        }

        return new InventoryResourceId(
            inventory_id: $this->getInventoryID(),
            external_id : 'MULTIPLE',
            type        : InventoryResourceType::Product,
            context     : [
                              ...$context,
                              "venues" => $venueIds,
                          ]
        );
    }

    /**
     * @param InventoryResourceId $productId
     * @param ProductResource     $product
     * @return InventoryResourceId|false
     * @throws APIAuthenticationError
     * @throws GuzzleException
     * @throws RequestException
     */
    public function updateProduct(InventoryResourceId $productId, ProductResource $product): InventoryResourceId|false {
        $client = $this->getConfig()->getClient();

        $venueIds = [];

        $screensCount = collect($product->broadcastLocations)->sum("screen_count");
        // For each player, we pull the screen to update it. If the screen does not exist, we create it and update our id/context
        foreach ($product->broadcastLocations as $broadcastLocation) {
            /** @var BroadcastPlayer $broadcastPlayer */
            foreach ($broadcastLocation->players as $broadcastPlayer) {
                $impressionsShare = $broadcastPlayer->screen_count / $screensCount;

                if (!isset($productId->context["venues"][$broadcastPlayer->id])) {
                    // No ID for this location
                    $venue = new Venue($client);
                } else {
                    $venue = Venue::find($client, $productId->context["venues"][$broadcastPlayer->id]["id"]);
                }

                $this->fillVenue($venue, $broadcastPlayer, $product, $productId->context, $impressionsShare);
                $venue->save();

                $venueIds[$broadcastPlayer->id] = ["id" => $venue->getKey(), "name" => $venue->name];
            }
        }

        // We now want to compare the list of screens we just built against the one we were given.
        // Any screen listed in the latter but missing in the former will have to be removed
        $venuesToRemove = array_diff(collect($productId->context["venues"])
                                         ->pluck("id")
                                         ->values()
                                         ->all(),
                                     collect($venueIds)
                                         ->pluck("id")
                                         ->values()
                                         ->all());
        foreach ($venuesToRemove as $venueToRemove) {
            $venue = new Venue($client);
            $venue->setKey($venueToRemove);
            $venue->delete();
        }

        $productId->context["venues"] = $venueIds;

        return $productId;
    }

    /**
     * @param InventoryResourceId $productId
     * @return bool
     * @throws APIAuthenticationError
     * @throws GuzzleException
     * @throws RequestException
     */
    public function removeProduct(InventoryResourceId $productId): bool {
        $client = $this->getConfig()->getClient();

        // We have to remove all the units listed in the product's context
        foreach ($productId->context["venues"] as ["id" => $venueId]) {
            $venue = new Venue($client);
            $venue->setKey($venueId);
            $venue->delete();
        }

        return true;
    }
}
