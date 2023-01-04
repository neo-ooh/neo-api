<?php
/*
 * Copyright 2022 (c) Neo-OOH - All Rights Reserved
 * Unauthorized copying of this file, via any medium is strictly prohibited
 * Proprietary and confidential
 * Written by Valentin Dufois <vdufois@neo-ooh.com>
 *
 * @neo/api - FetchCampaignsPerformancesCommand.php
 */

namespace Neo\Modules\Broadcast\Console\Commands;

use Illuminate\Console\Command;
use Neo\Modules\Broadcast\Jobs\Performances\FetchCampaignsPerformancesJob;

class FetchCampaignsPerformancesCommand extends Command {
    protected $signature = 'campaigns:fetch-performances {--network=null} {--lookback=3}';

    protected $description = "Fetch a campaign\'s performances";

    public function handle() {
        $network  = $this->option("network") === "null" ? null : $this->option("network");
        $lookback = $this->option("lookback") === "null" ? null : $this->option("lookback");

        $job = new FetchCampaignsPerformancesJob(
            networkId: $network,
            lookBack : $lookback,
        );

        $job->handle();

        return 0;
    }
}
