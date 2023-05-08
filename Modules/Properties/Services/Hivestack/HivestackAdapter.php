<?php
/*
 * Copyright 2023 (c) Neo-OOH - All Rights Reserved
 * Unauthorized copying of this file, via any medium is strictly prohibited
 * Proprietary and confidential
 * Written by Valentin Dufois <vdufois@neo-ooh.com>
 *
 * @neo/api - HivestackAdapter.php
 */

namespace Neo\Modules\Properties\Services\Hivestack;

use Carbon\Carbon;
use GuzzleHttp\Exception\GuzzleException;
use Illuminate\Http\Client\RequestException;
use Illuminate\Support\Enumerable;
use Illuminate\Support\LazyCollection;
use Neo\Modules\Properties\Enums\MediaType;
use Neo\Modules\Properties\Enums\PriceType;
use Neo\Modules\Properties\Enums\ProductType;
use Neo\Modules\Properties\Models\InventoryProvider;
use Neo\Modules\Properties\Services\Exceptions\IncompatibleResourceAndInventoryException;
use Neo\Modules\Properties\Services\Hivestack\API\HivestackClient;
use Neo\Modules\Properties\Services\Hivestack\Models\Site;
use Neo\Modules\Properties\Services\Hivestack\Models\Unit;
use Neo\Modules\Properties\Services\InventoryAdapter;
use Neo\Modules\Properties\Services\InventoryCapability;
use Neo\Modules\Properties\Services\InventoryConfig;
use Neo\Modules\Properties\Services\Resources\BroadcastLocation;
use Neo\Modules\Properties\Services\Resources\DayOperatingHours;
use Neo\Modules\Properties\Services\Resources\Enums\InventoryResourceType;
use Neo\Modules\Properties\Services\Resources\IdentifiableProduct;
use Neo\Modules\Properties\Services\Resources\InventoryResourceId;
use Neo\Modules\Properties\Services\Resources\ProductResource;
use Neo\Modules\Properties\Services\Resources\PropertyResource;
use RuntimeException;
use Traversable;

/**
 * @extends InventoryAdapter<HivestackConfig>
 */
class HivestackAdapter extends InventoryAdapter {

    protected array $capabilities = [
        InventoryCapability::ProductsRead,
        InventoryCapability::ProductsWrite,
        InventoryCapability::PropertiesRead,
        InventoryCapability::ProductsMediaTypes,
    ];

    /**
     * @inheritDoc
     */
    public static function buildConfig(InventoryProvider $provider): InventoryConfig {
        return new HivestackConfig(
            name         : $provider->name,
            inventoryID  : $provider->id,
            inventoryUUID: $provider->uuid,
            api_url      : $provider->settings->api_url,
            api_key      : $provider->settings->api_key
        );
    }

    /**
     * @inheritDoc
     */
    public function listProducts(?Carbon $ifModifiedSince = null): Traversable {
        $client = $this->getConfig()->getClient();

        return LazyCollection::make(function () use ($client) {
            $pageSize = 100;
            $cursor   = 0;

            do {
                $units = Unit::all(
                    client: $client,
                    limit : $pageSize,
                    offset: $cursor,
                );

                foreach ($units as $unit) {
                    yield ResourceFactory::makeIdentifiableProduct($unit, $this->getConfig());
                }

                $cursor += $pageSize;
            } while ($units->count() === $pageSize);
        });
    }

    /**
     * @inheritDoc
     * @param InventoryResourceId $productId
     * @return IdentifiableProduct
     */
    public function getProduct(InventoryResourceId $productId): IdentifiableProduct {
        $unitID = array_values($productId->context["units"] ?? [])[0] ?? null;

        if (!$unitID) {
            throw new RuntimeException("Product has invalid context: No unit id could be found");
        }

        $unit = Unit::find($this->getConfig()->getClient(), $unitID);
        return ResourceFactory::makeIdentifiableProduct($unit, $this->getConfig());
    }

