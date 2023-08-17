<?php
/*
 * Copyright 2023 (c) Neo-OOH - All Rights Reserved
 * Unauthorized copying of this file, via any medium is strictly prohibited
 * Proprietary and confidential
 * Written by Valentin Dufois <vdufois@neo-ooh.com>
 *
 * @neo/api - ContractLine.php
 */

namespace Neo\Models;

use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\BelongsTo;
use Illuminate\Database\Eloquent\Relations\BelongsToMany;
use Neo\Models\Traits\HasView;
use Neo\Modules\Broadcast\Models\Campaign;
use Neo\Modules\Properties\Enums\ProductType;
use Neo\Modules\Properties\Models\Product;
use Neo\Resources\Contracts\FlightProductPerformanceDatum;
use Staudenmeir\EloquentHasManyDeep\HasRelationships;

/**
 * @property-read int                           $id
 * @property-read int                           $product_id
 * @property-read int                           $flight_id
 * @property-read int                           $external_id
 * @property double                             $spots
 * @property double                             $media_value
 * @property double                             $discount
 * @property string                             $discount_type
 * @property double                             $price
 * @property int                                $traffic
 * @property int                                $impressions
 *
 * @property ContractFlight                     $flight
 * @property Product|null                       $product
 *
 * @property-read number|null                   $network_id
 * @property-read ProductType|null              $product_type
 *
 * @property FlightProductPerformanceDatum|null $performances Performances for this line. To be filled using
 *           `ContractFlight::fillLinesPerformances()`
 */
class ContractLine extends Model {
	use HasRelationships;
	use HasView;

	protected $table = "contracts_lines_view";

	public $write_table = "contracts_lines";
	
	protected $primaryKey = "id";

	protected $fillable = [
		"product_id",
		"flight_id",
		"external_id",
		"spots",
		"media_value",
		"discount",
		"discount_type",
		"price",
		"traffic",
		"impressions",
		"created_at",
		"updated_at",
	];

	public function flight(): BelongsTo {
		return $this->belongsTo(ContractFlight::class, "flight_id", "id");
	}

	public function product(): BelongsTo {
		return $this->belongsTo(Product::class, "product_id", "id");
	}

	public function campaigns(): BelongsToMany {
		return $this->belongsToMany(Campaign::class, "contract_lines_campaigns", "contract_line_id", "campaign_id");
	}
}
