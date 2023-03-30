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
use Neo\Modules\Properties\Services\Resources\DayOperatingHours;
use Neo\Modules\Properties\Services\Resources\IdentifiableProduct;
use Neo\Modules\Properties\Services\Resources\InventoryResourceId;
use Neo\Modules\Properties\Services\Resources\ProductResource;
use Neo\Modules\Properties\Services\Resources\PropertyResource;
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

        return LazyCollection::make(function () use ($ifModifiedSince, $client) {
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
     * @throws GuzzleException
     * @throws RequestException
     */
    public function getProduct(InventoryResourceId $productId): IdentifiableProduct {
        $product = Unit::find($this->getConfig()->getClient(), $productId->external_id);

        return ResourceFactory::makeIdentifiableProduct($product, $this->config);
    }

    /**
     * Transforms operating hours object to Hivestack active hours string format
     *
     * @param Enumerable $operatingHours
     * @return string
     */
    protected function operatingHoursToHivestackString(Enumerable $operatingHours) {
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

    protected function fillSite(Site $site, ProductResource $product) {
        $site->name        = $product->property_name;
        $site->description = $product->property_name;
        $site->longitude   = $product->geolocation->longitude;
        $site->latitude    = $product->geolocation->latitude;
        $site->external_id = (string)$product->property_connect_id;
    }

    protected function fillUnit(Unit $unit, ProductResource $product, array $context) {
        $unit->name            = $product->name[0]->value;
        $unit->description     = $product->name[0]->value;
        $unit->network_id      = $context["network_id"];
        $unit->external_id     = (string)$product->product_connect_id;
        $unit->floor_cpm       = $product->price;
        $unit->longitude       = $product->geolocation->longitude;
        $unit->latitude        = $product->geolocation->latitude;
        $unit->loop_length     = $product->loop_configuration->loop_length_ms / 1_000; // ms to seconds
        $unit->operating_hours = $this->operatingHoursToHivestackString($product->operating_hours->toCollection());
        $unit->screen_height   = $product->screen_height_px;
        $unit->screen_width    = $product->screen_width_px;
        $unit->spot_length     = $product->loop_configuration->spot_length_ms / 1_000; // ms to seconds
        $unit->min_spot_length = min($unit->spot_length, 5);                           // Min length : 5 seconds or spot length if shorter
        $unit->max_spot_length = $unit->spot_length;
        $unit->timezone        = $product->timezone;
        $unit->allow_image     = in_array(MediaType::Image, $product->allowed_media_types);
        $unit->allow_video     = in_array(MediaType::Video, $product->allowed_media_types);
        $unit->allow_html      = in_array(MediaType::HTML, $product->allowed_media_types);
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
            $site         = new Site($client);
            $site->active = true;
            $this->fillSite($site, $product);
            $site->save();

            $siteId = $site->getKey();
        }

        // Create and fill the unit
        $unit         = new Unit($client);
        $unit->active = true;
        $this->fillUnit($unit, $product, $context);
        $unit->site_id = $siteId;
        $unit->save();

        return $unit->toInventoryResourceId($this->getInventoryID());
    }

    /**
     * @inheritDoc
     * @throws GuzzleException
     * @throws RequestException
     */
    public function updateProduct(InventoryResourceId $productId, ProductResource $product): bool {
        $client = $this->getConfig()->getClient();

        // Hivestack has support for both products and properties, we therefore need to update both
        $unit = Unit::find($client, (int)$productId->external_id);
        $site = Site::find($client, $unit->site_id);

        $this->fillSite($site, $product);
        $site->save();

        $this->fillUnit($unit, $product, $productId->context);
        $unit->save();

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