    /**
     * Transforms operating hours object to Hivestack active hours string format
     *
     * @param Enumerable $operatingHours
     * @return string
     */
    protected function operatingHoursToHivestackString(Enumerable $operatingHours): string {
        $hoursString = "";
        for ($i = 1; $i <= 7; $i++) {
            /** @var DayOperatingHours $dayHours */
            $dayHours  = $operatingHours->firstWhere("day", "===", $i);
            $startHour = ($dayHours ? Carbon::createFromTimeString($dayHours->start_at) : Carbon::createFromTime())->hour;
            $endHour   = ($dayHours ? Carbon::createFromTimeString($dayHours->end_at) : Carbon::createFromTime())->hour;
            for ($h = 0; $h < 24; $h++) {
                $hoursString .= ($h >= $startHour && $h <= $endHour) ? "1" : "0";
            }
        }

        return $hoursString;
    }

    protected function fillSite(Site $site, ProductResource $product): void {
        $site->active      = true;
        $site->name        = trim($product->property_name);
        $site->description = trim($product->property_name);
        $site->longitude   = $product->geolocation->longitude;
        $site->latitude    = $product->geolocation->latitude;
        $site->external_id = "connect:" . $product->property_connect_id . " - " . $product->property_name;
    }

    protected function fillUnit(Unit $unit, BroadcastLocation $location, ProductResource $product, array $context): void {
        $unit->active                         = $product->is_sellable;
        $unit->name                           = trim($product->property_name) . " - " . trim($product->name[0]->value);
        $unit->description                    = $location->name;
        $unit->network_id                     = $context["network_id"];
        $unit->external_id                    = $location->external_id->external_id . "-" . $location->id;
        $unit->floor_cpm                      = $product->price;
        $unit->longitude                      = $product->geolocation->longitude;
        $unit->latitude                       = $product->geolocation->latitude;
        $unit->loop_length                    = $product->loop_configuration->loop_length_ms / 1_000; // ms to seconds
        $unit->operating_hours                = $this->operatingHoursToHivestackString($product->operating_hours->toCollection());
        $unit->screen_height                  = $product->screen_height_px;
        $unit->screen_width                   = $product->screen_width_px;
        $unit->spot_length                    = $product->loop_configuration->spot_length_ms / 1_000; // ms to seconds
        $unit->min_spot_length                = min($unit->spot_length, 5);                           // Min length : 5 seconds or spot length if shorter
        $unit->max_spot_length                = $unit->spot_length;
        $unit->timezone                       = $product->timezone;
        $unit->allow_image                    = in_array(MediaType::Image, $product->allowed_media_types);
        $unit->allow_video                    = in_array(MediaType::Video, $product->allowed_media_types);
        $unit->allow_html                     = in_array(MediaType::HTML, $product->allowed_media_types);
        $unit->enable_strict_iab_blacklisting = true;
        $unit->weekly_traffic                 = $product->weekly_traffic;
        $unit->physical_unit_count            = $location->screen_count;
    }

    /**
     * @inheritDoc
     * @throws RequestException
     * @throws GuzzleException
     * @throws IncompatibleResourceAndInventoryException
     */
    public function createProduct(ProductResource $product, array $context): InventoryResourceId|null {
        // First, validate this product is compatible with hivestack
        if ($product->type !== ProductType::Digital || $product->price_type !== PriceType::CPM || $product->is_bonus) {
            throw new IncompatibleResourceAndInventoryException(0, $this->getInventoryID(), $this->getInventoryType());
        }

        /** @var HivestackClient $client */
        $client = $this->getConfig()->getClient();
        // Hivestack has support for property, so we need to validate that first.
        // If the product resource come with a unit ID, we're good, otherwise we'll have to create it
        $siteId = (int)$product->property_id?->external_id;

        if (!$siteId) {
            // Let's create the property
            $site = new Site($client);
            $this->fillSite($site, $product);
            $site->save();

            $siteId = $site->getKey();
        }

        // We create a unit for each broadcast location of the product
        $unitIds = [];

        foreach ($product->broadcastLocations as $broadcastLocation) {
            $unit = new Unit($client);
            $this->fillUnit($unit, $broadcastLocation, $product, $context);
            $unit->site_id = $siteId;
            $unit->save();

//            Filling impressions through the API is not for users, lol
//            $unit->fillImpressions($product->weekdays_spot_impressions);

            $unitIds[$broadcastLocation->id] = ["id" => $unit->getKey(), "name" => $unit->name];
        }

        return new InventoryResourceId(
            inventory_id: $this->getInventoryID(),
            external_id : 'MULTIPLE',
            type        : InventoryResourceType::Product,
            context     : [
                              "network_id" => $context["network_id"],
                              "units"      => $unitIds,
                          ]
        );
    }

