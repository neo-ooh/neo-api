<?php
/*
 * Copyright 2020 (c) Neo-OOH - All Rights Reserved
 * Unauthorized copying of this file, via any medium is strictly prohibited
 * Proprietary and confidential
 * Written by Valentin Dufois <vdufois@neo-ooh.com>
 *
 * @neo/api - ContractFlight.php
 */

namespace Neo\Models;

use Illuminate\Database\Eloquent\Builder;
use Illuminate\Database\Eloquent\Collection as EloquentCollection;
use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\BelongsTo;
use Illuminate\Database\Eloquent\Relations\HasMany;
use Illuminate\Support\Carbon;
use Illuminate\Support\Collection;
use Illuminate\Support\Facades\Cache;
use Neo\Enums\ProductsFillStrategy;
use Neo\Modules\Broadcast\Exceptions\InvalidBroadcasterAdapterException;
use Neo\Modules\Broadcast\Models\Campaign;
use Neo\Modules\Broadcast\Models\Network;
use Neo\Modules\Broadcast\Models\ResourcePerformance;
use Neo\Modules\Broadcast\Services\BroadcasterAdapterFactory;
use Neo\Modules\Broadcast\Services\BroadcasterCapability;
use Neo\Modules\Broadcast\Services\BroadcasterOperator;
use Neo\Modules\Broadcast\Services\BroadcasterReporting;
use Neo\Modules\Broadcast\Services\Resources\CampaignPerformance;
use Neo\Resources\Contracts\FlightPerformanceDatum;
use Spatie\DataTransferObject\Exceptions\UnknownProperties;

/**
 * @property-read int                                $id
 * @property-read int                                $uid
 * @property-read int                                $contract_id
 * @property string                                  $name
 * @property Carbon                                  $start_date
 * @property Carbon                                  $end_date
 * @property string                                  $type
 * @property-read Carbon                             $created_at
 * @property-read Carbon                             $updated_at
 *
 * @property int                                     $expected_impressions
 * @property EloquentCollection<ContractLine>        $lines
 * @property EloquentCollection<ContractReservation> $reservations
 * @property Contract                                $contract
 * @property EloquentCollection<Campaign>            $campaigns
 *
 * @property Collection<FlightPerformanceDatum>      $performances
 */
class ContractFlight extends Model {
    protected $table = "contracts_flights";

    protected $primaryKey = "id";

    protected $fillable = [
        "contract_id",
        "uid",
        "name",
        "start_date",
        "end_date",
        "type",
    ];

    protected $dates = [
        "start_date",
        "end_date",
    ];

    public function lines(): HasMany {
        return $this->hasMany(ContractLine::class, "flight_id", "id");
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

    /*
    |--------------------------------------------------------------------------
    | Performances
    |--------------------------------------------------------------------------
    */

    public function getExpectedImpressionsAttribute() {
        return $this->lines()->whereHas('product', function (Builder $query) {
            $query->whereHas("category", function (Builder $query) {
                $query->where("fill_strategy", "=", ProductsFillStrategy::digital);
            });
        })->sum("impressions");
    }


    /**
     * Load the flight performances, aggregated from all sources – Campaigns & Reservations
     *
     * @return EloquentCollection<FlightPerformanceDatum>
     * @throws InvalidBroadcasterAdapterException
     * @throws UnknownProperties
     */
    public function getPerformancesAttribute(): Collection {
        // Contract can pull their performances from two different places:
        // The first place and most straightforward is form the campaign associated with the flight that are
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
                    flight_id: $this->getKey(),
                    network_id: $performance->data->network_id,
                    recorded_at: $performance->recorded_at,
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
     * Loads performances of attached reservations
     *
     * @return Collection<FlightPerformanceDatum>
     * @throws InvalidBroadcasterAdapterException|UnknownProperties
     */
    public function getReservationsPerformances(): Collection {
        return Cache::tags(["contract-performances"])->remember("contract-" . $this->id . "-performances", 3600 * 3, function () {
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
                        if (str_contains($reservation->original_name, "_{$network->slug}_") ||
                            str_contains($reservation->original_name, "-{$network->slug}-")) {
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
                                 ->toArray()
                ))->map(function (CampaignPerformance $performance) use ($reservationsNetworks): FlightPerformanceDatum {
                    return new FlightPerformanceDatum(
                        flight_id: $this->getKey(),
                        network_id: $reservationsNetworks[$performance->campaign->external_id] ?? null,
                        recorded_at: $performance->date,
                        repetitions: $performance->repetitions,
                        impressions: $performance->impressions,
                    );
                }));
            }

            return $performances;
        });
    }

}
