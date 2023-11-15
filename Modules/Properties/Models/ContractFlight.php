<?php
/*
 * Copyright 2023 (c) Neo-OOH - All Rights Reserved
 * Unauthorized copying of this file, via any medium is strictly prohibited
 * Proprietary and confidential
 * Written by Valentin Dufois <vdufois@neo-ooh.com>
 *
 * @neo/api - ContractFlight.php
 */

namespace Neo\Modules\Properties\Models;

use Illuminate\Database\Eloquent\Builder;
use Illuminate\Database\Eloquent\Collection as EloquentCollection;
use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\BelongsTo;
use Illuminate\Database\Eloquent\Relations\BelongsToMany;
use Illuminate\Database\Eloquent\Relations\HasMany;
use Illuminate\Support\Carbon;
use Illuminate\Support\Collection;
use Illuminate\Support\Facades\Cache;
use Illuminate\Support\Facades\DB;
use Neo\Helpers\Relation;
use Neo\Models\Traits\HasPublicRelations;
use Neo\Models\Traits\HasView;
use Neo\Modules\Broadcast\Exceptions\InvalidBroadcasterAdapterException;
use Neo\Modules\Broadcast\Models\Campaign;
use Neo\Modules\Broadcast\Models\Location;
use Neo\Modules\Broadcast\Models\Network;
use Neo\Modules\Broadcast\Models\ResourcePerformance;
use Neo\Modules\Broadcast\Services\BroadcasterAdapterFactory;
use Neo\Modules\Broadcast\Services\BroadcasterCapability;
use Neo\Modules\Broadcast\Services\BroadcasterOperator;
use Neo\Modules\Broadcast\Services\BroadcasterReporting;
use Neo\Modules\Broadcast\Services\Resources\CampaignLocationPerformance;
use Neo\Modules\Broadcast\Services\Resources\CampaignPerformance;
use Neo\Modules\Properties\Models\StructuredColumns\ContractFlightParameters;
use Neo\Resources\Contracts\FlightPerformanceDatum;
use Neo\Resources\Contracts\FlightProductPerformanceDatum;
use Neo\Resources\FlightType;
use Staudenmeir\EloquentHasManyDeep\HasManyDeep;
use Staudenmeir\EloquentHasManyDeep\HasRelationships;

/**
 * @property-read int                                  $id
 * @property-read int                                  $uid
 * @property-read int                                  $contract_id
 * @property string                                    $name
 * @property Carbon                                    $start_date
 * @property Carbon                                    $end_date
 * @property FlightType                                $type
 * @property ContractFlightParameters                  $parameters
 * @property boolean                                   $additional_lines_imported
 * @property boolean                                   $missing_lines_on_import
 * @property-read Carbon                               $created_at
 * @property-read Carbon                               $updated_at
 *
 * @property-read  int                                 $expected_impressions
 * @property EloquentCollection<ContractLine>          $lines
 * @property EloquentCollection<ContractReservation>   $reservations
 * @property Contract                                  $contract
 * @property EloquentCollection<Campaign>              $campaigns
 * @property EloquentCollection<Screenshot>            $screenshots
 *
 * @property Collection<FlightPerformanceDatum>        $performances          // Attribute
 * @property Collection<FlightProductPerformanceDatum> $products_performances // Attribute
 * @property Collection<Location>                      $locations             // Attribute
 */
class ContractFlight extends Model {
	use HasPublicRelations;
	use HasRelationships;
	use HasView;

	protected $table = "contracts_flights_view";

	public $write_table = "contracts_flights";

	protected $primaryKey = "id";

	protected $fillable = [
		"contract_id",
		"uid",
		"name",
		"start_date",
		"end_date",
		"type",
		"parameters",
	];

	/**
	 * The attributes that should be cast.
	 *
	 * @var array<string, string>
	 */
	public $casts = [
		"start_date"                => "date",
		"end_date"                  => "date",
		"type"                      => FlightType::class,
		"additional_lines_imported" => "boolean",
		"missing_lines_on_import"   => "boolean",
		"parameters"                => ContractFlightParameters::class,
	];

