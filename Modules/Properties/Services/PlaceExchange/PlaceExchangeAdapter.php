<?php
/*
 * Copyright 2023 (c) Neo-OOH - All Rights Reserved
 * Unauthorized copying of this file, via any medium is strictly prohibited
 * Proprietary and confidential
 * Written by Valentin Dufois <vdufois@neo-ooh.com>
 *
 * @neo/api - PlaceExchangeAdapter.php
 */

namespace Neo\Modules\Properties\Services\PlaceExchange;

use Carbon\Carbon;
use GuzzleHttp\Exception\GuzzleException;
use Illuminate\Support\LazyCollection;
use Neo\Modules\Properties\Enums\MediaType;
use Neo\Modules\Properties\Enums\PriceType;
use Neo\Modules\Properties\Enums\ProductType;
use Neo\Modules\Properties\Models\InventoryProvider;
use Neo\Modules\Properties\Services\Exceptions\IncompatibleResourceAndInventoryException;
use Neo\Modules\Properties\Services\Exceptions\IncompleteResourceException;
use Neo\Modules\Properties\Services\Exceptions\RequestException;
use Neo\Modules\Properties\Services\InventoryAdapter;
use Neo\Modules\Properties\Services\InventoryCapability;
use Neo\Modules\Properties\Services\InventoryConfig;
use Neo\Modules\Properties\Services\PlaceExchange\Models\AdUnit;
use Neo\Modules\Properties\Services\PlaceExchange\Models\Attributes\AdUnitAsset;
use Neo\Modules\Properties\Services\PlaceExchange\Models\Attributes\AdUnitAssetCapabilities;
use Neo\Modules\Properties\Services\PlaceExchange\Models\Attributes\AdUnitAuction;
use Neo\Modules\Properties\Services\PlaceExchange\Models\Attributes\AdUnitLocation;
use Neo\Modules\Properties\Services\PlaceExchange\Models\Attributes\AdUnitMeasurement;
use Neo\Modules\Properties\Services\PlaceExchange\Models\Attributes\AdUnitPlanning;
use Neo\Modules\Properties\Services\PlaceExchange\Models\Attributes\AdUnitRestrictions;
use Neo\Modules\Properties\Services\PlaceExchange\Models\Attributes\AdUnitSlot;
use Neo\Modules\Properties\Services\PlaceExchange\Models\Attributes\AdUnitStatus;
use Neo\Modules\Properties\Services\PlaceExchange\Models\Attributes\AdUnitVenue;
use Neo\Modules\Properties\Services\Resources\BroadcastLocation;
use Neo\Modules\Properties\Services\Resources\BroadcastPlayer;
use Neo\Modules\Properties\Services\Resources\DayOperatingHours;
use Neo\Modules\Properties\Services\Resources\Enums\InventoryResourceType;
use Neo\Modules\Properties\Services\Resources\IdentifiableProduct;
use Neo\Modules\Properties\Services\Resources\InventoryResourceId;
use Neo\Modules\Properties\Services\Resources\ProductResource;
use Neo\Services\API\APIAuthenticationError;
use RuntimeException;
use Traversable;

/**
 * @extends InventoryAdapter<PlaceExchangeConfig>
 */
class PlaceExchangeAdapter extends InventoryAdapter {
    protected array $capabilities = [
        InventoryCapability::ProductsRead,
        InventoryCapability::ProductsWrite,
        InventoryCapability::ProductsScreenSize,
        InventoryCapability::PropertiesType,
    ];

    /**
     * @inheritDoc
     */
    public static function buildConfig(InventoryProvider $provider): InventoryConfig {
        return new PlaceExchangeConfig(
            name         : $provider->name,
            inventoryID  : $provider->id,
            inventoryUUID: $provider->uuid,
            api_url      : $provider->settings->api_url,
            api_username : $provider->settings->api_username,
            api_key      : $provider->settings->api_key,
            org_id       : $provider->settings->client_id,
        );
    }

    /**
     * @return bool|string
     * @throws GuzzleException
     */
    public function validateConfiguration(): bool|string {
        try {
            return $this->getConfig()->getClient()->login();
        } catch (APIAuthenticationError $e) {
            return $e->getMessage();
        }
    }

    /**
     * @param Carbon|null $ifModifiedSince
     * @return Traversable
     */
    public function listProducts(?Carbon $ifModifiedSince = null): Traversable {
        return LazyCollection::make(function () {
            $adUnits = AdUnit::all($this->getConfig()->getClient());

            /** @var AdUnit $adUnit */
            foreach ($adUnits as $adUnit) {
                yield ResourceFactory::makeIdentifiableProduct($adUnit, $this->getConfig());
            }
        });
    }

