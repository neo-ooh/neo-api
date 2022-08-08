<?php
/*
 * Copyright 2020 (c) Neo-OOH - All Rights Reserved
 * Unauthorized copying of this file, via any medium is strictly prohibited
 * Proprietary and confidential
 * Written by Valentin Dufois <vdufois@neo-ooh.com>
 *
 * @neo/api - BroadcastJob.php
 */

namespace Neo\Modules\Broadcast\Models;

use Carbon\Carbon;
use Illuminate\Database\Eloquent\Model;
use Neo\Modules\Broadcast\Enums\BroadcastJobStatus;
use Neo\Modules\Broadcast\Enums\BroadcastJobType;

/**
 * @property int                $id
 * @property int                $resource_id
 * @property BroadcastJobType   $type
 * @property Carbon             $created_at
 * @property int                $attempts
 * @property Carbon|null        $last_attempt_at
 * @property BroadcastJobStatus $status
 * @property array|null         $payload
 * @property array|null         $last_attempt_result
 */
class BroadcastJob extends Model {
    protected $table = "broadcast_jobs";

    protected $primaryKey = "id";

    protected $casts = [
        "type"                => BroadcastJobType::class,
        "created_at"          => "date",
        "last_attempt_at"     => "date",
        "status"              => BroadcastJobStatus::class,
        "payload"             => "array",
        "last_attempt_result" => "array",
    ];

    public $timestamps = false;

    protected $fillable = [
        "resource_id",
        "type",
        "created_at",
        "payload"
    ];

    protected static function booted(): void {
        parent::boot();

        static::creating(static function (BroadcastJob $job) {
            $job->created_at = Carbon::now();
            $job->status     = BroadcastJobStatus::Pending;
        });
    }

    public function endAttempt(BroadcastJobStatus $status, array|null $result): void {
        $this->status              = $status;
        $this->last_attempt_result = $result;
        $this->save();
    }
}
