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


use Illuminate\Bus\Queueable;
use Illuminate\Foundation\Bus\Dispatchable;
use Illuminate\Queue\InteractsWithQueue;
use Illuminate\Queue\SerializesModels;
use Neo\Modules\Broadcast\Services\PiSignage\Models\Player;
use Neo\Modules\Broadcast\Services\PiSignage\PiSignageConfig;
use Neo\Services\Broadcast\PiSignage\Jobs\PiSignageJob;

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
