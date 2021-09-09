<?php
/*
 * Copyright 2020 (c) Neo-OOH - All Rights Reserved
 * Unauthorized copying of this file, via any medium is strictly prohibited
 * Proprietary and confidential
 * Written by Valentin Dufois <vdufois@neo-ooh.com>
 *
 * @neo/api - TurnPlayerTVOnOff.php
 */

namespace Neo\Services\Broadcast\PiSignage\Jobs\Players;


use GuzzleHttp\Psr7\Utils;
use Illuminate\Bus\Queueable;
use Illuminate\Contracts\Queue\ShouldBeUnique;
use Illuminate\Foundation\Bus\Dispatchable;
use Illuminate\Queue\InteractsWithQueue;
use Illuminate\Queue\SerializesModels;
use Neo\Models\Creative;
use Neo\Models\Schedule;
use Neo\Services\Broadcast\PiSignage\Jobs\PiSignageJob;
use Neo\Services\Broadcast\PiSignage\Models\Asset;
use Neo\Services\Broadcast\PiSignage\Models\Player;
use Neo\Services\Broadcast\PiSignage\PiSignageConfig;

/**
 * @package Neo\Jobs
 */
class TurnPlayerTVOnOff extends PiSignageJob {
    use Dispatchable, InteractsWithQueue, Queueable, SerializesModels;

    public function __construct(PiSignageConfig $config, protected string $playerId, protected bool $newState) {
        parent::__construct($config);
    }

    public function handle(): void {
        Player::toggleScreen($this->getAPIClient(), $this->playerId, $this->newState);
    }
}
