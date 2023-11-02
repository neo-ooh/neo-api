<?php
/*
 * Copyright 2023 (c) Neo-OOH - All Rights Reserved
 * Unauthorized copying of this file, via any medium is strictly prohibited
 * Proprietary and confidential
 * Written by Valentin Dufois <vdufois@neo-ooh.com>
 *
 * @neo/api - WeatherBundleBackground.php
 */

namespace Neo\Modules\Dynamics\Models;

use Carbon\Carbon;
use Illuminate\Database\Eloquent\Model;
use Illuminate\Http\UploadedFile;
use Illuminate\Support\Facades\Storage;
use Neo\Models\Traits\HasCreatedByUpdatedBy;
use Neo\Models\Traits\HasPublicRelations;
use Neo\Modules\Broadcast\Exceptions\UnsupportedFileFormatException;
use Neo\Modules\Broadcast\Models\Format;
use Neo\Modules\Broadcast\Utils\ThumbnailCreator;
use Vinkla\Hashids\Facades\Hashids;

/**
 * @property int         $id
 * @property int         $bundle_id
 * @property string|null $weather
 * @property string|null $period
 * @property int         $format_id
 * @property string      $extension
 *
 * @property Carbon      $created_at
 * @property int         $created_by
 * @property Carbon      $updated_at
 * @property int         $updated_by
 *
 * @property string      $uid
 * @property string      $file_path
 * @property string      $thumbnail_path
 * @property string      $url
 * @property string      $thumbnail_url
 */
class WeatherBundleBackground extends Model {
	use HasPublicRelations;
	use HasCreatedByUpdatedBy;

	/*
	|--------------------------------------------------------------------------
	| Table properties
	|--------------------------------------------------------------------------
	*/

	protected $table = "weather_bundle_backgrounds";

	public $incrementing = true;

	protected $primaryKey = "id";

	protected $casts = [
	];

	protected $appends = [
		"url",
		"thumbnail_url",
	];

	public function getPublicRelations(): array {
		return [
		];
	}

	protected static function boot() {
		parent::boot();

		static::deleting(static function (WeatherBundleBackground $background) {
			Storage::disk("public")->delete($background->file_path);
			Storage::disk("public")->delete($background->thumbnail_path);
		});
	}

	/*
	|--------------------------------------------------------------------------
	| Relations
	|--------------------------------------------------------------------------
	*/

	public function bundle() {
		return $this->belongsTo(WeatherBundle::class, "flight_id");
	}

	public function format() {
		return $this->belongsTo(Format::class, "format_id");
	}

	/*
	|--------------------------------------------------------------------------
	| Image Handling
	|--------------------------------------------------------------------------
	*/

	public function getUidAttribute(): string {
		return Hashids::encode($this->getKey());
	}

	public function getFilePathAttribute(): string {
		return "dynamics/weather/backgrounds/$this->uid.$this->extension";
	}

	public function getThumbnailPathAttribute() {
		return "dynamics/weather/backgrounds/$this->uid-thumb.$this->extension";
	}

	public function getUrlAttribute() {
		return Storage::disk("public")->url($this->file_path);
	}

	public function getThumbnailUrlAttribute() {
		return Storage::disk("public")->url($this->thumbnail_path);
	}

	/**
	 * @throws UnsupportedFileFormatException
	 */
	public function store(UploadedFile $file): void {
		Storage::disk("public")
		       ->putFileAs("dynamics/weather/backgrounds/", $file, "$this->uid.$this->extension", ["visibility" => "public"]);

		$creator = new ThumbnailCreator($file);
		Storage::disk("public")
		       ->writeStream($this->thumbnail_path, $creator->getThumbnailAsStream(), ["visibility" => "public"]);
	}
}
