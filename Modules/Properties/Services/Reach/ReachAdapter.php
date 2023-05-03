<?php
/*
 * Copyright 2023 (c) Neo-OOH - All Rights Reserved
 * Unauthorized copying of this file, via any medium is strictly prohibited
 * Proprietary and confidential
 * Written by Valentin Dufois <vdufois@neo-ooh.com>
 *
 * @neo/api - ReachAdapter.php
 */

namespace Neo\Modules\Properties\Services\Reach;

use Carbon\Carbon;
use GuzzleHttp\Exception\GuzzleException;
use Illuminate\Http\Client\RequestException;
use Illuminate\Support\Collection;
use Neo\Modules\Properties\Enums\MediaType;
use Neo\Modules\Properties\Enums\PriceType;
use Neo\Modules\Properties\Enums\ProductType;
use Neo\Modules\Properties\Models\InventoryProvider;
use Neo\Modules\Properties\Services\Exceptions\IncompatibleResourceAndInventoryException;
use Neo\Modules\Properties\Services\InventoryAdapter;
use Neo\Modules\Properties\Services\InventoryCapability;
use Neo\Modules\Properties\Services\InventoryConfig;
use Neo\Modules\Properties\Services\Reach\Models\AspectRatio;
use Neo\Modules\Properties\Services\Reach\Models\Attributes\NamedIdentityAttribute;
use Neo\Modules\Properties\Services\Reach\Models\Attributes\ScreenAspectRatio;
use Neo\Modules\Properties\Services\Reach\Models\Attributes\ScreenBidFloor;
use Neo\Modules\Properties\Services\Reach\Models\Attributes\ScreenCurrency;
use Neo\Modules\Properties\Services\Reach\Models\Attributes\ScreenPublisher;
use Neo\Modules\Properties\Services\Reach\Models\Attributes\ScreenResolution;
use Neo\Modules\Properties\Services\Reach\Models\Attributes\ScreenVenueType;
use Neo\Modules\Properties\Services\Reach\Models\Resolution;
use Neo\Modules\Properties\Services\Reach\Models\Screen;
use Neo\Modules\Properties\Services\Resources\BroadcastLocation;
use Neo\Modules\Properties\Services\Resources\Enums\InventoryResourceType;
use Neo\Modules\Properties\Services\Resources\IdentifiableProduct;
use Neo\Modules\Properties\Services\Resources\InventoryResourceId;
use Neo\Modules\Properties\Services\Resources\ProductResource;
use RuntimeException;
use Traversable;

/**
 * @extends InventoryAdapter<ReachConfig>
 */
class ReachAdapter extends InventoryAdapter {
    protected array $capabilities = [
        InventoryCapability::ProductsRead,
        InventoryCapability::ProductsWrite,
        InventoryCapability::ProductsAudioSupport,
    ];

    /**
     * @inheritDoc
     */
    public static function buildConfig(InventoryProvider $provider): InventoryConfig {
        return new ReachConfig(
            name         : $provider->name,
            inventoryID  : $provider->id,
            inventoryUUID: $provider->uuid,
            auth_url     : $provider->settings->auth_url,
            api_url      : $provider->settings->api_url,
            api_username : $provider->settings->api_username,
            api_key      : $provider->settings->api_key,
            publisher_id : $provider->settings->publisher_id,
            client_id    : $provider->settings->client_id,
        );
    }

    /**
     * @param Carbon|null $ifModifiedSince
     * @return Traversable
     */
    public function listProducts(Carbon|null $ifModifiedSince = null): Traversable {
        return Screen::all($this->getConfig()->getClient(), $ifModifiedSince, 100)
                     ->map(fn(Screen $screen) => ResourceFactory::makeIdentifiableProduct($screen, $this->getConfig()));
    }

    public function getProduct(InventoryResourceId $productId): IdentifiableProduct {
        $screenId = array_values($productId->context["screens"] ?? [])[0] ?? null;

        if (!$screenId) {
            throw new RuntimeException("Product has invalid context: No screen id could be found");
        }

        $screen = Screen::find($this->getConfig()->getClient(), $screenId);
        return ResourceFactory::makeIdentifiableProduct($screen, $this->getConfig());
    }

