<?php
/*
 * Copyright 2023 (c) Neo-OOH - All Rights Reserved
 * Unauthorized copying of this file, via any medium is strictly prohibited
 * Proprietary and confidential
 * Written by Valentin Dufois <vdufois@neo-ooh.com>
 *
 * @neo/api - Format.php
 */

namespace Neo\Modules\Broadcast\Models;

use Carbon\Carbon;
use Illuminate\Database\Eloquent\Builder;
use Illuminate\Database\Eloquent\Collection;
use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\BelongsTo;
use Illuminate\Database\Eloquent\Relations\BelongsToMany;
use Illuminate\Database\Eloquent\Relations\HasMany;
use Illuminate\Database\Eloquent\SoftDeletes;
use Neo\Enums\Capability;
use Neo\Helpers\Relation;
use Neo\Models\Traits\HasPublicRelations;
use Neo\Modules\Properties\Models\Product;
use Neo\Modules\Properties\Models\ProductCategory;

/**
 * Neo\Models\Formats
 *
 * @property int                           $id
 * @property string                        $slug
 * @property int                           $network_id
 * @property string                        $name
 * @property int                           $content_length seconds
 * @property int|null                      $main_layout_id
 *
 * @property Carbon                        $created_at
 * @property Carbon                        $updated_at
 * @property Carbon|null                   $deleted_at
 *
 * @property Collection<Layout>            $layouts
 * @property Layout|null                   $main_layout
 * @property Collection<DisplayType>       $display_types
 * @property Collection<BroadcastTag>      $broadcast_tags
 * @property Collection<LoopConfiguration> $loop_configurations
 * @property Collection<FormatCropFrame>   $crop_frames
 *
 * @mixin Builder<Format>
 */
class Format extends Model {
    use SoftDeletes;
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
    protected $table = 'formats';

    /**
     * The attributes that are mass assignable.
     *
     * @var array<string>
     */
    protected $fillable = [
        "network_id",
        "name",
        "content_length",
    ];

    protected $touches = [
        "products",
        "products_categories",
    ];

    public function getPublicRelations(): array {
        return [
            "display_types"       => "display_types",
            "display_types_ids"   => "display_types:id",
            "layouts"             => "layouts",
            "tags"                => "broadcast_tags",
            "loop_configurations" => "loop_configurations",
            "network"             => "network",
            "crop_frames"         => Relation::make(
                load: "crop_frames",
                gate: Capability::formats_crop_frames_edit
            ),
        ];
    }

    protected static function boot(): void {
        parent::boot();

        static::deleting(static function (Format $format) {
            // Clean up loop configurations
            $loopConfigurations = $format->loop_configurations;
            $format->loop_configurations()->detach();

            $loopConfigurations->each(
                fn(LoopConfiguration $loopConfiguration) => $loopConfiguration->delete()
            );
        });
    }


    /*
    |--------------------------------------------------------------------------
    | Relations
    |--------------------------------------------------------------------------
    */

    /**
     * @return BelongsTo<Network, Format>
     */
    public function network(): BelongsTo {
        return $this->belongsTo(Network::class, "network_id");
    }

    /**
     * @return BelongsToMany<DisplayType>
     */
    public function display_types(): BelongsToMany {
        return $this->belongsToMany(DisplayType::class, 'format_display_types', 'format_id', "display_type_id")
                    ->orderBy("name");
    }

    /**
     * @return BelongsToMany<Layout>
     */
    public function layouts(): BelongsToMany {
        return $this->belongsToMany(Layout::class, 'format_layouts', 'format_id', 'layout_id')
                    ->withPivot(["is_fullscreen"])
                    ->as("settings")->using(FormatLayoutPivot::class)
                    ->orderBy("name_en")
                    ->orderBy("name_fr");
    }

    public function main_layout(): BelongsTo {
        return $this->belongsTo(Layout::class, "main_layout_id", "id");
    }

    /**
     * @return BelongsToMany<BroadcastTag>
     */
    public function broadcast_tags(): BelongsToMany {
        return $this->belongsToMany(BroadcastTag::class, 'format_broadcast_tags', 'format_id', 'broadcast_tag_id')
                    ->orderBy("name_en")
                    ->orderBy("name_fr");
    }

    /**
     * @return BelongsToMany<LoopConfiguration>
     */
    public function loop_configurations(): BelongsToMany {
        return $this->belongsToMany(LoopConfiguration::class, 'format_loop_configurations', 'format_id', 'loop_configuration_id');
    }

    /**
     * @return HasMany<ProductCategory>
     */
    public function products_categories(): HasMany {
        return $this->hasMany(ProductCategory::class, "format_id", "id");
    }

    /**
     * @return HasMany<LoopConfiguration>
     */
    public function products(): HasMany {
        return $this->hasMany(Product::class, "format_id", "id");
    }

    /**
     * @return HasMany<FormatCropFrame>
     */
    public function crop_frames(): HasMany {
        return $this->hasMany(FormatCropFrame::class, "format_id", "id");
    }
}