	protected function getPublicRelations(): array {
		return [
			"advertiser"            => Relation::make(load: "contract.advertiser"),
			"campaigns"             => Relation::make(load: "campaigns"),
			"contract"              => Relation::make(load: "contract"),
			"lines"                 => Relation::make(load: "lines.product.property"),
			"lines-campaigns"       => Relation::make(load: "lines.campaigns"),
			"performances"          => Relation::make(
				load  : "campaigns.performances",
				append: "performances"
			),
			"products"              => Relation::make(load: "products"),
			"products-performances" => Relation::make(append: "products_performances", custom: fn(ContractFlight $flight) => $flight->fillLinesPerformances()),
			"screenshots"           => Relation::make(load: ["screenshots.product.property", "screenshots.location"]),
		];
	}

	/*
	|--------------------------------------------------------------------------
	| Relations
	|--------------------------------------------------------------------------
	*/

	public function lines(): HasMany {
		return $this->hasMany(ContractLine::class, "flight_id", "id");
	}

	/**
	 * @return HasManyDeep<Product>
	 */
	public function products(): HasManyDeep {
		return $this->hasManyDeepFromRelations([$this->lines(), (new ContractLine())->product()]);
	}

	public function reservations(): HasMany {
		return $this->hasMany(ContractReservation::class, "flight_id", "id");
	}

	public function contract(): BelongsTo {
		return $this->belongsTo(Contract::class, "contract_id", "id");
	}

	public function campaigns(): HasMany {
		return $this->hasMany(Campaign::class, "flight_id", "id");
	}

	/**
	 * @return BelongsToMany<Screenshot>
	 */
	public function screenshots(): BelongsToMany {
		return $this->belongsToMany(Screenshot::class, "contracts_screenshots", "flight_id", "screenshot_id");
	}

	/*
	|--------------------------------------------------------------------------
	| Performances
	|--------------------------------------------------------------------------
	*/

	/**
	 * Load the flight day-by-day performances, aggregated from all sources – Campaigns & Reservations
	 *
	 * @return EloquentCollection<FlightPerformanceDatum>
	 * @throws InvalidBroadcasterAdapterException
	 */
	public function getPerformancesAttribute(): Collection {
		// Contract can pull their performances from two different places:
		// The first place and most straightforward is from the campaign associated with the flight that are
		// setup in Connect. For these, we just have to format the already stored performances.
		// The other place is external reservations (campaigns in external broadcaster) that have been associated directly
		// with the flight without passing through a Connect campaign. For these we need to pull the performances straight
		// from the broadcaster and format them
		$this->campaigns->load("performances");

		/** @var EloquentCollection<FlightPerformanceDatum> $performances */
		$performances = collect();

		foreach ($this->campaigns as $campaign) {
			$performances->push(...$campaign->performances->map(function (ResourcePerformance $performance) {
				return new FlightPerformanceDatum(
					flight_id  : $this->getKey(),
					network_id : $performance->data->network_id,
					recorded_at: $performance->recorded_at->toDateString(),
					repetitions: $performance->repetitions,
					impressions: $performance->impressions,
				);
			}));
		}

		// Append reservations performances as well
		$performances->push(...$this->getReservationsPerformances());

		return $performances;
	}

