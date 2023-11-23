<?php
/*
 * Copyright 2023 (c) Neo-OOH - All Rights Reserved
 * Unauthorized copying of this file, via any medium is strictly prohibited
 * Proprietary and confidential
 * Written by Valentin Dufois <vdufois@neo-ooh.com>
 *
 * @neo/api - WeatherBundle.php
 */

namespace Neo\Modules\Dynamics\Models;

use Carbon\Carbon;
use Illuminate\Database\Eloquent\Collection;
use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\BelongsTo;
use Illuminate\Database\Eloquent\Relations\BelongsToMany;
use Illuminate\Database\Eloquent\Relations\HasMany;
use Illuminate\Database\Eloquent\SoftDeletes;
use Neo\Helpers\Relation;
use Neo\Models\Traits\HasCreatedByUpdatedBy;
use Neo\Models\Traits\HasPublicRelations;
use Neo\Modules\Broadcast\Models\Format;
use Neo\Modules\Dynamics\Models\Enums\WeatherBundleBackgroundSelection;
use Neo\Modules\Dynamics\Models\Enums\WeatherBundleLayout;
use Neo\Modules\Dynamics\Models\Structs\WeatherBundleTargeting;
use Neo\Modules\Properties\Models\ContractFlight;

/**
 * @property int                                 $id
 * @property string                              $name
 * @property int|null                            $flight_id
 * @property Carbon                              $start_date
 * @property Carbon                              $end_date
 * @property boolean                             $ignore_years
 * @property int                                 $priority
 * @property WeatherBundleLayout                 $layout
 * @property WeatherBundleTargeting              $targeting
 * @property WeatherBundleBackgroundSelection    $background_selection
 *
 * @property Carbon                              $created_at
 * @property int                                 $created_by
 * @property Carbon                              $updated_at
 * @property int                                 $updated_by
 * @property Carbon|null                         $deleted_at
 * @property int|null                            $deleted_by
 *
 * @property Collection<WeatherBundleBackground> $backgrounds
 */
class WeatherBundle extends Model {
	use SoftDeletes;
	use HasPublicRelations;
	use HasCreatedByUpdatedBy;

	/*
	|--------------------------------------------------------------------------
	| Table properties
	|--------------------------------------------------------------------------
	*/

	protected $table = "weather_bundles";

	public $incrementing = true;

	protected $primaryKey = "id";

	protected $casts = [
		"start_date"           => "date",
		"end_date"             => "date",
		"ignore_years"         => "boolean",
		"layout"               => WeatherBundleLayout::class,
		"targeting"            => WeatherBundleTargeting::class,
		"background_selection" => WeatherBundleBackgroundSelection::class,
	];

	public function getPublicRelations(): array {
		return [
			"formats"     => Relation::make(load: "formats.layouts.frames"),
			"backgrounds" => Relation::make(load: "backgrounds"),
		];
	}

	protected static function boot() {
		parent::boot();

		static::deleting(static function (WeatherBundle $weatherBundle) {
			/** @var WeatherBundleBackground $background */
			foreach ($weatherBundle->backgrounds as $background) {
				$background->delete();
			}
		});
	}

	/*
	|--------------------------------------------------------------------------
	| Relations
	|--------------------------------------------------------------------------
	*/

	/**
	 * @return BelongsTo<ContractFlight>
	 */
	public function flight(): BelongsTo {
		return $this->belongsTo(ContractFlight::class, "flight_id", "id");
	}

	/**
	 * @return BelongsToMany<Format>
	 */
	public function formats(): BelongsToMany {
		return $this->belongsToMany(Format::class, "weather_bundle_formats", "bundle_id", "format_id");
	}

	/**
	 * @return HasMany<WeatherBundleBackground>
	 */
	public function backgrounds(): HasMany {
		return $this->hasMany(WeatherBundleBackground::class, "bundle_id");
	}
}
