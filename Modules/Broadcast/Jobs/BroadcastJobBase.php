<?php
/*
 * Copyright 2020 (c) Neo-OOH - All Rights Reserved
 * Unauthorized copying of this file, via any medium is strictly prohibited
 * Proprietary and confidential
 * Written by Valentin Dufois <vdufois@neo-ooh.com>
 *
 * @neo/api - BroadcastJobBase.php
 */

namespace Neo\Modules\Broadcast\Jobs;

use Exception;
use Illuminate\Contracts\Queue\ShouldBeUnique;
use Illuminate\Contracts\Queue\ShouldBeUniqueUntilProcessing;
use Illuminate\Queue\Middleware\WithoutOverlapping;
use Neo\Jobs\Job;
use Neo\Modules\Broadcast\Enums\BroadcastJobStatus;
use Neo\Modules\Broadcast\Enums\BroadcastJobType;
use Neo\Modules\Broadcast\Models\BroadcastJob;

/**
 * @template TPayload of array
 * @extends Job<array|null>
 */
abstract class BroadcastJobBase extends Job implements ShouldBeUnique, ShouldBeUniqueUntilProcessing {
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
            $this->broadcastJob->save();
            return;
        }

        // Register the job
        $this->broadcastJob = new BroadcastJob([
            "resource_id" => $this->resourceId,
            "type"        => $this->type,
            "payload"     => $this->payload,
        ]);
        $this->broadcastJob->save();
    }

    /*
    |--------------------------------------------------------------------------
    | Job configuration
    |--------------------------------------------------------------------------
    */

    /**
     * Broadcast jobs should not be retried on fail
     *
     * @var int
     */
    public int $tries = 1;

    /**
     * Broadcast job should wait a bit before being processed as we want to prevent too many interactions with external services
     *
     * @var int
     */
    public $delay = 30;

    /**
     * Prevent having many job for the same resource queued at the same time
     *
     * @return int
     */
    public function uniqueId(): int {
        return $this->resourceId;
    }

    /**
     * Prevent multiple jobs for the same resource from executing at the same time
     *
     * @return array
     */
    public function middleware(): array {
        return [new WithoutOverlapping($this->resourceId)];
    }

    /*
    |--------------------------------------------------------------------------
    | Lifecycle
    |--------------------------------------------------------------------------
    */

    abstract protected function run(): array|null;

    public function getLastAttemptResult(): mixed {
        return $this->broadcastJob->last_attempt_result;
    }

    protected function beforeRun(): void {
        ++$this->broadcastJob->attempts;
        $this->broadcastJob->status = BroadcastJobStatus::Active;
        $this->broadcastJob->save();
    }

    /**
     * @param array|null $result
     * @return void
     */
    protected function onSuccess(mixed $result): void {
        $this->broadcastJob->status              = BroadcastJobStatus::Success;
        $this->broadcastJob->last_attempt_result = $result;
        $this->broadcastJob->save();
    }

    protected function onFailure(Exception $exception): void {
        $this->broadcastJob->endAttempt(BroadcastJobStatus::Failed, [
            "message"   => $exception->getMessage(),
            "locations" => $exception->getFile() . ":" . $exception->getLine(),
            "trace"     => $exception->getTrace()
        ]);
    }
}
