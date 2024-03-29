<?php
/*
 * Copyright 2023 (c) Neo-OOH - All Rights Reserved
 * Unauthorized copying of this file, via any medium is strictly prohibited
 * Proprietary and confidential
 * Written by Valentin Dufois <vdufois@neo-ooh.com>
 *
 * @neo/api - BroadcastJobBase.php
 */

namespace Neo\Modules\Broadcast\Jobs;

use Carbon\Carbon;
use Illuminate\Contracts\Queue\ShouldBeUniqueUntilProcessing;
use Illuminate\Queue\Middleware\WithoutOverlapping;
use Neo\Jobs\Job;
use Neo\Modules\Broadcast\Enums\BroadcastJobStatus;
use Neo\Modules\Broadcast\Enums\BroadcastJobType;
use Neo\Modules\Broadcast\Enums\BroadcastParameters;
use Neo\Modules\Broadcast\Models\BroadcastJob;
use Throwable;

/**
 * @template TPayload of array
 * @extends Job<array|null>
 */
abstract class BroadcastJobBase extends Job implements ShouldBeUniqueUntilProcessing {
    protected BroadcastJob $broadcastJob;

    /**
     * @param BroadcastJobType  $type
     * @param int               $resourceId
     * @param TPayload          $payload
     * @param BroadcastJob|null $existingJob
     */
    public function __construct(protected BroadcastJobType $type, protected int $resourceId, protected mixed $payload = null, BroadcastJob|null $existingJob = null) {
        if ($existingJob) {
            $this->broadcastJob         = $existingJob;
            $this->broadcastJob->status = $this->broadcastJob->status === BroadcastJobStatus::Failed ? BroadcastJobStatus::PendingRetry : BroadcastJobStatus::Pending;
        } else {
            // Register the job
            $this->broadcastJob = new BroadcastJob([
                                                       "resource_id" => $this->resourceId,
                                                       "type"        => $this->type,
                                                       "payload"     => $this->payload,
                                                   ]);
        }

        $this->broadcastJob->save();
    }

    public function getBroadcastJob(): BroadcastJob {
        return $this->broadcastJob;
    }

    /*
    |--------------------------------------------------------------------------
    | Job configuration
    |--------------------------------------------------------------------------
    */

    /**
     * @var int Allow infinite retry. The `Neo\Jobs\Job` superclass ensure work execution will not result in an exception leaking
     *          Infinite retry makes sure our rate limiter will not incur a loss of jobs
     */
    public int $tries = 0;

    /**
     * The maximum number of unhandled exceptions to allow before failing.
     *
     * @var int
     */
    public int $maxExceptions = 1;


    /**
     * Prevent having many job for the same resource queued at the same time
     *
     * @return int
     */
    public function uniqueId(): int {
        return $this->resourceId;
    }

    /**
     * Broadcast jobs middlewares
     *
     * @return array
     */
    public function middleware(): array {
        return [
            // Prevent multiple broadcast jobs from executing at the same time
            (new WithoutOverlapping('broadcast-job'))->expireAfter(60),
        ];
    }

    /*
    |--------------------------------------------------------------------------
    | Lifecycle
    |--------------------------------------------------------------------------
    */

    protected function beforeRun(): bool {
        ++$this->broadcastJob->attempts;
        $this->broadcastJob->last_attempt_at = Carbon::now();

        // Check if broadcast jobs are enabled
        if (param(BroadcastParameters::BroadcastJobsEnabledBool)) {
            $this->broadcastJob->status = BroadcastJobStatus::Active;
            $this->broadcastJob->save();
            return true;
        }

        $this->broadcastJob->endAttempt(BroadcastJobStatus::Skipped, ["reason" => "broadcast.disabled-jobs"]);
        return false;
    }

    /**
     * @param array|null $result
     * @return void
     */
    protected function onSuccess(mixed $result): void {
        $this->broadcastJob->endAttempt(BroadcastJobStatus::Success, $result);
    }

    protected function onFailure(Throwable $exception): void {
        $this->broadcastJob->endAttempt(BroadcastJobStatus::Failed, [
            "message"   => $exception->getMessage(),
            "locations" => $exception->getFile() . ":" . $exception->getLine(),
            "trace"     => $exception->getTrace(),
        ]);
    }

    public function getLastAttemptResult(): mixed {
        return $this->broadcastJob->last_attempt_result;
    }
}
