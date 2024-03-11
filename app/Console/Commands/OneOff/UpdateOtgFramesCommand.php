<?php
/*
 * Copyright 2024 (c) Neo-OOH - All Rights Reserved
 * Unauthorized copying of this file, via any medium is strictly prohibited
 * Proprietary and confidential
 * Written by Valentin Dufois <vdufois@neo-ooh.com>
 *
 * @neo/api - UpdateOtgFramesCommand.php
 */

namespace Neo\Console\Commands\OneOff;

use Illuminate\Console\Command;
use Neo\Modules\Broadcast\Services\BroadcasterAdapterFactory;
use Neo\Modules\Broadcast\Services\BroadSign\API\BroadSignClient;
use Neo\Modules\Broadcast\Services\BroadSign\BroadSignAdapter;
use Neo\Modules\Broadcast\Services\BroadSign\Models\DisplayUnit;
use Neo\Modules\Broadcast\Services\BroadSign\Models\ResourceCriteria;
use Neo\Modules\Broadcast\Services\BroadSign\Models\Skin;

class UpdateOtgFramesCommand extends Command {
    protected $signature = 'one-off:update-otg-frames';

    protected $description = 'Command description';

    public function handle(): void {
        // Load client manually
        /** @var BroadSignAdapter $adapter */
        $adapter = BroadcasterAdapterFactory::makeForBroadcaster(1);
        $client = new BroadSignClient($adapter->getConfig());

        $advertisingCriteriaId = 1143561;
        $backFrameCriteriaId = 919334405;

        $displayUnits = DisplayUnit::inContainer($client, 827973615)->where("active", "===", true)->pluck("id");

        foreach ($displayUnits as $displayUnitId) {
            $this->output->section("#$displayUnitId");

            // List the frames of the display unit
            $frames = Skin::byDisplayUnit($client, ["display_unit_id" => $displayUnitId]);

            $dayParts = [];

            // For each frame, we will load their criteria
            /** @var Skin $frame */
            foreach ($frames as $frame) {
                $criterias = ResourceCriteria::for($client, $frame->getKey());

                $criteriaIds = $criterias->pluck("criteria_id");

                if($criteriaIds->contains($backFrameCriteriaId)) {
                    $this->info("Updating back frame #$frame->id");
                    $frame->geometry_type = 2;
                    $frame->x = 0;
                    $frame->y = 0;
                    $frame->width = 1920;
                    $frame->height = 1080;
                    $frame->save();
                }

                if($criteriaIds->contains($advertisingCriteriaId)) {
                    $this->info("Updating main frame #$frame->id");
                    $frame->geometry_type = 2;
                    $frame->x = 480;
                    $frame->y = 0;
                    $frame->width = 1440;
                    $frame->height = 810;
                    $frame->save();
                }

//                if($criteriaIds->doesntContain($advertisingCriteriaId)) {
//                    // Frame is not main, delete it
//                    $this->info("Delete frame #$frame->id");
//                    $frame->active = false;
//                    $frame->save();
//                    continue;
//                }
//
//                // Move this frame frontward
//                $this->info("Updating frame #$frame->id");
//                $frame->z = 10;
//                $frame->save();
//
//                // Remember the day part
//                $dayParts[] = $frame->parent_id;
            }
//
//            // For each day part we stored, we will create a new frame
//            foreach ($dayParts as $dayPartId) {
//                $this->info("Creating new frame for Day Part #$dayPartId");
//                $frame = new Skin($client);
//                $frame->domain_id = $adapter->getConfig()->domainId;
//                $frame->geometry_type = 1;
//                $frame->height = 100;
//                $frame->width = 100;
//                $frame->name = "Back Frame";
//                $frame->interactivity_timeout = 0;
//			    $frame->interactivity_trigger_id = 0;
//                $frame->loop_policy_id = 925598024;
//                $frame->parent_id = $dayPartId;
//                $frame->screen_no = 1;
//                $frame->x = 0;
//                $frame->y = 0;
//                $frame->z = 0;
//                $frame->create();
//
//                // Attach criteria to the frame
//                $criteria = new ResourceCriteria($client);
//                $criteria->parent_id = $frame->getKey();
//                $criteria->domain_id = $adapter->getConfig()->domainId;
//                $criteria->criteria_id = 919334405;
//                $criteria->create();
//            }
        }
    }
}
