<?php
/*
 * Copyright 2020 (c) Neo-OOH - All Rights Reserved
 * Unauthorized copying of this file, via any medium is strictly prohibited
 * Proprietary and confidential
 * Written by Valentin Dufois <vdufois@neo-ooh.com>
 *
 * @neo/api - AddParentIdToBroadSignCreativesCommand.php
 */

namespace Neo\Console\Commands\Hotfixes;

use Illuminate\Console\Command;
use Neo\Models\Creative;
use Neo\Models\CreativeExternalId;
use Neo\Services\Broadcast\Broadcast;
use Neo\Services\Broadcast\BroadSign\API\BroadsignClient;
use Neo\Services\Broadcast\BroadSign\BroadSignConfig;

class AddParentIdToBroadSignCreativesCommand extends Command {
    protected $signature = 'one-off:add-creatives-parent-id';

    protected $description = 'Fill in the parent_id parameter for creatives';

    public function handle() {
        $creatives = Creative::query()->with("external_ids")->lazy(100);

        foreach ($creatives as $creative) {
            $this->getOutput()->write("Creative #$creative->id...");

            /** @var CreativeExternalId $external_id */
            foreach ($creative->external_ids as $external_id) {
                $networkConfig = Broadcast::network($external_id->network_id);

                if (!($networkConfig instanceof BroadSignConfig)) {
                    continue;
                }

                $client   = new BroadsignClient($networkConfig);
                $creative = \Neo\Services\Broadcast\BroadSign\Models\Creative::get($client, $external_id->external_id);

                $creative->container_id = $networkConfig->containerId;
                $creative->parent_id    = $networkConfig->customerId;
//                $creative->save();
            }

            $this->getOutput()->writeln(" OK!");
        }
    }
}
