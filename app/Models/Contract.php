<?php

namespace Neo\Models;

use Illuminate\Database\Eloquent\Collection;
use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\BelongsTo;
use Illuminate\Database\Eloquent\Relations\HasMany;
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
 * @property integer                         $id
 * @property string                          $contract_id // ID of the contract has set by sales (not related to the actual ID of
 *           the contract inside Connect)
 * @property integer                         $client_id
 * @property integer                         $owner_id
 * @property array                           $data
 * @property integer                         $created_at
 * @property integer                         $updated_at
 *
 * @property Client                          $client
 * @property Actor                           $owner
 * @property Collection<ContractBurst>       $bursts
 * @property Collection<ContractReservation> $reservations
 */
class Contract extends Model {
    use HasFactory;

    protected $table = "contracts";

    protected $fillable = [
        "contract_id",
        "client_id",
        "owner_id"
    ];

    protected $casts = [
        "data" => "array",
    ];

    /*
    |--------------------------------------------------------------------------
    | Relations
    |--------------------------------------------------------------------------
    */

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


    /*
    |--------------------------------------------------------------------------
    | Relations
    |--------------------------------------------------------------------------
    */

    public function getPerformancesAttribute(): array {
        $config          = static::getConnectionConfig();
        $broadsignClient = new BroadsignClient($config);

        $reservations = $this->reservations;
        $performances = ReservablePerformance::byReservable($broadsignClient, $reservations->pluck('external_id')
                                                                                           ->values()
                                                                                           ->toArray());

        return $performances->values()->groupBy(["played_on", "reservable_id"])->all();
    }

    public function loadReservationsLocations(): void {
        $config          = static::getConnectionConfig();
        $broadsignClient = new BroadsignClient($config);

        foreach ($this->reservations as $reservation) {
            $bsLocations            = BSLocation::byReservable($broadsignClient, ["reservable_id" => $reservation->external_id])->pluck('id');
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
