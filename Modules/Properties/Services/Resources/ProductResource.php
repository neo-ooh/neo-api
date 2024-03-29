<?php
/*
 * Copyright 2023 (c) Neo-OOH - All Rights Reserved
 * Unauthorized copying of this file, via any medium is strictly prohibited
 * Proprietary and confidential
 * Written by Valentin Dufois <vdufois@neo-ooh.com>
 *
 * @neo/api - ProductResource.php
 */

namespace Neo\Modules\Properties\Services\Resources;

use Neo\Modules\Properties\Enums\MediaType;
use Neo\Modules\Properties\Enums\PriceType;
use Neo\Modules\Properties\Enums\ProductType;
use Spatie\LaravelData\Attributes\DataCollectionOf;
use Spatie\LaravelData\DataCollection;

class ProductResource extends InventoryResource {
    public function __construct(
        /**
         * @var DataCollection<LocalizedString> The product name, localized
         */
        #[DataCollectionOf(LocalizedString::class)]
        public DataCollection           $name,

        /**
         * @var ProductType The product's type
         */
        public ProductType              $type,

        /**
         * The category to which this product belong, if applicable
         *
         * @var InventoryResourceId|null
         */
        public InventoryResourceId|null $category_id,

        /**
         * @var bool Tell if the product is sellable
         */
        public bool                     $is_sellable,

        /**
         * @var bool Tell if the product is a bonus product.
         */
        public bool                     $is_bonus,

        /**
         * @var InventoryResourceId|null ID of another product linked with this one
         */
        public InventoryResourceId|null $linked_product_id,

        /**
         * @var int Screen count for digital products, frames count otherwise
         */
        public int                      $quantity,

        /**
         * @var PriceType How the product is priced
         */
        public PriceType                $price_type,

        /**
         * @var float The product's price
         */
        public float                    $price,

        /**
         * @var float The product's price for programmatic platforms, always as CPM
         */
        public float                    $programmatic_price,

        /**
         * @var string Fully qualified URL for the product's picture
         */
        public string|null              $picture_url,

        /**
         * @var LoopConfiguration|null For digital product, their loop configuration
         */
        public LoopConfiguration|null   $loop_configuration,

        /**
         * @var int Screen width in pixels
         */
        public int                      $screen_width_px,

        /**
         * @var int Screen height in pixels
         */
        public int                      $screen_height_px,

        /**
         * @var float Screen diagonal size in inches
         */
        public float|null               $screen_size_in,

        /**
         * @var InventoryResourceId|null External Id of the screen type
         */
        public InventoryResourceId|null $screen_type,

        /**
         * @var MediaType[]
         */
        public array                    $allowed_media_types,

        /**
         * @var bool Does the product allow files with audio
         */
        public bool                     $allows_audio,

        /**
         * @var bool Does the product allow motion
         */
        public bool                     $allows_motion,

        /**
         * @var InventoryResourceId|null ID of the property in which the product is, if supported
         */
        public InventoryResourceId|null $property_id,

        /**
         * @var string The product property's name
         */
        public string                   $property_name,

        /**
         * @var InventoryResourceId|null External ID of the property type, if available
         */
        public InventoryResourceId|null $property_type,

        /**
         * @var Address The property location
         */
        public Address|null             $address,

        /**
         * @var Geolocation|null The property lng and lat
         */
        public Geolocation|null         $geolocation,

        /**
         * @var string The property timezone
         */
        public string|null              $timezone,

        /**
         * @var DataCollection<DayOperatingHours> Ordered (Monday to Sunday) product's operating hours
         */
        #[DataCollectionOf(DayOperatingHours::class)]
        public DataCollection|null      $operating_hours,

        /**
         * @var int The property's average weekly traffic
         */
        public int                      $weekly_traffic,

        /**
         * @var array<int, float>|null 1-indexed amount of impressions generated on each play for each day of the week
         */
        public array|null               $weekdays_spot_impressions,

        // -- Players ?
        // -- Media types

        // Connect's ids
        /**
         * @var int|null ID of the product in Connect
         */
        public int|null                 $product_connect_id = null,

        /**
         * @var int|null ID of the property in Connect
         */
        public int|null                 $property_connect_id = null,

        // Broadcast location
        /**
         * @var BroadcastLocation[]|null List of broadcast locations associated with the product. Only relevant for `ProductResource`s coming from Connect
         */
        public array|null               $broadcastLocations = null,

        /**
         * @var InventoryResourceId[]|null
         */
        public array|null               $externalRepresentations = null,
    ) {

    }
}
