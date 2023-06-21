<?php
/*
 * Copyright 2023 (c) Neo-OOH - All Rights Reserved
 * Unauthorized copying of this file, via any medium is strictly prohibited
 * Proprietary and confidential
 * Written by Valentin Dufois <vdufois@neo-ooh.com>
 *
 * @neo/api - ProductCategory.php
 */

namespace Neo\Modules\Properties\Models;

use Carbon\Carbon;
use Illuminate\Database\Eloquent\Collection;
use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\BelongsTo;
use Illuminate\Database\Eloquent\Relations\BelongsToMany;
use Illuminate\Database\Eloquent\Relations\HasMany;
use Neo\Casts\EnumSetCast;
use Neo\Enums\Capability;
use Neo\Helpers\Relation;
use Neo\Models\Traits\HasPublicRelations;
use Neo\Modules\Broadcast\Models\Format;
use Neo\Modules\Broadcast\Models\LoopConfiguration;
use Neo\Modules\Properties\Enums\MediaType;
use Neo\Modules\Properties\Enums\ProductType;
use Neo\Modules\Properties\Models\Interfaces\WithAttachments;
use Neo\Modules\Properties\Models\Interfaces\WithImpressionsModels;
use Neo\Modules\Properties\Models\Traits\HasImpressionsModels;
use Neo\Modules\Properties\Models\Traits\InventoryResourceModel;
use Neo\Modules\Properties\Services\Resources\Enums\InventoryResourceType;
use Staudenmeir\EloquentHasManyDeep\HasManyDeep;
use Staudenmeir\EloquentHasManyDeep\HasRelationships;

/**
 * @property int                           $id
 * @property int                           $inventory_resource_id
 * @property string                        $name_en
 * @property string                        $name_fr
 * @property ProductType                   $type
 * @property int|null                      $format_id
 * @property MediaType[]                   $allowed_media_types
 * @property boolean                       $allows_audio
 * @property boolean                       $allows_motion
 * @property double                        $production_cost
 * @property double                        $programmatic_price
 * @property double                        $screen_size_in
 * @property int|null                      $screen_type_id
 * @property Carbon                        $created_at
 * @property Carbon                        $updated_at
 *
 * @property Format                        $format
 * @property Collection<ImpressionsModel>  $impressions_models
 * @property Collection<LoopConfiguration> $loop_configurations
 * @property ScreenType                    $screen_type
 *
 * @property PricelistProductsCategory     $pricing
 *
 * @property int|null                      $cover_picture_id
 * @property InventoryPicture|null         $cover_picture
 */
class ProductCategory extends Model implements WithImpressionsModels, WithAttachments {
    use HasImpressionsModels;
    use HasRelationships;
    use HasPublicRelations;
    use InventoryResourceModel;

    protected $table = "products_categories";

    protected $primaryKey = "id";

    protected $fillable = [
        "name_en",
        "name_fr",
        "type",
        "external_id",
        "allowed_media_types",
        "allows_audio",
        "production_cost",
        "programmatic_price",
    ];

    protected $casts = [
        "type"                => ProductType::class,
        "allowed_media_types" => EnumSetCast::class . ":" . MediaType::class,
        "allows_audio"        => "boolean",
        "allows_motion"       => "boolean",
    ];

    public string $impressions_models_pivot_table = "products_categories_impressions_models";

    public InventoryResourceType $inventoryResourceType = InventoryResourceType::ProductCategory;

    public $touches = [
        "products",
    ];

    public function getPublicRelations() {
        return [
            "attachments"         => "load:attachments",
            "cover_picture"       => Relation::make(
                load: "cover_picture",
                gate: Capability::properties_pictures_view,
            ),
            "format"              => "load:format",
            "impressions_models"  => "load:impressions_models",
            "inventories"         => Relation::make(
                load: ["inventory_resource.inventories_settings", "inventory_resource.external_representations"],
                gate: Capability::properties_inventories_view
            ),
            "loop_configurations" => "load:loop_configurations",
            "pictures"            => Relation::make(
                load: "pictures",
                gate: Capability::properties_pictures_view
            ),
            "products"            => "load:products",
            "properties"          => "load:properties",
            "screen_type"         => "screen_type",
        ];
    }

    /*
    |--------------------------------------------------------------------------
    | Relations
    |--------------------------------------------------------------------------
    */

    public function properties(): BelongsToMany {
        return $this->belongsToMany(Property::class, "products", "category_id", "property_id");
    }

    public function format(): BelongsTo {
        return $this->belongsTo(Format::class, "format_id", "id");
    }

    public function products(): HasMany {
        return $this->hasMany(Product::class, "category_id", "id");
    }

    public function attachments(): BelongsToMany {
        return $this->belongsToMany(Attachment::class, "products_categories_attachments", "product_category_id", "attachment_id");
    }

    public function loop_configurations(): HasManyDeep {
        return $this->hasManyDeepFromRelations([$this->format(), (new Format())->loop_configurations()]);
    }

    public function screen_type(): BelongsTo {
        return $this->belongsTo(ScreenType::class, "screen_type_id", "id");
    }

    public function pictures(): HasManyDeep {
        return $this->hasManyDeepFromRelations([$this->products(), (new Product())->pictures()]);
    }

    public function cover_picture(): BelongsTo {
        return $this->belongsTo(InventoryPicture::class, "cover_picture_id", "id");
    }
}
