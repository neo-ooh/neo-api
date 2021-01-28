<?php
/*
 * Copyright 2020 (c) Neo-OOH - All Rights Reserved
 * Unauthorized copying of this file, via any medium is strictly prohibited
 * Proprietary and confidential
 * Written by Valentin Dufois <Valentin Dufois>
 *
 * @neo/api - Report.php
 */

namespace Neo\Models;

use Illuminate\Database\Eloquent\Builder;
use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\BelongsTo;
use Illuminate\Database\Eloquent\Relations\HasMany;
use Illuminate\Support\Collection;
use Neo\BroadSign\Models\Campaign as BSCampaign;
use Neo\BroadSign\Models\Customer;
use Neo\BroadSign\Models\Location as BSLocation;
use Neo\BroadSign\Models\ReservablePerformance;

/**
 * Neo\Models\ActorsLocations
 *
 * @property int                                                  id
 * @property int                                                  customer_id
 * @property string                                               contract_id
 * @property string                                               name
 * @property int                                                  created_by
 *
 * @property Actor                                                creator
 * @property Customer                                             customer
 *
 * @property \Illuminate\Database\Eloquent\Collection<BSCampaign> reservations
 * @property \Illuminate\Database\Eloquent\Collection<Burst> bursts
 *
 * @mixin Builder
 */
class Report extends Model {
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
    protected $table = 'reports';

    /**
     * The attributes that are mass assignable.
     *
     * @var array
     */
    protected $fillable = [
        "customer_id",
        "contract_id",
        "name",
        "created_by",
    ];


    /*
    |--------------------------------------------------------------------------
    | Relations
    |--------------------------------------------------------------------------
    */

    public function player(): BelongsTo {
        return $this->belongsTo(Player::class);
    }

    public function creator(): BelongsTo {
        return $this->belongsTo(Actor::class, 'created_by');
    }

    public function bursts(): HasMany {
        return $this->hasMany(Burst::class, 'report_id');
    }

    public function reservations(): HasMany {
        return $this->hasMany(ReportReservation::class, 'report_id');
    }

    /*
    |--------------------------------------------------------------------------
    | ***
    |--------------------------------------------------------------------------
    */

    public function getCustomerAttribute(): Customer {
        return Customer::get($this->customer_id);
    }

    public function getPerformancesAttribute(): array {
        $reservations = $this->reservations;
        $performances = ReservablePerformance::byReservable($reservations->pluck('broadsign_reservation_id')->values()->toArray());

        return $performances->values()->groupBy(["played_on", "reservable_id"])->all();
    }

    public function loadReservationsLocations(): void {
        foreach ($this->reservations as $reservation) {
            $bsLocations = BSLocation::byReservable(["reservable_id" => $reservation->broadsign_reservation_id])->pluck('id');
            $reservation->locations = Location::query()->whereIn("broadsign_display_unit", $bsLocations)->get();
        }
    }
}