	/**
	 * Loads day-by-day performances of attached reservations
	 *
	 * @return Collection<FlightPerformanceDatum>
	 * @throws InvalidBroadcasterAdapterException
	 */
	protected function getReservationsPerformances(): Collection {
		return Cache::tags(["contract-performances", $this->contract->contract_id])
		            ->remember("contract-" . $this->id . "-performances", 3600 * 3, function () {
			            $reservationsByBroadcaster = $this->reservations->groupBy("broadcaster_id");

			            /** @var Collection<FlightPerformanceDatum> $performances */
			            $performances = collect();

			            /** @var EloquentCollection<Network> $networks */
			            $networks = Network::query()->whereHas('broadcaster_connection', function (Builder $query) {
				            $query->where("contracts", "=", true);
			            })->get();

			            /** @var EloquentCollection<ContractReservation> $reservations */
			            foreach ($reservationsByBroadcaster as $broadcasterId => $reservations) {
				            // For each reservation, we try to find on which network it is playing
				            $reservationsNetworks = [];
				            foreach ($reservations as $reservation) {
					            foreach ($networks as $network) {
						            if (stripos($reservation->original_name, "_{$network->slug}_") !== false ||
							            stripos($reservation->original_name, "-{$network->slug}-") !== false) {
							            $reservationsNetworks[$reservation->external_id] = $network->getKey();
							            break;
						            }
					            }
				            }

				            /** @var BroadcasterOperator & BroadcasterReporting $broadcaster */
				            $broadcaster = BroadcasterAdapterFactory::makeForBroadcaster($broadcasterId);

				            // Make sure the broadcaster supports reporting
				            if (!$broadcaster->hasCapability(BroadcasterCapability::Reporting)) {
					            continue;
				            }

				            $performances->push(...collect($broadcaster->getCampaignsPerformances(
					            $reservations->map(fn(ContractReservation $reservation) => $reservation->toResource())
					                         ->all()
				            ))->map(function (CampaignPerformance $performance) use ($reservationsNetworks): FlightPerformanceDatum {
					            return new FlightPerformanceDatum(
						            flight_id  : $this->getKey(),
						            network_id : $reservationsNetworks[$performance->campaign->external_id] ?? null,
						            recorded_at: $performance->date,
						            repetitions: $performance->repetitions,
						            impressions: $performance->impressions,
					            );
				            }));
			            }

			            return $performances;
		            });
	}

	/**
	 * Performances of the flight on a per-product basis
	 *
	 * @throws InvalidBroadcasterAdapterException
	 */
	public function getProductsPerformancesAttribute() {
		$this->campaigns->load("location_performances");

		$results = collect(DB::select(<<<EOL
				  WITH `flight_products` AS
				         (SELECT `pl`.`product_id` as `id`
				            FROM `products_locations` `pl`
				                 JOIN `products` `p` ON `pl`.`product_id` = `p`.`id`
				                 JOIN `contracts_lines` `cl` ON `p`.`id` = `cl`.`product_id`
				           WHERE `flight_id` = ?)
				SELECT `pl`.`product_id`,
				       SUM(`rlp`.`repetitions`) as repetitions,
				       SUM(`rlp`.`impressions`) as impressions
				    FROM `resource_location_performances` `rlp`
				       JOIN `campaigns` `c` ON `rlp`.`resource_id` = `c`.`id`
				       LEFT JOIN `products_locations` `pl` ON `rlp`.`location_id` = `pl`.`location_id`
				 WHERE `c`.`flight_id` = ?
				   AND `pl`.`product_id` IN (SELECT `id` FROM `flight_products`)
				   GROUP BY `pl`.`product_id`
				EOL
			, [$this->getKey(), $this->getKey()]));

		$performances = $results->map(fn($r) => new FlightProductPerformanceDatum(
			flight_id  : $this->getKey(),
			product_id : $r->product_id,
			repetitions: $r->repetitions,
			impressions: $r->impressions,
		));

		$performances->push(...$this->getReservationsLocationPerformances());
		return $performances->groupBy("product_id")->map(fn(Collection $data) => new FlightProductPerformanceDatum(
			flight_id  : $data->first()->flight_id,
			product_id : $data->first()->product_id,
			repetitions: $data->sum("repetitions"),
			impressions: $data->sum("impressions"),
		)
		);
	}

