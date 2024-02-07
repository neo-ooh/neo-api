<?php
/*
 * Copyright 2023 (c) Neo-OOH - All Rights Reserved
 * Unauthorized copying of this file, via any medium is strictly prohibited
 * Proprietary and confidential
 * Written by Valentin Dufois <vdufois@neo-ooh.com>
 *
 * @neo/api - Screenshot.php
 */

namespace Neo\Modules\Properties\Models;

use Carbon\Carbon;
use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\BelongsTo;
use Illuminate\Database\Eloquent\Relations\BelongsToMany;
use Illuminate\Database\Eloquent\SoftDeletes;
use Illuminate\Support\Facades\Storage;
use ImagickException;
use Neo\Helpers\Relation;
use Neo\Modules\Properties\Models\ScreenshotRequest;
use Neo\Models\Traits\HasPublicRelations;
use Neo\Modules\Broadcast\Models\Location;
use Neo\Modules\Broadcast\Models\Player;
use Neo\Modules\Properties\Models\Contract;
use Neo\Modules\Properties\Models\Product;
use Neo\Utils\MockupContractScreenshot;
use Staudenmeir\EloquentHasManyDeep\HasRelationships;

/**
 * Class Screenshot
 *
 * @package Neo\Models
 *
 * @property integer           $id
 * @property integer|null      $product_id
 * @property integer|null      $location_id
 * @property integer           $player_id
 * @property integer           $request_id
 * @property Carbon            $received_at
 * @property Carbon|null            $deleted_at
 *
 * @property ScreenshotRequest $request
 * @property Product           $product
 * @property Location          $location
 * @property Player            $player
 *
 * @property string            $file_path
 * @property string            $url
 * @property string            $mockup_path
 */
class Screenshot extends Model {
    use SoftDeletes;
	use HasPublicRelations;
	use HasRelationships;

	protected $table = "screenshots";

	public $timestamps = false;

	protected $casts = [
		"received_at" => 'datetime',
	];

	protected $fillable = [
		"id",
		"product_id",
		"location_id",
		"player_id",
		"request_id",
		"received_at",
	];

	protected $appends = [
		"url",
	];

	public function getPublicRelations(): array {
		return [
			"product"  => Relation::make(load: "product.property"),
			"location" => Relation::make(load: "location"),
			"player"   => Relation::make(load: "player"),
			"request"  => Relation::make(load: "request"),
		];
	}

	protected static function boot(): void {
		parent::boot();

		static::deleting(static function (Screenshot $screenshot) {
			Storage::disk("public")->delete($screenshot->file_path);
		});
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

	public function request(): BelongsTo {
		return $this->belongsTo(ScreenshotRequest::class, "request_id", "id");
	}

	public function contracts(): BelongsToMany {
		return $this->belongsToMany(Contract::class, "contracts_screenshots", "screenshot_id", "contract_id");
	}

	/*
	|--------------------------------------------------------------------------
	| Screenshot
	|--------------------------------------------------------------------------
	*/

	public function getFilePathAttribute(): string {
		return "bursts/$this->request_id/{$this->getKey()}.jpg";
	}

	/**
	 * @param resource $screenshot
	 */
	public function store($screenshot): void {
		Storage::disk("public")->writeStream($this->file_path, $screenshot, ["visibility" => "public"]);
	}


	public function getUrlAttribute(): string {
		return Storage::disk("public")->url($this->file_path);
	}

	/**
	 * @param bool $trim
	 * @return string
	 * @throws ImagickException
	 */
	public function makeMockupPath(bool $trim): string {
		$mockup = new MockupContractScreenshot($this);
		return $mockup->makeMockup($trim) ?? $this->url;
	}
}
