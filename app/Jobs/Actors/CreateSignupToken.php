<?php
/*
 * Copyright 2023 (c) Neo-OOH - All Rights Reserved
 * Unauthorized copying of this file, via any medium is strictly prohibited
 * Proprietary and confidential
 * Written by Valentin Dufois <vdufois@neo-ooh.com>
 *
 * @neo/api - CreateSignupToken.php
 */

namespace Neo\Jobs\Actors;

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
	public function __construct(int $actorID) {
		$this->actorID = $actorID;
	}

	/**
	 * Execute the job.
	 *
	 * @return void
	 */
	public function handle(): void {
		if (config("app.env") !== "production") {
			return;
		}

		// Load the user to make sure it is not a group and indeed is a human
		$actor = Actor::query()->findOrFail($this->actorID);

		if ($actor->is_group) {
			return;
		}

		// Delete any leftover signup token for this user
		SignupToken::query()->where("actor_id", "=", $actor->id)->delete();

		// And create a new one
		$token = new SignupToken([
			                         "actor_id" => $actor->id,
		                         ]);
		$token->save();

		// Send an email with the token to the user.
		Mail::to($actor)->send(new ActorWelcomeEmail($token));
	}
}
