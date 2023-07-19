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
use Illuminate\Support\Facades\Storage;
use Neo\Resources\CampaignPlannerPlan\CampaignPlannerPlan;
use Vinkla\Hashids\Facades\Hashids;

/**
 * @property integer     $id
 * @property string      $uid
 * @property string      $name
 * @property integer     $actor_id
 * @property string      $version
 * @property string|null $contract
 * @property string|null $client_name
 * @property string|null $advertiser_name
 * @property Carbon      $created_at
 * @property Carbon      $updated_at
 *
 * @property string      $plan_path
 * @property string      $plan_url
 */
class CampaignPlannerSave extends Model {
    protected $table = "campaign_planner_saves";

    protected $primaryKey = "id";

    protected $appends = ["plan_url"];

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

    protected static function boot() {
        parent::boot();

        static::created(function (CampaignPlannerSave $save) {
            $save->uid = \Hashids::encode($save->getKey());
            $save->save();
        });
    }

    public function actor(): BelongsTo {
        return $this->belongsTo(Actor::class, "actor_id", "id");
    }

    public function getPlanPathAttribute() {
        return "plans/$this->uid.plan";
    }

    public function getPlanUrlAttribute() {
        return Storage::disk("public")->url($this->plan_path);
    }

    public function storePlan($planData) {
        clock()->event("Storing plan")->color("purple")->begin();
        Storage::disk("public")->put($this->plan_path, $planData);
        clock()->event("Storing plan")->end();
    }

    /**
     * Get the plan data
     *
     * @return CampaignPlannerPlan
     */
    public function getPlan() {
        $rawPlan = Storage::disk("public")->get($this->plan_path);

        return CampaignPlannerPlan::from(json_decode($rawPlan, true));
    }
}
