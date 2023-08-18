<?php
/*
 * Copyright 2023 (c) Neo-OOH - All Rights Reserved
 * Unauthorized copying of this file, via any medium is strictly prohibited
 * Proprietary and confidential
 * Written by Valentin Dufois <vdufois@neo-ooh.com>
 *
 * @neo/api - ActorLogo.php
 */

namespace Neo\Models;

use Carbon\Carbon;
use Exception;
use Illuminate\Database\Eloquent\Model;
use Illuminate\Http\UploadedFile;
use Illuminate\Support\Facades\Storage;
use Neo\Modules\Broadcast\Exceptions\UnsupportedFileFormatException;
use Neo\Modules\Broadcast\Utils\ThumbnailCreator;

/**
 * Class ActorLogo
 *
 * @package Neo\Models
 *
 * @property int    $id
 * @property int    $actor_id
 * @property string $original_name
 * @property Carbon $created_at
 * @property Carbon $updated_at
 *
 * @property string $file_path
 */
class ActorLogo extends Model {
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
	protected $table = "actors_logo";

	/**
	 * @var array<string>
	 */
	protected $fillable = ["id", "original_name"];

	/**
	 * The accessors to append to the model's array form.
	 *
	 * @var array
	 */
	protected $appends = ['file_path'];


	/*
	|--------------------------------------------------------------------------
	| Custom Attributes
	|--------------------------------------------------------------------------
	*/

	public function getFilePathAttribute(): string {
		return Storage::disk("public")->url("actors_logo/{$this->getKey()}.png");
	}

	/**
	 * @param UploadedFile $file
	 * @throws UnsupportedFileFormatException
	 */
	public function store(UploadedFile $file): void {
		// Resize the image to not be too big
		$thumbCreator = new ThumbnailCreator($file);

		Storage::disk("public")
		       ->writeStream("actors_logo/{$this->getKey()}.png", $thumbCreator->getThumbnailAsStream("png"), ["visibility" => "public"]);
	}

	/**
	 * @throws Exception
	 */
	public function erase(): void {
		// Delete the file
		Storage::disk("public")->delete($this->file_path);
		$this->delete();
	}
}
