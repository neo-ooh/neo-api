<?php
/*
 * Copyright 2020 (c) Neo-OOH - All Rights Reserved
 * Unauthorized copying of this file, via any medium is strictly prohibited
 * Proprietary and confidential
 * Written by Valentin Dufois <vdufois@neo-ooh.com>
 *
 * @neo/api - CreateSignupToken.php
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
 * Class CreateSignupToken
 *
 * @package Neo\Jobs
 *
 */
class CreateSignupToken implements ShouldQueue {
    use Dispatchable, InteractsWithQueue, Queueable, SerializesModels;

    /**
     * @var int ID of the user
     */
    protected int $actorID;


    /**
     * Create a new job instance.
     *
     * @param int $actorID ID of the actor
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
        if(config("app.env") !== "production") {
            return;
        }

        $actor = Actor::query()->findOrFail($this->actorID);

        if ($actor->is_group) {
            return;
        }

        $token = new SignupToken([
            "actor_id" => $actor->id,
        ]);
        $token->save();

        Mail::to($actor)->send(new ActorWelcomeEmail($token));
    }
}
