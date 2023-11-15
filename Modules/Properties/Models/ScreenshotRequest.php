<?php
/*
 * Copyright 2023 (c) Neo-OOH - All Rights Reserved
 * Unauthorized copying of this file, via any medium is strictly prohibited
 * Proprietary and confidential
 * Written by Valentin Dufois <vdufois@neo-ooh.com>
 *
 * @neo/api - ScreenshotRequest.php
 */

namespace Neo\Modules\Properties\Models;

use Carbon\Carbon;
use Illuminate\Database\Eloquent\Collection;
use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\BelongsTo;
use Illuminate\Database\Eloquent\Relations\HasMany;
use Neo\Helpers\Relation;
use Neo\Models\Traits\HasCreatedByUpdatedBy;
use Neo\Models\Traits\HasPublicRelations;
use Neo\Modules\Broadcast\Models\Location;
use Neo\Modules\Broadcast\Models\Player;
use Neo\Modules\Properties\Models\Product;
use Neo\Modules\Properties\Models\Screenshot;

/**
 * Class ScreenshotRequest
 *
 * @package Neo\Models
 *
 * @property integer                $id
 * @property integer|null           $product_id
 * @property integer|null           $location_id
 * @property integer|null           $player_id
 * @property Carbon                 $send_at
 * @property boolean                $sent
 * @property int                    $scale_percent
 * @property int                    $duration_ms
 * @property int                    $frequency_ms
 * @property Carbon                 $created_at
 * @property int|null               $created_by
 * @property Carbon                 $updated_at
 * @property int|null               $updated_by
 *
 * @property integer                $expected_screenshots
 * @property integer                $screenshots_count
 * @property Collection<Screenshot> $screenshots
 *
 * @property Product|null           $product
 * @property Location|null          $location
 * @property Player|null            $player
 */
class ScreenshotRequest extends Model {
	use HasCreatedByUpdatedBy;
	use HasPublicRelations;

	protected $table = "screenshots_requests";

	/**
	 * The attributes that should be cast.
	 *
	 * @var array<string, string>
	 */
	public $casts = [
		"send_at" => "datetime",
		"sent"    => "boolean",
	];

	protected $fillable = [
		"id",
		"product_id",
		"location_id",
		"player_id",
		"send_at",
		"sent",
		"scale_percent",
		"duration_ms",
		"frequency_ms",
		"created_at",
		"created_by",
		"updated_at",
		"updated_by",
	];

	/**
	 * The attributes that should always be loaded
	 *
	 * @var array
	 */
	protected $appends = [
		"expected_screenshots",
	];

	public function getPublicRelations(): array {
		return [
			"product"           => Relation::make(load: "product.property"),
			"location"          => Relation::make(load: "location"),
			"player"            => Relation::make(load: "player"),
			"screenshots"       => Relation::make(load: "screenshots"),
			"screenshots_count" => Relation::make(count: "screenshots"),
		];
	}

	public function getDeletedByColumn(): string|null {
		return null;
	}

	/*
	|--------------------------------------------------------------------------
	| Relations
	|--------------------------------------------------------------------------
	*/

	public function product(): BelongsTo {
		return $this->belongsTo(Product::class, "product_id", "id");
	}

	public function location(): BelongsTo {
		return $this->belongsTo(Location::class, "location_id", "id");
	}

	public function player(): BelongsTo {
		return $this->belongsTo(Player::class, "player_id", "id");
	}

	public function screenshots(): HasMany {
		return $this->hasMany(Screenshot::class, "request_id", "id")->orderBy("created_at");
	}

	/*
	|--------------------------------------------------------------------------
	| Burst Mechanism
	|--------------------------------------------------------------------------
	*/

	public function getExpectedScreenshotsAttribute() {
		return ceil($this->duration_ms / $this->frequency_ms);
	}
}
