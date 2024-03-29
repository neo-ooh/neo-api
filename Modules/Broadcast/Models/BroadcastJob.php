<?php
/*
 * Copyright 2023 (c) Neo-OOH - All Rights Reserved
 * Unauthorized copying of this file, via any medium is strictly prohibited
 * Proprietary and confidential
 * Written by Valentin Dufois <vdufois@neo-ooh.com>
 *
 * @neo/api - BroadcastJob.php
 */

namespace Neo\Modules\Broadcast\Models;

use Carbon\Carbon;
use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\BelongsTo;
use Neo\Helpers\Relation;
use Neo\Models\Actor;
use Neo\Models\Traits\HasCreatedByUpdatedBy;
use Neo\Models\Traits\HasPublicRelations;
use Neo\Modules\Broadcast\Enums\BroadcastJobStatus;
use Neo\Modules\Broadcast\Enums\BroadcastJobType;
use Neo\Modules\Broadcast\Enums\BroadcastParameters;
use Neo\Modules\Broadcast\Jobs\Campaigns\DeleteCampaignJob;
use Neo\Modules\Broadcast\Jobs\Campaigns\PromoteCampaignJob;
use Neo\Modules\Broadcast\Jobs\Creatives\DeleteCreativeJob;
use Neo\Modules\Broadcast\Jobs\Creatives\ImportCreativeJob;
use Neo\Modules\Broadcast\Jobs\Creatives\UpdateCreativeJob;
use Neo\Modules\Broadcast\Jobs\Schedules\DeleteScheduleJob;
use Neo\Modules\Broadcast\Jobs\Schedules\PromoteScheduleJob;
use Neo\Modules\Broadcast\Services\ExternalCampaignDefinition;

/**
 * @property int                      $id
 * @property int                      $resource_id
 * @property BroadcastJobType         $type
 * @property Carbon                   $created_at
 * @property int|null                 $created_by
 * @property Carbon                   $scheduled_at
 * @property int                      $attempts
 * @property Carbon|null              $last_attempt_at
 * @property BroadcastJobStatus       $status
 * @property array|null               $payload
 * @property array|null               $last_attempt_result
 * @property int|null                 $updated_by
 *
 * @property BroadcastResourceDetails $resource
 */
class BroadcastJob extends Model {
    use HasCreatedByUpdatedBy;
    use HasPublicRelations;

    protected $table = "broadcast_jobs";

    protected $primaryKey = "id";

    protected $casts = [
        "type"                => BroadcastJobType::class,
        "created_at"          => "datetime",
        "scheduled_at"        => "datetime",
        "last_attempt_at"     => "datetime",
        "status"              => BroadcastJobStatus::class,
        "payload"             => "array",
        "last_attempt_result" => "array",
    ];

    public $timestamps = false;

    protected $fillable = [
        "resource_id",
        "type",
        "created_at",
        "scheduled_at",
        "payload",
    ];

    public function getDeletedByColumn(): string|null {
        return null;
    }

    public function getPublicRelations(): array {
        return [
            "resource" => Relation::make(load: "resource"),
            "creator"  => Relation::make(load: "creator"),
            "updator"  => Relation::make(load: "updator"),
        ];
    }

    protected static function booted(): void {
        parent::boot();

        static::creating(static function (BroadcastJob $job) {
            $job->created_at   = Carbon::now();
            $job->scheduled_at = Carbon::now()->addSeconds(param(BroadcastParameters::BroadcastJobsDelaySec));
            $job->status       = BroadcastJobStatus::Pending;
        });
    }

    public function resource(): BelongsTo {
        return $this->belongsTo(BroadcastResourceDetails::class, "resource_id", "id");
    }

    public function creator(): BelongsTo {
        return $this->belongsTo(Actor::class, "created_by", "id");
    }

    public function updator(): BelongsTo {
        return $this->belongsTo(Actor::class, "updated_by", "id");
    }

    public function endAttempt(BroadcastJobStatus $status, array|null $result): void {
        $this->status              = $status;
        $this->last_attempt_result = $result;
        $this->save();
    }

    public function execute(): void {
        switch ($this->type) {
            case BroadcastJobType::PromoteCampaign:
                PromoteCampaignJob::dispatch($this->resource_id, $this);
                break;
            case BroadcastJobType::DeleteCampaign:
                DeleteCampaignJob::dispatch($this->resource_id, is_array($this->payload) && $this->payload["resource_id"] ? $this->payload["resource_id"] : null, $this);
                break;
            case BroadcastJobType::PromoteSchedule:
                PromoteScheduleJob::dispatch($this->resource_id, $this->payload["representation"] ? ExternalCampaignDefinition::from($this->payload["representation"]) : null, $this);
                break;
            case BroadcastJobType::DeleteSchedule:
                DeleteScheduleJob::dispatch($this->resource_id, $this->payload["representation"] ? ExternalCampaignDefinition::from($this->payload["representation"]) : null, $this);
                break;
            case BroadcastJobType::ImportCreative:
                ImportCreativeJob::dispatch($this->resource_id, $this->payload["broadcasterId"], $this);
                break;
            case BroadcastJobType::UpdateCreative:
                UpdateCreativeJob::dispatch($this->resource_id, $this);
                break;
            case BroadcastJobType::DeleteCreative:
                DeleteCreativeJob::dispatch($this->resource_id, is_array($this->payload) && $this->payload["resource_id"] ? $this->payload["resource_id"] : null, $this);
        }
    }
}