	/**
	 * Loads per-products performances of attached reservations
	 *
	 * @return Collection<FlightProductPerformanceDatum>
	 * @throws InvalidBroadcasterAdapterException
	 */
	public function getReservationsLocationPerformances(): Collection {
		return Cache::tags(["contract-performances", $this->contract->contract_id])
		            ->remember("contract-" . $this->id . "-product-performances", 3600 * 3, function () {
			            $reservationsByBroadcaster = $this->reservations->groupBy("broadcaster_id");

			            /** @var EloquentCollection<Network> $networks */
			            $networks = Network::query()->whereHas('broadcaster_connection', function (Builder $query) {
				            $query->where("contracts", "=", true);
			            })->get();

			            $tempTableName = "reservations_performances";
			            DB::statement("DROP TABLE IF EXISTS `$tempTableName`", []);
			            DB::statement(<<<EOL
							CREATE TEMPORARY TABLE `$tempTableName` (
							  `broadcaster_id` bigint unsigned,
							  `location_id` bigint unsigned,
							  `repetitions` bigint unsigned,
							  `impressions` bigint unsigned
							  )
							EOL, []);

			            // We need a temporary table to store all performances of our locations
			            // We will then be able to query this table to merge the performances with their respective products

			            /** @var EloquentCollection<ContractReservation> $reservations */
			            foreach ($reservationsByBroadcaster as $broadcasterId => $reservations) {
				            // For each reservation, we try to find on which network it is playing
				            $reservationsNetworks = [];
				            foreach ($reservations as $reservation) {
					            foreach ($networks as $network) {
						            if (stripos($reservation->original_name, "_{$network->slug}_") !== false ||
							            stripos($reservation->original_name, "-{$network->slug}-") !== false) {
							            $reservationsNetworks[$reservation->external_id] = $network->getKey();
							            break;
						            }
					            }
				            }

				            /** @var BroadcasterOperator & BroadcasterReporting $broadcaster */
				            $broadcaster = BroadcasterAdapterFactory::makeForBroadcaster($broadcasterId);

				            // Make sure the broadcaster supports reporting
				            if (!$broadcaster->hasCapability(BroadcasterCapability::Reporting)) {
					            continue;
				            }

				            $reservationsPerformances = collect($broadcaster->getCampaignsPerformancesByLocations(
					            $reservations->map(fn(ContractReservation $reservation) => $reservation->toResource())
					                         ->all()
				            ));

				            DB::table($tempTableName)
				              ->insert($reservationsPerformances->map(fn(CampaignLocationPerformance $datum) => ([
					              "broadcaster_id" => $datum->location->broadcaster_id,
					              "location_id"    => $datum->location->external_id,
					              "repetitions"    => $datum->repetitions,
					              "impressions"    => $datum->impressions,
				              ]))->toArray());
			            }

			            // Match the stored performances with the flight's lines products
			            $results = collect(DB::select(<<<EOL
				  WITH `flight_products` AS
				         (SELECT `pl`.`product_id` as `id`
				            FROM `products_locations` `pl`
				                 JOIN `products` `p` ON `pl`.`product_id` = `p`.`id`
				                 JOIN `contracts_lines` `cl` ON `p`.`id` = `cl`.`product_id`
				           WHERE `flight_id` = ?)
				SELECT `pl`.`product_id`,
				       SUM(`rp`.`repetitions`) as repetitions,
				       SUM(`rp`.`impressions`) as impressions
				    FROM `$tempTableName` `rp`
				       JOIN `networks` `n` ON `rp`.`broadcaster_id` = `n`.`connection_id`
				       JOIN `locations` `l` ON `rp`.`location_id` = `l`.`external_id` AND `n`.`id` = `l`.`network_id`
				       LEFT JOIN `products_locations` `pl` ON `l`.`id` = `pl`.`location_id`
				 WHERE `pl`.`product_id` IN (SELECT `id` FROM `flight_products`)
				   GROUP BY `pl`.`product_id`
				EOL
				            , [$this->getKey()]));

			            $performances = $results->map(fn($r) => new FlightProductPerformanceDatum(
				            flight_id  : $this->getKey(),
				            product_id : $r->product_id,
				            repetitions: $r->repetitions,
				            impressions: $r->impressions,
			            ));

			            return $performances;
		            });
	}

	public function fillLinesPerformances() {
		$productsPerformances = $this->products_performances->keyBy("product_id");

		$this->lines->each(fn(ContractLine $line) => $line->performances = $productsPerformances[$line->product_id] ?? null);
	}

	/*
	|--------------------------------------------------------------------------
	| Locations
	|--------------------------------------------------------------------------
	*/

	/**
	 * @return Collection<Location>
	 */
	public function getLocationsAttribute(): Collection {
		$this->lines->load("product.locations");

		return $this->lines->flatMap(fn(ContractLine $line) => $line->product?->locations)->whereNotNull()->unique();
	}
}
