<?php
/*
 * Copyright 2023 (c) Neo-OOH - All Rights Reserved
 * Unauthorized copying of this file, via any medium is strictly prohibited
 * Proprietary and confidential
 * Written by Valentin Dufois <vdufois@neo-ooh.com>
 *
 * @neo/api - SendTwoFactorTokenJob.php
 */

namespace Neo\Jobs\Actors;

use Aloha\Twilio\Support\Laravel\Facade as Twilio;
use Illuminate\Bus\Queueable;
use Illuminate\Contracts\Queue\ShouldQueue;
use Illuminate\Foundation\Bus\Dispatchable;
use Illuminate\Queue\InteractsWithQueue;
use Illuminate\Queue\SerializesModels;
use Illuminate\Support\Facades\Mail;
use Neo\Mails\TwoFactorTokenEmail;
use Neo\Models\Actor;

/**
 * This job sends a two-factor auth token to its user using its preferred method (text message or email)
 */
class SendTwoFactorTokenJob implements ShouldQueue {
	use Dispatchable, InteractsWithQueue, Queueable, SerializesModels;

	public int $tries = 1;

	public function __construct(protected int $actorId) {
	}

	public function handle() {
		/** @var Actor $actor */
		$actor = Actor::findOrFail($this->actorId);

		if ($actor->two_fa_method === 'phone' && $actor->phone !== null) {
			Twilio::message($actor->phone->number, __("auth.two-factor-text", ["token" => $actor->twoFactorToken->token]));
			return;
		}

		Mail::to($actor)->send(new TwoFactorTokenEmail($actor, $actor->twoFactorToken));
	}
}
