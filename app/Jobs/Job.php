<?php
/*
 * Copyright 2020 (c) Neo-OOH - All Rights Reserved
 * Unauthorized copying of this file, via any medium is strictly prohibited
 * Proprietary and confidential
 * Written by Valentin Dufois <vdufois@neo-ooh.com>
 *
 * @neo/api - Job.php
 */

/**
 * Ref: https://robertkabat.com/laravel-8-and-php8-jobs-with-lifecycle-hooks-and-auto-wiring/
 */

namespace Neo\Jobs;

use Exception;
use Illuminate\Bus\Queueable;
use Illuminate\Contracts\Queue\ShouldQueue;
use Illuminate\Foundation\Bus\Dispatchable;
use Illuminate\Queue\InteractsWithQueue;
use Illuminate\Queue\SerializesModels;
use Illuminate\Support\Facades\Log;

/**
 * This base class for jobs implements hooks for some of the lifecycle events of jobs
 *
 * > Call order is as follow:
 * > Job::beforeRun();
 * > Job::run();
 * > Job::onSuccess(mixed $result); // Called if `run()` finished without throwing, The `run()` return value is passed as argument
 * > Job::onSuccess(Exception $exception); // Called if `run()` has thrown. The thrown exception is passed as argument
 * > Job::finally(); // Called no matter the result of `run()`
 *
 * @template TRunReturn Run method return value, passed to the success callback
 */
abstract class Job implements ShouldQueue {
    use Dispatchable;
    use InteractsWithQueue;
    use Queueable;
    use SerializesModels;

    final public function handle(): void {
        $jobName = static::class;
        Log::debug("[JOB] $jobName: Begin");

        $this->beforeRun();

        try {
            $result = $this->run();

            Log::debug("[JOB] $jobName: Success", $result);
            $this->onSuccess($result);
        } catch (Exception $exception) {
            Log::debug("[JOB] $jobName: Failure");
            $this->onFailure($exception);
        } finally {
            $this->finally();
        }
    }

    /**
     * The logic of the job
     *
     * @return TRunReturn
     */
    abstract protected function run(): mixed;

    /**
     * Lifecycle method called before `run()`
     *
     * @return void
     */
    protected function beforeRun(): void {
    }

    /**
     * Lifecycle method called after `run()` finished without throwing
     *
     * @param TRunReturn $result `run()` return value
     * @return void
     */
    protected function onSuccess(mixed $result): void {
    }

    /**
     * Lifecycle method called after `run()` has thrown an exception
     *
     * @param Exception $exception Exception thrown in `run()`
     * @return void
     */
    protected function onFailure(Exception $exception): void {
    }

    /**
     * Lifecycle method called at the end of the job execution, no matter the result of `run()`
     *
     * @return void
     */
    protected function finally(): void {
    }
}
