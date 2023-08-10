<?php
/*
 * Copyright 2023 (c) Neo-OOH - All Rights Reserved
 * Unauthorized copying of this file, via any medium is strictly prohibited
 * Proprietary and confidential
 * Written by Valentin Dufois <vdufois@neo-ooh.com>
 *
 * @neo/api - ResolvedProduct.php
 */

namespace Neo\Modules\Properties\Models;

use Carbon\Carbon;
use Illuminate\Database\Eloquent\Collection;
use Neo\Models\Traits\HasView;
use Neo\Modules\Broadcast\Models\Format;
use Neo\Modules\Broadcast\Models\Location;
use Neo\Modules\Broadcast\Models\LoopConfiguration;
use Neo\Modules\Properties\Enums\MediaType;

/**
 * @property int                           $id
 * @property int                           $inventory_resource_id
 * @property int                           $property_id
 * @property int|null                      $category_id
 * @property string                        $name_en
 * @property string                        $name_fr
 * @property int|null                      $format_id
 * @property int|null                      $site_type_id
 * @property int                           $quantity
 * @property boolean                       $is_sellable
 * @property double                        $unit_price
 * @property boolean                       $is_bonus
 * @property int|null                      $linked_product_id
 * @property MediaType[]                   $allowed_media_types
 * @property boolean|null                  $allows_audio
 * @property boolean                       $allows_motion
 * @property double|null                   $production_cost
 * @property double|null                   $programmatic_price
 * @property string                        $notes
 * @property double|null                   $screen_size_in
 * @property int|null                      $screen_type_id
 * @property Carbon                        $created_at
 * @property Carbon                        $updated_at
 * @property Carbon                        $deleted_at
 *
 * @property Property                      $property
 * @property ProductCategory               $category
 * @property Format                        $format
 * @property PropertyType                  $site_type
 * @property ProductWarnings               $warnings
 * @property Collection<ImpressionsModel>  $impressions_models
 * @property Collection<LoopConfiguration> $loop_configurations
 * @property Collection<Unavailability>    $unavailabilities
 * @property ScreenType                    $screen_type
 *
 * @property Pricelist|null                $pricelist
 * @property PricelistProduct|null         $pricing
 *
 * @property Collection<number>            $enabled_inventories
 *
 * @property int                           $locations_count           // Laravel `withCount` result accessor
 * @property Collection<Location>          $locations
 *
 * @property int|null                      $cover_picture_id
 * @property InventoryPicture|null         $cover_picture
 */
class ResolvedProduct extends Product {
	use HasView;
	
	protected $table = "products_view";

	protected $write_table = "products";
}
