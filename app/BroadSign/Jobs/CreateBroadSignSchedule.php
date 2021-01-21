<?php
/*
 * Copyright 2020 (c) Neo-OOH - All Rights Reserved
 * Unauthorized copying of this file, via any medium is strictly prohibited
 * Proprietary and confidential
 * Written by Valentin Dufois <Valentin Dufois>
 *
 * @neo/api - CreateBroadSignSchedule.php
 */

namespace Neo\BroadSign\Jobs;

use Facade\FlareClient\Http\Exceptions\BadResponse;
use Illuminate\Bus\Queueable;
use Illuminate\Contracts\Queue\ShouldQueue;
use Illuminate\Foundation\Bus\Dispatchable;
use Illuminate\Queue\InteractsWithQueue;
use Illuminate\Queue\SerializesModels;
use Neo\BroadSign\Models\Bundle as BSBundle;
use Neo\BroadSign\Models\LoopSlot;
use Neo\BroadSign\Models\Schedule as BSSchedule;
use Neo\Models\Actor;
use Neo\Models\Content;
use Neo\Models\Creative;
use Neo\Models\Schedule;

/**
 * Class CreateBroadSignSchedule
 * Create a schedule in BroadSign for the specified schedule in Access.
 *
 * @package Neo\Jobs
 */
class CreateBroadSignSchedule implements ShouldQueue {
    use Dispatchable, InteractsWithQueue, Queueable, SerializesModels;

    /**
     * @var int ID of the schedule to replicate in BroadSign
     */
    protected int $scheduleID;

    /**
     * @var int ID of the user who created the schedule
     */
    protected int $actorID;


    /**
     * Create a new job instance.
     *
     * @param int $scheduleID ID of the schedule to replicate in BroadSign
     * @param int $actorID    ID of the user who created the schedule
     *
     * @return void
     */
    public function __construct (int $scheduleID, int $actorID) {
        $this->scheduleID = $scheduleID;
        $this->actorID = $actorID;
    }

    /**
     * Execute the job.
     *
     * @return void
     */
    public function handle (): void {
        if(config("app.env") === "testing") {
            return;
        }

        // Get the access Schedules
        /** @var Schedule $schedule */
        $schedule = Schedule::query()->findOrFail($this->scheduleID);

        if ($schedule->broadsign_schedule_id || !$schedule->campaign->broadsign_reservation_id) {
            // This schedule already has a BroadSign ID, do nothing. OR
            // This schedule's campaign do not have a schedule ID, do nothing
            return;
        }

        // Get the associated content
        $content = $schedule->content;

        // Get the actor who made the schedule
        $actor = Actor::query()->findOrFail($this->actorID);

        // Load the broadsign loop slot for the campaign
        $loopSlot = LoopSlot::forCampaign([ "reservable_id" => $schedule->campaign->broadsign_reservation_id ])[0];

        // Create the schedule in broadsign
        $bsSchedule = new BSSchedule();
        $bsSchedule->day_of_week_mask = 127; // 01111111
        $bsSchedule->name = $schedule->campaign->name . " - " . $actor->email;
        $bsSchedule->parent_id = $loopSlot->id;
        $bsSchedule->reservable_id = $schedule->campaign->broadsign_reservation_id;
        $bsSchedule->rotation_mode = 0;
        $bsSchedule->schedule_group = 2;
        $bsSchedule->start_date = $schedule->start_date->toDateString();
        $bsSchedule->start_time = $schedule->start_date->toTimeString();
        $bsSchedule->end_date = $schedule->end_date->toDateString();
        $bsSchedule->end_time = $schedule->end_date->toTimeString();
        $bsSchedule->weight = 1;
        $bsSchedule->create();

        $schedule->broadsign_schedule_id = $bsSchedule->id;
        $schedule->save();

        // Create the broadsign bundle that will be broadcast by the schedule
        $this->makeBundle($content, $bsSchedule, $schedule);
    }

    /**
     * A BroadSign bundle is the equivalent of a content in Access. They are played by schedules. A bundle needs its
     * creative to have finished importing to be associated with it.
     *
     * @param Content    $content
     * @param BSSchedule $bsSchedule
     * @param Schedule   $schedule
     *
     * @return void
     */
    public function makeBundle (Content $content, BSSchedule $bsSchedule, Schedule $schedule): void {
        // Create a bundle
        $bundle = new BSBundle();
        $bundle->active = true;
        $bundle->allow_custom_duration = true;
        $bundle->fullscreen = true;
        $bundle->max_duration_msec = $schedule->campaign->display_duration * 1000;
        $bundle->name = $schedule->campaign->name . " (" . $schedule->campaign_id . ")" . "-" . $content->name ?? ("Bundle #" . $schedule->id);
        $bundle->parent_id = $bsSchedule->id;
        $bundle->create();

        // Assign the bundle ID to the content
        $schedule->broadsign_bundle_id = $bundle->id;
        $content->save();

        // Import the content's creatives
        /** @var Creative $creative */
        foreach ($content->creatives as $creative) {
            try {
                $bundle->associateCreative($creative->broadsign_ad_copy_id);
            } catch (BadResponse $e) {
                // Association failed, The creative / Ad-Copy may have not finished uploading, set a job to try again in a bit
                AssociateAdCopyWithBundle::dispatch($bundle->id, $creative->broadsign_ad_copy_id);
            }
        }
    }
}
