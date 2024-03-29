<?php
/*
 * Copyright 2023 (c) Neo-OOH - All Rights Reserved
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
use Throwable;

/**
 * This base class for jobs implements hooks for some lifecycle events of jobs
 *
 * > Call order is as follow:
 * > Job::beforeRun();
 * > Job::run(); // Called if `beforeRun()` returned true
 * > Job::onSuccess(mixed $result); // Called if `run()` finished without throwing, The `run()` return value is passed as argument
 * > Job::onSuccess(Exception $exception); // Called if `run()` has thrown. The thrown exception is passed as argument
 * > Job::finally(); // Called no matter the result of `beforeRun()` and `run()`
 *
 * @template TRunReturn Run method return value, passed to the success callback
 */
abstract class Job implements ShouldQueue {
    use Dispatchable;
    use InteractsWithQueue;
    use Queueable;
    use SerializesModels;

    final public function handle(): void {
        $jobID = uniqid('', true);
        clock()->event(get_class($this))
               ->color("orange")
               ->name($jobID)
               ->begin();

        if (!$this->beforeRun()) {
            $this->finally();
            clock()->event($jobID)->end();
            return;
        }

        try {
            $result = $this->run();

            $this->onSuccess($result);
        } catch (Throwable $exception) {
            $this->onFailure($exception);
        } finally {
            $this->finally();
            clock()->event($jobID)->end();
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
     * @return bool Returning false here short-circuit the job
     */
    protected function beforeRun(): bool {
        return true;
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
    protected function onFailure(Throwable $exception): void {
    }

    /**
     * Lifecycle method called at the end of the job execution, no matter the result of `run()`
     *
     * @return void
     */
    protected function finally(): void {
    }
}
