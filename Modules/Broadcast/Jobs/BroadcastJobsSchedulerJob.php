<?php
/*
 * Copyright 2023 (c) Neo-OOH - All Rights Reserved
 * Unauthorized copying of this file, via any medium is strictly prohibited
 * Proprietary and confidential
 * Written by Valentin Dufois <vdufois@neo-ooh.com>
 *
 * @neo/api - BroadcastJobsSchedulerJob.php
 */

namespace Neo\Modules\Broadcast\Jobs;

use Carbon\Carbon;
use Illuminate\Bus\Queueable;
use Illuminate\Contracts\Queue\ShouldQueue;
use Illuminate\Database\Eloquent\Builder;
use Illuminate\Foundation\Bus\Dispatchable;
use Illuminate\Queue\InteractsWithQueue;
use Illuminate\Queue\SerializesModels;
use Neo\Modules\Broadcast\Enums\BroadcastJobStatus;
use Neo\Modules\Broadcast\Models\BroadcastJob;

/**
 * This job list all Broadcast jobs that are pending and whose scheduled datetime is passed, and sends them for immediate
 * execution to the Laravel dispatcher.
 */
class BroadcastJobsSchedulerJob implements ShouldQueue {
	use Dispatchable, InteractsWithQueue, Queueable, SerializesModels;

	public function handle() {
		// List jobs that have been scheduled for up to know, that are still marked as `Pending`
		$jobs = BroadcastJob::query()->where(function (Builder $query) {
			$query->where("status", "=", BroadcastJobStatus::Pending)
			      ->orWhere("status", "=", BroadcastJobStatus::PendingRetry);
		})->where("scheduled_at", "<=", Carbon::now())
		                    ->get();

		/**
		 * @var BroadcastJob $job
		 */
		foreach ($jobs as $job) {
			$job->execute();
		}
	}
}
