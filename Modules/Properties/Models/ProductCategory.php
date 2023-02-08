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
use Neo\Models\Traits\HasPublicRelations;
use Neo\Modules\Broadcast\Models\Format;
use Neo\Modules\Broadcast\Models\LoopConfiguration;
use Neo\Modules\Properties\Enums\ProductsFillStrategy;
use Neo\Modules\Properties\Models\Interfaces\WithAttachments;
use Neo\Modules\Properties\Models\Interfaces\WithImpressionsModels;
use Neo\Modules\Properties\Models\Traits\HasImpressionsModels;
use Staudenmeir\EloquentHasManyDeep\HasManyDeep;
use Staudenmeir\EloquentHasManyDeep\HasRelationships;

/**
 * @property int                           $id
 * @property int                           $external_id
 * @property int                           $type_id
 * @property string                        $name_en
 * @property string                        $name_fr
 * @property ProductsFillStrategy          $fill_strategy
 * @property int|null                      $format_id
 * @property Carbon                        $created_at
 * @property Carbon                        $updated_at
 *
 * @property ProductType                   $type
 * @property Format                        $format
 * @property Collection<ImpressionsModel>  $impressions_models
 * @property Collection<LoopConfiguration> $loop_configurations
 */
class ProductCategory extends Model implements WithImpressionsModels, WithAttachments {
    use HasImpressionsModels;
    use HasRelationships;
    use HasPublicRelations;

    protected $table = "products_categories";

    protected $primaryKey = "id";

    protected $fillable = [
        "type_id",
        "name_en",
        "name_fr",
        "fill_strategy",
        "external_id",
    ];

    protected $casts = [
        "fill_strategy" => ProductsFillStrategy::class,
    ];

    public string $impressions_models_pivot_table = "products_categories_impressions_models";

    public function getPublicRelations() {
        return [
            "type"                => "load:product_type",
            "properties"          => "load:properties",
            "format"              => "load:format",
            "attachments"         => "load:attachments",
            "products"            => "load:products",
            "impressions_models"  => "load:impressions_models",
            "loop_configurations" => "load:loop_configurations",
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

    public function product_type(): BelongsTo {
        return $this->belongsTo(ProductType::class, "type_id", "id");
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
}
