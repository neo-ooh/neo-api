<?php
/*
 * Copyright 2023 (c) Neo-OOH - All Rights Reserved
 * Unauthorized copying of this file, via any medium is strictly prohibited
 * Proprietary and confidential
 * Written by Valentin Dufois <vdufois@neo-ooh.com>
 *
 * @neo/api - NewsRecord.php
 */

namespace Neo\Modules\Dynamics\Models;

use Carbon\Carbon;
use Exception;
use Illuminate\Database\Eloquent\Model;
use Illuminate\Support\Facades\Storage;
use Vinkla\Hashids\Facades\Hashids;

/**
 * @property-read int    $id
 * @property string      $cp_id
 * @property string      $category
 * @property string      $locale
 * @property string      $headline
 * @property Carbon      $date
 * @property string|null $media
 * @property number|null $media_width
 * @property number|null $media_height
 *
 * @property Carbon      $created_at
 * @property Carbon      $updated_at
 *
 * @property-read string $uid
 * @property-read string $media_path
 * @property-read string $media_url
 */
class NewsRecord extends Model {
	protected $table = "news_records";

	protected $primaryKey = "id";

	protected $casts = [
		"date" => "datetime",
	];

	protected $fillable = [
		"cp_id",
		"category",
		"locale",
		"headline",
		"date",
		"media",
	];

	protected $appends = [
		"media_url",
	];

	public function getUidAttribute(): string {
		return Hashids::encode($this->getKey());
	}

	public function getMediaPathAttribute() {
		return "dynamics/news/media/$this->uid";
	}

	public function getMediaUrlAttribute() {
		return Storage::disk("public")->url($this->media_path);
	}

	/**
	 * Takes a file stream and store it
	 *
	 * @param resource $stream
	 * @return void
	 */
	public function storeMediaStream($stream) {
		try {
			Storage::disk("public")->writeStream(
				$this->media_path,
				$stream,
				["visibility" => "public"]
			);
		} catch (Exception $e) {
			// Could not store media
			$this->media        = null;
			$this->media_width  = null;
			$this->media_height = null;
		}

		// Media stored successfully, load it and store its dimensions
		$contents = Storage::disk("public")->get($this->media_path);
		$im       = imagecreatefromstring($contents);

		$this->media_width  = imagesx($im);
		$this->media_height = imagesy($im);
		$this->save();
	}
}
