<?php
/*
 * Copyright 2023 (c) Neo-OOH - All Rights Reserved
 * Unauthorized copying of this file, via any medium is strictly prohibited
 * Proprietary and confidential
 * Written by Valentin Dufois <vdufois@neo-ooh.com>
 *
 * @neo/api - RetryPendingBroadcastJobsJob.php
 */

namespace Neo\Modules\Broadcast\Jobs\Chores;

use Carbon\Carbon;
use Illuminate\Bus\Queueable;
use Illuminate\Contracts\Queue\ShouldQueue;
use Illuminate\Foundation\Bus\Dispatchable;
use Illuminate\Queue\InteractsWithQueue;
use Illuminate\Queue\SerializesModels;
use Neo\Modules\Broadcast\Models\BroadcastJob;

/**
 * Sometimes, scheduled Broadcast Jobs are never executed. This job takes broadcast job that are more than half an hour old and
 * whose status is still pending, and retry them.
 *
 * It would be interesting to track how many jobs still needs to be forced this way, in order to assess if this job is still
 * useful.
 */
class RetryPendingBroadcastJobsJob implements ShouldQueue {
	use Dispatchable, InteractsWithQueue, Queueable, SerializesModels;

	protected $backoffMinutes = 30;

	public function handle() {
		$jobs = BroadcastJob::query()->where("created_at", "<", Carbon::now()->subMinutes(30))
		                    ->whereNull("last_attempt_at")
		                    ->get();

		/** @var BroadcastJob $job */
		foreach ($jobs as $job) {
			$job->execute();
		}
	}
}