    public function getProduct(InventoryResourceId $productId): IdentifiableProduct {
        $adUnitId = array_values($productId->context["units"] ?? [])[0]["id"] ?? null;

        if (!$adUnitId) {
            throw new RuntimeException("Product has invalid context: No adUnit id could be found");
        }

        $adUnit = AdUnit::find($this->getConfig()->getClient(), $adUnitId);
        return ResourceFactory::makeIdentifiableProduct($adUnit, $this->getConfig());
    }

    protected function fillAdUnit(AdUnit $adUnit, BroadcastPlayer $player, ProductResource $product, array $context, float $impressionsShare): void {
        [$wratio, $hratio] = aspect_ratio($product->screen_width_px / $product->screen_height_px);
        $adUnit->ad_formats    = [[
                                      "w" => $product->screen_width_px,
                                      "h" => $product->screen_height_px,
                                  ]];
        $adUnit->aspect_ratios = [[
                                      "wratio" => $wratio,
                                      "hratio" => $hratio,
                                  ]];

        $name = str_replace(" ", "_", trim($player->name));

        $adUnit->asset            = new AdUnitAsset(
            aspect_ratio: $wratio . ":" . $hratio,
            capability  : new AdUnitAssetCapabilities(
                              audio : $product->allows_audio,
                              banner: in_array(MediaType::Image, $product->allowed_media_types),
                              video : in_array(MediaType::Video, $product->allowed_media_types),
                          ),
            category_id : $adUnit->asset->category ?? null,
            image_url   : $adUnit->asset->image_url ?? "",
            mimes       : $adUnit->asset->mimes ?? [],
            name        : $name,
            screen_count: $player->screen_count,
            size        : $product->screen_size_in ?? 5,
            type        : "digital",
        );
        $adUnit->auction          = new AdUnitAuction(
            at         : 1,
            bidfloor   : $product->price,
            bidfloorcur: "USD",
        );
        $adUnit->eids             = []; // TODO: Fill in BroadSign ID
        $adUnit->integration_type = 3;
        $adUnit->keywords         = $adUnit->keywords ?? [];
        $adUnit->location         = $adUnit->location ?? AdUnitLocation::from([
                                                                                  "lat"                 => $product->geolocation->latitude,
                                                                                  "lon"                 => $product->geolocation->longitude,
                                                                                  "horizontal_accuracy" => 0,
                                                                                  "dma_code"            => null,
                                                                              ]);
        $adUnit->location->lat    = $product->geolocation->latitude;
        $adUnit->location->lon    = $product->geolocation->longitude;

        $impressionsPerPlay = collect($product->weekdays_spot_impressions)->sum() / 7;
        $impressionsPerWeek = collect($product->weekdays_spot_impressions)
            ->map(function ($playImpressions, $weekday) use ($product) {
                /** @var DayOperatingHours $openLength */
                $openLength = $product->operating_hours[$weekday - 1];
                $loopPerDay = $openLength->open_length_min / ($product->loop_configuration->loop_length_ms / 60_000);
                return $loopPerDay * $playImpressions;
            })->sum();

        $adUnit->measurement     = new AdUnitMeasurement(
            duration     : $product->loop_configuration->spot_length_ms / 1_000, // ms to s
            imp_four_week: max(1, $impressionsPerWeek * 4 * $impressionsShare),
            imp_x        : max(1, $impressionsPerPlay * $impressionsShare),
            provider     : "",
        );
        $adUnit->name            = $name;
        $adUnit->network_id      = $context["network_id"];
        $adUnit->placements      = $adUnit->placements ?? [];
        $adUnit->planning        = new AdUnitPlanning(
            base_rate: $product->price,
            rate_cur : "USD",
        );
        $adUnit->private_auction = 0; // Not private
        $adUnit->restrictions    = $adUnit->restrictions ?? new AdUnitRestrictions();
        $adUnit->slot            = new AdUnitSlot(
            h           : $product->screen_height_px,
            max_duration: $product->loop_configuration->spot_length_ms / 1_000,
            min_duration: min(5, $product->loop_configuration->spot_length_ms / 1_000),
            w           : $product->screen_width_px,
        );
        $adUnit->start_date      = null;
        $adUnit->status          = $product->is_sellable ? AdUnitStatus::Live : AdUnitStatus::Decommissioned;
        $adUnit->venue           = new AdUnitVenue(
            address         : $product->address->full,
            name            : $product->property_name,
            openooh_category: $product->property_type?->external_id,
        );
    }