    protected function findResolution(int $width, int $height): Resolution {
        return $this->getCache()->remember("resolution-$width-$height", null,
            /**
             * @throws RequestException
             * @throws GuzzleException
             */
            function () use ($height, $width) {
                /** @var Resolution $resolution */
                foreach (Resolution::all($this->getConfig()->getClient()) as $resolution) {
                    dump("found $resolution->id: $resolution->width x $resolution->height");
                    if ($resolution->width === $width && $resolution->height === $height) {
                        return $resolution;
                    }
                }

                $resolution         = new Resolution($this->getConfig()->getClient());
                $resolution->name   = $width . "x" . $height;
                $resolution->width  = $width;
                $resolution->height = $height;
                $resolution->create();

                return $resolution;
            });
    }

    protected function findAspectRatio(int $width, int $height): AspectRatio {
        [$h, $v] = aspect_ratio($width / $height, 32);
        return $this->getCache()->remember("aspect-ratio-$h-$v", null,
            /**
             * @throws RequestException
             * @throws GuzzleException
             */
            function () use ($v, $h, $height, $width) {
                /** @var Resolution $aspectRatio */
                foreach (AspectRatio::all($this->getConfig()->getClient()) as $aspectRatio) {
                    if ($aspectRatio->horizontal === $h && $aspectRatio->vertical === $v) {
                        return $aspectRatio;
                    }
                }

                $aspectRatio             = new AspectRatio($this->getConfig()->getClient());
                $aspectRatio->name       = $width . ":" . $height;
                $aspectRatio->horizontal = $h;
                $aspectRatio->vertical   = $v;
                $aspectRatio->create();

                return $aspectRatio;
            });
    }

    protected function fillScreen(Screen $screen, BroadcastLocation $location, ProductResource $product, array $context): void {
        $resolution  = $this->findResolution($product->screen_width_px, $product->screen_height_px);
        $aspectRatio = $this->findAspectRatio($product->screen_width_px, $product->screen_height_px);

        $productScreensCount = collect($product->broadcastLocations)->sum("screen_count");
        $impressionsShare    = $location->screen_count / $productScreensCount;

        $screen->device_id             = $location->provider->value . ".com:" . $location->external_id->external_id;
        $screen->name                  = trim($product->property_name) . " - " . trim($product->name[0]->value);
        $screen->publisher             = ScreenPublisher::from(["id" => $this->config->publisher_id]);
        $screen->is_active             = $product->is_sellable;
        $screen->resolution            = ScreenResolution::from(["id" => $resolution->getKey()]);
        $screen->aspect_ratio          = ScreenAspectRatio::from(["id" => $aspectRatio->getKey()]);
        $screen->connectivity          = 1;
        $screen->is_audio              = false;
        $screen->tags                  = new Collection();
        $screen->longitude             = $product->geolocation->longitude;
        $screen->latitude              = $product->geolocation->latitude;
        $screen->address               = $product->address->full;
        $screen->venue_types           = collect($context["venue_type_id"])
            ->map(fn($venueTypeId) => ScreenVenueType::from(["id" => $venueTypeId]));
        $screen->demography_type       = "basic";
        $screen->total                 = 0;
        $screen->audience_data_sources = collect([
                                                     NamedIdentityAttribute::from(["id" => 4]),
                                                 ]);
        $screen->allowed_ad_types      = collect([
                                                     in_array(MediaType::Image, $product->allowed_media_types) ? NamedIdentityAttribute::from(["id" => 1]) : null,
                                                     in_array(MediaType::Video, $product->allowed_media_types) ? NamedIdentityAttribute::from(["id" => 2]) : null,
                                                     in_array(MediaType::Audio, $product->allowed_media_types) ? NamedIdentityAttribute::from(["id" => 3]) : null,
                                                     in_array(MediaType::HTML, $product->allowed_media_types) ? NamedIdentityAttribute::from(["id" => 4]) : null,
                                                 ])->where(null, "!==", null);
        $screen->allows_motion         = true;
        $screen->screen_img_url        = null;
        $screen->min_ad_duration       = min($product->loop_configuration->spot_length_ms / 1_000, 5);
        $screen->max_ad_duration       = $product->loop_configuration->spot_length_ms / 1_000;
//        Not taken into account in the API
//        $screen->floor_cpm                    = $product->price;

        $screen->bid_floors                   = collect([
                                                            new ScreenBidFloor(
                                                                floor   : $product->price,
                                                                currency: ScreenCurrency::from(["id" => 9]),
                                                            ),
                                                        ]);
        $screen->average_weekly_impressions   = collect($product->weekdays_spot_impressions)->sum() * ($product->loop_configuration->loop_length_ms / $product->loop_configuration->spot_length_ms) * $impressionsShare;
        $screen->bearing                      = null;
        $screen->internal_publisher_screen_id = "connect:" . $product->product_connect_id;
        $screen->ox_enabled                   = true;
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

        /** @var ReachClient $client */
        $client = $this->getConfig()->getClient();

        $screenIds = [];

        $screensCount = collect($product->broadcastLocations)->sum("screen_count");

        foreach ($product->broadcastLocations as $broadcastLocation) {
            $screen = new Screen($client);
            $this->fillScreen($screen, $broadcastLocation, $product, $context);
            $screen->save();

            if ($product->weekdays_spot_impressions) {
                $impressionsShare = $broadcastLocation->screen_count / $screensCount;
                $screen->fillImpressions(collect($product->weekdays_spot_impressions)
                                             ->map(fn($v) => $v * $impressionsShare)
                                             ->all());
            }

            $screenIds[$broadcastLocation->id] = $screen->getKey();
        }

        return new InventoryResourceId(
            inventory_id: $this->getInventoryID(),
            external_id : 'MULTIPLE',
            type        : InventoryResourceType::Product,
            context     : [
                              ...$context,
                              "screens" => $screenIds,
                          ]
        );
    }

