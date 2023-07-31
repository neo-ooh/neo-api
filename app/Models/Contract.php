<?php
/*
 * Copyright 2023 (c) Neo-OOH - All Rights Reserved
 * Unauthorized copying of this file, via any medium is strictly prohibited
 * Proprietary and confidential
 * Written by Valentin Dufois <vdufois@neo-ooh.com>
 *
 * @neo/api - Contract.php
 */

namespace Neo\Models;

use Carbon\Carbon;
use Illuminate\Database\Eloquent\Collection;
use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\BelongsTo;
use Illuminate\Database\Eloquent\Relations\BelongsToMany;
use Illuminate\Database\Eloquent\Relations\HasMany;
use Illuminate\Support\Facades\Storage;
use JsonException;
use Neo\Helpers\Relation;
use Neo\Models\Traits\HasPublicRelations;
use Neo\Modules\Broadcast\Services\BroadSign\Models\ReservablePerformance;
use Neo\Resources\Contracts\CPCompiledPlan;
use Staudenmeir\EloquentHasManyDeep\HasManyDeep;
use Staudenmeir\EloquentHasManyDeep\HasRelationships;
use Vinkla\Hashids\Facades\Hashids;

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
 * @property boolean                           $has_plan
 * @property Carbon                            $start_date
 * @property Carbon                            $end_date
 * @property integer                           $expected_impressions
 * @property integer                           $received_impressions
 * @property integer                           $created_at
 * @property integer                           $updated_at
 *
 * @property Client                            $client
 * @property Collection<ContractFlight>        $flights
 * @property Actor                             $owner
 * @property Advertiser|null                   $advertiser
 * @property Collection<ContractReservation>   $reservations
 * @property Collection<ReservablePerformance> $performances
 */
class Contract extends Model {
    use HasRelationships;
    use HasPublicRelations;

    protected $table = "contracts";

    protected $fillable = [
        "contract_id",
        "client_id",
        "salesperson_id",
    ];

    protected $casts = [
        "has_plan"   => "boolean",
        "start_date" => "date",
        "end_date"   => "date",
    ];

    protected function getPublicRelations(): array {
        return [
            "advertiser"   => "advertiser",
            "client"       => "client",
            "flights"      => "flights",
            "lines"        => Relation::make(load: 'flights.lines'),
            "locations"    => [
                fn(Contract $contract) => $contract->flights
                    ->append("locations"),
            ],
            "owner"        => "owner",
            "performances" => "append:performances",
            "plan"         => "append:stored_plan",
            "reservations" => "reservations",
            "salesperson"  => "salesperson",
            "screenshots"  => Relation::make(load: ["screenshots.product", "screenshots.location"]),
            "campaigns"    => "campaigns",
        ];
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

    public function reservations(): HasMany {
        return $this->hasMany(ContractReservation::class, "contract_id", "id");
    }

    public function flights(): HasMany {
        return $this->hasMany(ContractFlight::class, "contract_id", "id")->orderBy("start_date");
    }

    public function screenshots(): BelongsToMany {
        return $this->belongsToMany(Screenshot::class, "contracts_screenshots", "contract_id", "screenshot_id")
                    ->withPivot(["flight_id"]);
    }

    /**
     * @return HasManyDeep
     */
    public function campaigns(): HasManyDeep {
        return $this->hasManyDeepFromRelations([$this->flights(), (new ContractFlight())->campaigns()]);
    }

    /*
    |--------------------------------------------------------------------------
    | External Relations
    |--------------------------------------------------------------------------
    */

    public function getPerformancesAttribute() {
        return $this->flights->append("performances")
                             ->reduce(fn(\Illuminate\Support\Collection $acc, ContractFlight $flight) => $acc->push(...$flight->performances), collect());
    }

    /*
    |--------------------------------------------------------------------------
    | Attached plan
    |--------------------------------------------------------------------------
    */

    /**
     * File name of the stored plan. This does not check if a plan is present.
     *
     * @return string
     */
    public function getAttachedPlanName(): string {
        return $this->contract_id . ".ccp";
    }

    /**
     * Gives the path to the plan file, without the file name
     *
     * @return string
     */
    public function getContractStoragePath(): string {
        return "contracts/" . Hashids::encode($this->getKey()) . "/";
    }

    /**
     * Takes the raw content of the contract compiled plan and store it in the appropriate location
     *
     * @param string $rawPlan
     * @return void
     */
    public function storePlan(string $rawPlan): void {
        Storage::disk("public")->put($this->getContractStoragePath() . $this->getAttachedPlanName(), $rawPlan);
    }

    /**
     * @throws JsonException
     */
    public function getStoredPlanAttribute(): ?CPCompiledPlan {
        if (!$this->has_plan) {
            return null;
        }

        $encodedPlan = Storage::disk("public")->get($this->getContractStoragePath() . $this->getAttachedPlanName());

        if (!$encodedPlan) {
            return null;
        }

        // Decode and return the plan
        return CPCompiledPlan::from(json_decode(gzdecode(base64_decode($encodedPlan)), true, 512, JSON_THROW_ON_ERROR));
    }
}