    /**
     * @inheritDoc
     * @throws GuzzleException
     * @throws RequestException
     */
    public function updateProduct(InventoryResourceId $productId, ProductResource $product): InventoryResourceId|false {
        $client = $this->getConfig()->getClient();

        // Start by updating the site
        $site = Site::find($client, $product->property_id->external_id) ?? new Site($client);
        $this->fillSite($site, $product);
        $site->save();

        $unitIds = [];

        // For each broadcast location, we pull the unit to update it. If the unit does not exist, we create it and update our id/context
        foreach ($product->broadcastLocations as $broadcastLocation) {
            if (!isset($productId->context["units"][$broadcastLocation->id]["id"])) {
                // No ID for this location
                $unit = new Unit($client);
            } else {
                $unit = Unit::find($client, $productId->context["units"][$broadcastLocation->id]["id"]);
            }

            $this->fillUnit($unit, $broadcastLocation, $product, $productId->context);
            $unit->site_id = $site->getKey();
            $unit->save();
//            Filling impressions through the API is not for users, lol
//            $unit->fillImpressions($product->weekdays_spot_impressions);

            $unitIds[$broadcastLocation->id] = ["id" => $unit->getKey(), "name" => $unit->name];
        }

        // We now want to compare the list of units we just built against the one we were given.
        // Any unit listed in the latter but missing in the former will have to be removed
        $unitsToRemove = array_diff(collect($productId->context["units"])
                                        ->map(fn(array $unit) => $unit["id"])
                                        ->values(),
                                    collect($unitIds)
                                        ->map(fn(array $unit) => $unit["id"])
                                        ->values()
                                        ->all());
        foreach ($unitsToRemove as $unitToRemove) {
            $unit = new Unit($client);
            $unit->setKey($unitToRemove);
            $unit->delete();
        }

        $productId->context["units"] = $unitIds;

        return $productId;
    }

    /**
     * @throws RequestException
     * @throws GuzzleException
     */
    public function removeProduct(InventoryResourceId $productId): bool {
        $client = $this->getConfig()->getClient();

        // We have to remove all the units listed in the product's context
        foreach ($productId->context["units"] as ["id" => $unitId]) {
            $unit = new Unit($client);
            $unit->setKey($unitId);
            $unit->delete();
        }

        return true;
    }

    /**
     * @inheritDoc
     */
    public function listProperties(?Carbon $ifModifiedSince = null): Traversable {
        $client = $this->getConfig()->getClient();

        return LazyCollection::make(function () use ($ifModifiedSince, $client) {
            $pageSize = 100;
            $cursor   = 0;

            do {
                $sites = Site::all(
                    client: $client,
                    limit : $pageSize,
                    offset: $cursor,
                );

                foreach ($sites as $site) {
                    yield ResourceFactory::makeIdentifiableProperty($site, $this->getConfig());
                }

                $cursor += $pageSize;
            } while ($sites->count() === $pageSize);
        });
    }

    /**
     * @inheritDoc
     */
    public function getProperty(InventoryResourceId $property): PropertyResource {
        $site = Site::find($this->getConfig()->getClient(), (int)$property->external_id);
        return ResourceFactory::makeIdentifiableProperty($site, $this->getConfig());
    }
}
