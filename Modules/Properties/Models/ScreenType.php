<?php
/*
 * Copyright 2023 (c) Neo-OOH - All Rights Reserved
 * Unauthorized copying of this file, via any medium is strictly prohibited
 * Proprietary and confidential
 * Written by Valentin Dufois <vdufois@neo-ooh.com>
 *
 * @neo/api - ScreenType.php
 */

namespace Neo\Modules\Properties\Models;

use Carbon\Carbon;
use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\HasMany;
use Illuminate\Database\Eloquent\SoftDeletes;
use Neo\Enums\Capability;
use Neo\Helpers\Relation;
use Neo\Models\Traits\HasCreatedByUpdatedBy;
use Neo\Models\Traits\HasPublicRelations;
use Neo\Modules\Properties\Models\Traits\InventoryResourceModel;
use Neo\Modules\Properties\Services\Resources\Enums\InventoryResourceType;

/**
 * @property int         $id
 * @property string      $name_en
 * @property string      $name_fr
 *
 * @property Carbon      $created_at
 * @property int         $created_by
 * @property Carbon      $updated_at
 * @property int         $updated_by
 * @property Carbon|null $deleted_at
 * @property int|null    $deleted_by
 */
class ScreenType extends Model {
    use SoftDeletes;
    use InventoryResourceModel;
    use HasCreatedByUpdatedBy;
    use HasPublicRelations;

    /*
    |--------------------------------------------------------------------------
    | Table properties
    |--------------------------------------------------------------------------
    */

    /**
     * The table associated with the model.
     *
     * @var string
     */
    protected $table = "screen_types";

    /**
     * The primary key for the table
     *
     * @var string
     */
    protected $primaryKey = "id";

    /**
     * Indicates if the IDs are auto-incrementing.
     * In this case, it is handled by the `InventoryResourceModel` traits
     *
     * @var bool
     */
    public $incrementing = false;

    /**
     * The inventory resource key
     *
     * @var string
     */
    protected string $inventoryKey = "id";

    protected InventoryResourceType $inventoryResourceType = InventoryResourceType::PropertyType;

    protected $fillable = [
        "name_en",
        "name_fr",
    ];

    public function getPublicRelations(): array {
        return [
            "product_categories" => Relation::make(
                load: "product_categories",
                gate: Capability::product_categories_edit,
            ),
            "products"           => Relation::make(
                load: "products",
                gate: Capability::products_view,
            ),
            "inventories"        => Relation::make(
                load: ["inventory_resource.inventories_settings", "inventory_resource.external_representations"],
                gate: Capability::properties_inventories_view
            ),
        ];
    }

    /*
    |--------------------------------------------------------------------------
    | Relations
    |--------------------------------------------------------------------------
    */

    /**
     * @return HasMany
     */
    public function product_categories(): HasMany {
        return $this->hasMany(ProductCategory::class, "screen_type_id", "id");
    }

    /**
     * @return HasMany
     */
    public function products(): HasMany {
        return $this->hasMany(Product::class, "screen_type_id", "id");
    }
}
