<?php
/*
 * Copyright 2020 (c) Neo-OOH - All Rights Reserved
 * Unauthorized copying of this file, via any medium is strictly prohibited
 * Proprietary and confidential
 * Written by Valentin Dufois <Valentin Dufois>
 *
 * @neo/api - $file.filePath
 */

namespace Neo\Jobs;

use Illuminate\Bus\Queueable;
use Illuminate\Contracts\Queue\ShouldQueue;
use Illuminate\Foundation\Bus\Dispatchable;
use Illuminate\Queue\InteractsWithQueue;
use Illuminate\Queue\SerializesModels;
use Illuminate\Support\Facades\Mail;
use Neo\Mails\ActorWelcomeEmail;
use Neo\Models\Actor;
use Neo\Models\SignupToken;

/**
 * Class CreateUserLibrary
 * Create a library for the specified user
 *
 * @package Neo\Jobs
 *
 */
class SendWelcomeEmail implements ShouldQueue {
    use Dispatchable, InteractsWithQueue, Queueable, SerializesModels;

    /**
     * @var int ID of the user
     */
    protected int $actorID;


    /**
     * Create a new job instance.
     *
     * @param int $actorID ID of the user
     *
     * @return void
     */
    public function __construct (int $actorID) {
        $this->actorID = $actorID;
    }

    /**
     * Execute the job.
     *
     * @return void
     */
    public function handle (): void {
        /** @var Actor $actor */
        $actor = Actor::query()->findOrFail($this->actorID);
        $token = SignupToken::query()->find(["actor_id" => $this->actorID]);

        Mail::to($actor)->send(new ActorWelcomeEmail($token));
    }
}
