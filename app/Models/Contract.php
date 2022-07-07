<?php
/*
 * Copyright 2020 (c) Neo-OOH - All Rights Reserved
 * Unauthorized copying of this file, via any medium is strictly prohibited
 * Proprietary and confidential
 * Written by Valentin Dufois <vdufois@neo-ooh.com>
 *
 * @neo/api - Contract.php
 */

namespace Neo\Models;

use Illuminate\Database\Eloquent\Collection;
use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\BelongsTo;
use Illuminate\Database\Eloquent\Relations\HasMany;
use Illuminate\Database\Eloquent\Relations\HasManyThrough;
use Illuminate\Support\Facades\Cache;
use Neo\Services\Broadcast\Broadcast;
use Neo\Services\Broadcast\Broadcaster;
use Neo\Services\Broadcast\BroadSign\API\BroadsignClient;
use Neo\Services\Broadcast\BroadSign\BroadSignConfig;
use Neo\Services\Broadcast\BroadSign\Models\Location as BSLocation;
use Neo\Services\Broadcast\BroadSign\Models\ReservablePerformance;
use RuntimeException;

/**
 * Class Contract
 *
 * @package Neo\Models
 *
 * @property integer                           $id
 * @property string                            $contract_id // ID of the contract has set by sales (not related to the actual ID
 *           of the contract inside Connect)
 * @property integer                           $external_id
 * @property integer                           $client_id
 * @property integer                           $salesperson_id
 * @property integer                           $advertiser_id
 * @property \Carbon\Carbon                    $start_date
 * @property \Carbon\Carbon                    $end_date
 * @property integer                           $expected_impressions
 * @property integer                           $received_impressions
 * @property integer                           $created_at
 * @property integer                           $updated_at
 *
 * @property Client                            $client
 * @property Collection<ContractFlight>        $flights
 * @property Actor                             $owner
 * @property Collection<ContractBurst>         $bursts
 * @property Collection<ContractReservation>   $reservations
 * @property Collection<ReservablePerformance> $performances
 */
class Contract extends Model {
    protected $table = "contracts";

    protected $fillable = [
        "contract_id",
        "client_id",
        "salesperson_id"
    ];

    protected $dates = [
        "start_date",
        "end_date",
    ];

    protected static function boot() {
        parent::boot(); // TODO: Change the autogenerated stub

        static::deleting(function (Contract $contract) {
            foreach ($contract->bursts as $burst) {
                $burst->delete();
            }
        });
    }

    /*
    |--------------------------------------------------------------------------
    | Relations
    |--------------------------------------------------------------------------
    */

    public function salesperson(): BelongsTo {
        return $this->belongsTo(Actor::class, "salesperson_id", "id");
    }

    public function advertiser(): BelongsTo {
        return $this->belongsTo(Advertiser::class, "advertiser_id", "id");
    }

    public function client(): BelongsTo {
        return $this->belongsTo(Client::class, "client_id", "id");
    }

    public function owner(): BelongsTo {
        return $this->belongsTo(Actor::class, "owner_id", "id");
    }

    public function bursts(): HasMany {
        return $this->hasMany(ContractBurst::class, "contract_id", "id");
    }

    public function reservations(): HasMany {
        return $this->hasMany(ContractReservation::class, "contract_id", "id");
    }

    public function data(): HasMany {
        return $this->hasMany(ContractNetworkData::class, "contract_id", "id");
    }

    public function flights(): HasMany {
        return $this->hasMany(ContractFlight::class, "contract_id", "id")->orderBy("start_date");
    }

    public function screenshots(): HasManyThrough {
        return $this->hasManyThrough(ContractScreenshot::class, ContractBurst::class, 'contract_id', 'burst_id');
    }

    public function validated_screenshots(): HasManyThrough {
        return $this->hasManyThrough(ContractScreenshot::class, ContractBurst::class, 'contract_id', 'burst_id')
                    ->where("is_locked", "=", true);
    }

    /*
    |--------------------------------------------------------------------------
    | External Relations
    |--------------------------------------------------------------------------
    */

    public function getContractPerformancesCacheKey(): string {
        return "contract-" . $this->id . "-performances";
    }

    public function getPerformancesAttribute() {
        return Cache::tags(["contract-performances"])->remember($this->getContractPerformancesCacheKey(), 3600 * 3, function () {
            $config          = static::getConnectionConfig();
            $broadsignClient = new BroadsignClient($config);

            $reservations = $this->reservations;

            if ($reservations->isEmpty()) {
                return collect();
            }

            $performances = ReservablePerformance::byReservable(
                $broadsignClient,
                $reservations->pluck('external_id')
                             ->values()
                             ->toArray());

            return $performances->values();
        });
    }


    /*
    |--------------------------------------------------------------------------
    | Additional Attributes
    |--------------------------------------------------------------------------
    */

    public function loadReservationsLocations(): void {
        $config          = static::getConnectionConfig();
        $broadsignClient = new BroadsignClient($config);

        foreach ($this->reservations as $reservation) {
            $bsLocations            = BSLocation::byReservable($broadsignClient, ["reservable_id" => $reservation->external_id])
                                                ->pluck('id');
            $reservation->locations = Location::query()->whereIn("external_id", $bsLocations)->get();
        }
    }

    /*
    |--------------------------------------------------------------------------
    | Utils
    |--------------------------------------------------------------------------
    */

    public static function getConnectionConfig(): BroadSignConfig {
        // As any requests with Broadsign requires a valid connection, and valid network, we will use any network already setup with the connection specified for the network.
        // Also, since all the contracts work is reading information, we don't care about the customer, container and tracking information specified for the network
        /** @var ?Network $network */
        $network = Network::query()->where("connection_id", "=", Param::find("CONTRACTS_CONNECTION")->value)->first();

        if (!$network) {
            throw new RuntimeException("No network setup for the contracts connection");
        }


        if ($network->broadcaster_connection->broadcaster !== Broadcaster::BROADSIGN) {
            throw new RuntimeException("Contracts connection MUST be aa broadsign connection");
        }

        return Broadcast::network($network->id)->getConfig();
    }
}