    /**
     * @throws RequestException
     * @throws GuzzleException
     */
    public function updateProduct(InventoryResourceId $productId, ProductResource $product): InventoryResourceId|false {
        $client = $this->getConfig()->getClient();

        $screenIds = [];

        $screensCount = collect($product->broadcastLocations)->sum("screen_count");
        // For each screen ID in the context, we pull the screen to update it. If the screen does not exist, we create it and update our id/context
        foreach ($product->broadcastLocations as $broadcastLocation) {
            if (!isset($productId->context["screens"][$broadcastLocation->id])) {
                // No ID for this location
                $screen = new Screen($client);
            } else {
                $screen = Screen::find($client, $productId->context["screens"][$broadcastLocation->id]);
            }

            $this->fillScreen($screen, $broadcastLocation, $product, $productId->context);
            $screen->save();

            if ($product->weekdays_spot_impressions) {
                $impressionsShare = $broadcastLocation->screen_count / $screensCount;
                $screen->fillImpressions(collect($product->weekdays_spot_impressions)
                                             ->map(fn($v) => $v * $impressionsShare)
                                             ->all());
            }

            $screenIds[$broadcastLocation->id] = $screen->getKey();
        }

        // We now want to compare the list of screens we just built against the one we were given.
        // Any screen listed in the latter but missing in the former will have to be removed

        $screensToRemove = array_diff(array_values($productId->context["screens"]), array_values($screenIds));
        foreach ($screensToRemove as $screenToRemove) {
            $screen = new Screen($client);
            $screen->setKey($screenToRemove);
            $screen->delete();
        }

        $productId->context["screens"] = $screenIds;

        return $productId;
    }

    /**
     * @throws RequestException
     * @throws GuzzleException
     */
    public function removeProduct(InventoryResourceId $productId): bool {
        $client = $this->getConfig()->getClient();

        // We have to remove all the screens listed in the product's context
        foreach ($productId->context["screens"] as $screenId) {
            $screen = new Screen($client);
            $screen->setKey($screenId);
            $screen->delete();
        }

        return true;
    }
}