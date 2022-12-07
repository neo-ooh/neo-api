<?php
/*
 * Copyright 2020 (c) Neo-OOH - All Rights Reserved
 * Unauthorized copying of this file, via any medium is strictly prohibited
 * Proprietary and confidential
 * Written by Valentin Dufois <vdufois@neo-ooh.com>
 *
 * @neo/api - ContractBurst.php
 */

namespace Neo\Models;

use Carbon\Carbon;
use Illuminate\Database\Eloquent\Collection;
use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\BelongsTo;
use Illuminate\Database\Eloquent\Relations\HasMany;
use Illuminate\Database\Eloquent\SoftDeletes;
use Neo\Jobs\Contracts\DeleteBurstJob;
use Neo\Modules\Broadcast\Models\Location;

/**
 * Class ContractBurst
 *
 * @package Neo\Models
 *
 * @property integer                        $id
 * @property integer                        $contract_id
 * @property integer                        $reservation_id
 * @property ?integer                       $actor_id
 * @property integer|null                   $location_id
 * @property Carbon                         $start_at
 * @property string                         $status
 * @property int                            $scale_percent
 * @property int                            $duration_ms
 * @property int                            $frequency_ms
 * @property Carbon                         $created_at
 * @property Carbon                         $updated_at
 * @property Carbon|null                    $deleted_at
 *
 * @property integer                        $expected_screenshots
 * @property integer                        $screenshots_count
 * @property Collection<ContractScreenshot> $screenshots
 *
 * @property Contract                       $contract
 * @property ContractReservation            $reservation
 * @property Actor                          $actor
 * @property Location|null                  $location
 */
class ContractBurst extends Model {
    use SoftDeletes;

    protected $table = "contracts_bursts";

    protected $dates = [
        "start_at",
    ];

    protected $fillable = [
        "id",
        "contract_id",
        "actor_id",
        "player_id",
        "location_id",
        "start_at",
        "status",
        "scale_percent",
        "duration_ms",
        "frequency_ms",
        "created_at",
        "updated_at",
    ];

    /**
     * The attributes that should always be loaded
     *
     * @var array
     */
    protected $appends = [
        "expected_screenshots",
    ];

    protected static function boot() {
        parent::boot();

        static::deleting(static function (ContractBurst $burst) {
            // On soft delete, trigger the job.
            // Don't trigger on force delete, as the job will be using it to fully delete the burst
            if (!$burst->isForceDeleting()) {
                DeleteBurstJob::dispatch($burst->getKey(), true);
            }
        });
    }

    /*
    |--------------------------------------------------------------------------
    | Relations
    |--------------------------------------------------------------------------
    */

    public function contract(): BelongsTo {
        return $this->belongsTo(Contract::class, "contract_id", "id");
    }

    public function reservation(): BelongsTo {
        return $this->belongsTo(ContractReservation::class, "reservation_id", "id")->orderBy("name");
    }

    public function actor(): BelongsTo {
        return $this->belongsTo(Actor::class, "actor_id", "id");
    }

    public function location(): BelongsTo {
        return $this->belongsTo(Location::class, "location_id", "id");
    }

    public function screenshots(): HasMany {
        return $this->hasMany(ContractScreenshot::class, "burst_id", "id")->orderBy("created_at");
    }

    /*
    |--------------------------------------------------------------------------
    | Burst Mechanism
    |--------------------------------------------------------------------------
    */


    public function getExpectedScreenshotsAttribute() {
        return ceil($this->duration_ms / $this->frequency_ms);
    }
}
