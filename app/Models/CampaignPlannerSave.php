<?php
/*
 * Copyright 2023 (c) Neo-OOH - All Rights Reserved
 * Unauthorized copying of this file, via any medium is strictly prohibited
 * Proprietary and confidential
 * Written by Valentin Dufois <vdufois@neo-ooh.com>
 *
 * @neo/api - CampaignPlannerSave.php
 */

namespace Neo\Models;

use Carbon\Carbon;
use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\BelongsTo;
use Neo\Models\Traits\HasView;
use Vinkla\Hashids\Facades\Hashids;

/**
 * @property integer $id
 * @property string  $name
 * @property integer $actor_id
 * @property Carbon  $created_at
 * @property Carbon  $updated_at
 *
 * @property string  $uid
 * @property array   $data
 */
class CampaignPlannerSave extends Model {
    use HasView;

    protected $table = "campaign_planner_saves_view";

    protected $write_table = "campaign_planner_saves";

    protected $primaryKey = "id";

    protected $appends = ["uid"];

    protected $casts = [
        "data" => "array",
    ];

    protected $fillable = [
        "name",
        "actor_id",
        "data",
    ];

    public function resolveRouteBinding($value, $field = null) {
        $id = Hashids::decode($value)[0] ?? null;
        return $this->newQuery()->findOrFail($id);
    }

    public function actor(): BelongsTo {
        return $this->belongsTo(Actor::class, "actor_id", "id");
    }

    public function getUidAttribute(): string {
        return Hashids::encode($this->id);
    }

    /**
     * Get the plan data
     *
     * @return array
     */
    public function getPlan() {
        return CampaignPlannerSave::query()
                                  ->from($this->getWriteTable())
                                  ->where("id", "=", $this->getKey())
                                  ->first()
            ->data;
    }
}