    /**
     * @param ProductResource $product
     * @param array           $context
     * @return InventoryResourceId|null
     * @throws APIAuthenticationError
     * @throws GuzzleException
     * @throws IncompatibleResourceAndInventoryException
     * @throws IncompleteResourceException
     * @throws RequestException
     */
    public function createProduct(ProductResource $product, array $context): InventoryResourceId|null {
        // First, validate the product is compatible with Reach
        if ($product->type !== ProductType::Digital || $product->price_type !== PriceType::CPM) {
            throw new IncompatibleResourceAndInventoryException(0, $this->getInventoryID(), $this->getInventoryType());
        }

        if (!$product->property_type) {
            throw new IncompleteResourceException($product->product_connect_id ?? 0, "property_type", $this->getInventoryID(), $this->getInventoryType());
        }

        /** @var PlaceExchangeClient $client */
        $client = $this->getConfig()->getClient();

        $adUnitsIds = [];

        $screensCount = collect($product->broadcastLocations)->sum("screen_count");

        /** @var BroadcastLocation $broadcastLocation */
        foreach ($product->broadcastLocations as $broadcastLocation) {
            /** @var BroadcastPlayer $broadcastPlayer */
            foreach ($broadcastLocation->players as $broadcastPlayer) {
                $impressionsShare = $broadcastPlayer->screen_count / $screensCount;

                $adUnit         = new AdUnit($client);
                $adUnit->name   = str_replace(" ", "_", trim($broadcastPlayer->name));
                $adUnit->slot   = new AdUnitSlot(
                    h           : $product->screen_height_px,
                    max_duration: $product->loop_configuration->spot_length_ms / 1_000,
                    min_duration: min(5, $product->loop_configuration->spot_length_ms / 1_000),
                    w           : $product->screen_width_px,
                );
                $adUnit->status = $product->is_sellable ? AdUnitStatus::Live : AdUnitStatus::Decommissioned;
                $adUnit->create();

                try {
                    $this->fillAdUnit($adUnit, $broadcastPlayer, $product, $context, $impressionsShare);
                    $adUnit->save();
                } catch (RequestException $e) {
                    // In case of an error when filling the ad unit, delete it. Otherwise, the incomplete unit would be left dangling
                    $adUnit->delete();
                    throw $e;
                }

                $adUnitsIds[$broadcastPlayer->id] = ["id" => $adUnit->getKey(), "name" => $adUnit->name];
            }
        }

        return new InventoryResourceId(
            inventory_id: $this->getInventoryID(),
            external_id : 'MULTIPLE',
            type        : InventoryResourceType::Product,
            context     : [
                              ...$context,
                              "units" => $adUnitsIds,
                          ]
        );
    }

    /**
     * @param InventoryResourceId $productId
     * @param ProductResource     $product
     * @return InventoryResourceId|false
     * @throws APIAuthenticationError
     * @throws GuzzleException
     * @throws IncompleteResourceException
     * @throws RequestException
     */
    public function updateProduct(InventoryResourceId $productId, ProductResource $product): InventoryResourceId|false {
        if (!$product->property_type) {
            throw new IncompleteResourceException($product->product_connect_id ?? 0, "property_type", $this->getInventoryID(), $this->getInventoryType());
        }

        $client = $this->getConfig()->getClient();

        $adUnitsIds = [];

        $screensCount = collect($product->broadcastLocations)->sum("screen_count");
        // For each player, we pull the screen to update it. If the screen does not exist, we create it and update our id/context
        foreach ($product->broadcastLocations as $broadcastLocation) {
            /** @var BroadcastPlayer $broadcastPlayer */
            foreach ($broadcastLocation->players as $broadcastPlayer) {
                $impressionsShare = $broadcastPlayer->screen_count / $screensCount;

                if (!isset($productId->context["units"][$broadcastPlayer->id])) {
                    // No ID for this location
                    $adUnit = new AdUnit($client);
                } else {
                    $adUnit = AdUnit::find($client, $productId->context["units"][$broadcastPlayer->id]["id"]);
                }

                $unitName = $adUnit->name;

                $this->fillAdUnit($adUnit, $broadcastPlayer, $product, $productId->context, $impressionsShare);
                $adUnit->save($unitName);

                $adUnitsIds[$broadcastPlayer->id] = ["id" => $adUnit->getKey(), "name" => $adUnit->name];
            }
        }

        // We now want to compare the list of screens we just built against the one we were given.
        // Any screen listed in the latter but missing in the former will have to be removed
        $adUnitsToRemove = array_diff(collect($productId->context["units"])
                                          ->pluck("id")
                                          ->values()
                                          ->all(),
                                      collect($adUnitsIds)
                                          ->pluck("id")
                                          ->values()
                                          ->all());

        foreach ($adUnitsToRemove as $adUnitToRemove) {
            $adUnit = new AdUnit($client);
            $adUnit->setKey($adUnitToRemove);
            $adUnit->delete();
        }

        $productId->context["units"] = $adUnitsIds;

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
        foreach ($productId->context["units"] as ["id" => $adUnitId]) {

            $adUnit         = AdUnit::find($client, $adUnitId);
            $adUnitKey      = $adUnit->name;
            $adUnit->name   .= "_disabled-" . time();
            $adUnit->status = AdUnitStatus::Decommissioned;
            $adUnit->save($adUnitKey);
        }

        return true;
    }
}
