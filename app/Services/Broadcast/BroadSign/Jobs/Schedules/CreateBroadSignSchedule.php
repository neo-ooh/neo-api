<?php
/*
 * Copyright 2020 (c) Neo-OOH - All Rights Reserved
 * Unauthorized copying of this file, via any medium is strictly prohibited
 * Proprietary and confidential
 * Written by Valentin Dufois <vdufois@neo-ooh.com>
 *
 * @neo/api - CreateBroadSignSchedule.php
 */

namespace Neo\Services\Broadcast\BroadSign\Jobs\Schedules;

use Illuminate\Bus\Queueable;
use Illuminate\Foundation\Bus\Dispatchable;
use Illuminate\Queue\InteractsWithQueue;
use Illuminate\Queue\SerializesModels;
use Neo\Models\Actor;
use Neo\Models\Content;
use Neo\Models\Creative;
use Neo\Models\Schedule;
use Neo\Services\Broadcast\BroadSign\BroadSignConfig;
use Neo\Services\Broadcast\BroadSign\Jobs\BroadSignJob;
use Neo\Services\Broadcast\BroadSign\Jobs\Creatives\AssociateAdCopyWithBundle;
use Neo\Services\Broadcast\BroadSign\Jobs\Creatives\ImportCreativeInBroadSign;
use Neo\Services\Broadcast\BroadSign\Models\Bundle as BSBundle;
use Neo\Services\Broadcast\BroadSign\Models\LoopSlot;
use Neo\Services\Broadcast\BroadSign\Models\Schedule as BSSchedule;
use Symfony\Component\Translation\Exception\InvalidResourceException;

/**
 * Class CreateBroadSignSchedule
 * Create a schedule in BroadSign for the specified schedule in Access.
 *
 * @package Neo\Jobs
 */
class CreateBroadSignSchedule extends BroadSignJob {
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
    public function __construct(BroadSignConfig $config, int $scheduleID, int $actorID) {
        parent::__construct($config);

        $this->scheduleID = $scheduleID;
        $this->actorID    = $actorID;
    }

    /**
     * Execute the job.
     *
     * @return void
     */
    public function handle(): void {
        // Get the access Schedules
        /** @var Schedule $schedule */
        $schedule = Schedule::query()->findOrFail($this->scheduleID);

        if ($schedule->external_id_2 || !$schedule->campaign) {
            // This schedule already has a BroadSign ID, do nothing. OR
            // This schedule's campaign do not have a schedule ID, do nothing
            return;
        }

        // Make sure the campaign has a an external id, if not, release and retry later
        if ($schedule->campaign->external_id === null) {
            // Wait 30s before trying again
            $this->release(30);
            return;
        }

        // Get the associated content
        $content = $schedule->content;

        // Get the actor who made the schedule
        $actor = Actor::query()->findOrFail($this->actorID);

        // Load the broadsign loop slot for the campaign
        $loopSlot = LoopSlot::forCampaign($this->getAPIClient(), ["reservable_id" => $schedule->campaign->external_id])[0];

        if ($loopSlot === null) {
            throw new InvalidResourceException("Could not retrieve the loop slot for the reservation " . $schedule->campaign->external_id . ". ");
        }

        // We need to make sure the end time is not after 23:59:00
        $endTime = $schedule->end_date;
        if ($endTime->isAfter($endTime->copy()->setTime(23, 59, 00))) {
            $endTime = $endTime->setTime(23, 59, 00);
        }

        // Create the schedule in broadsign
        $bsSchedule                   = new BSSchedule($this->getAPIClient());
        $bsSchedule->day_of_week_mask = 127; // 01111111
        $bsSchedule->name             = $schedule->campaign->name . " - " . $actor->email;
        $bsSchedule->parent_id        = $loopSlot->id;
        $bsSchedule->reservable_id    = $schedule->campaign->external_id;
        $bsSchedule->rotation_mode    = 0;
        $bsSchedule->schedule_group   = 2;
        $bsSchedule->start_date       = $schedule->start_date->toDateString();
        $bsSchedule->start_time       = $schedule->start_date->setSecond(0)->toTimeString();
        $bsSchedule->end_date         = $schedule->end_date->toDateString();
        $bsSchedule->end_time         = $endTime->toTimeString();
        $bsSchedule->weight           = 1;
        $bsSchedule->create();

        $schedule->external_id_2 = $bsSchedule->id;
        $schedule->save();

        // Create the broadsign bundle that will be broadcast by the schedule
        $this->makeBundle($content, $bsSchedule, $schedule);

        // Trigger an update of the schedule status
        UpdateBroadSignScheduleStatus::dispatch($this->config, $schedule->id);
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
    public function makeBundle(Content $content, BSSchedule $bsSchedule, Schedule $schedule): void {
        // Create a bundle
        $bundle                        = new BSBundle($this->getAPIClient());
        $bundle->active                = true;
        $bundle->allow_custom_duration = true;
        $bundle->auto_synchronized     = true;
        $bundle->fullscreen            = $content->layout->is_fullscreen;
        $bundle->max_duration_msec     = $schedule->campaign->display_duration * 1000;
        $bundle->name                  = $schedule->campaign->name . " (" . $schedule->campaign_id . ")" . "-" . $content->name ?? ("Bundle #" . $schedule->id);
        $bundle->parent_id             = $bsSchedule->id;

        if ($content->layout->trigger_id) {
            $bundle->auto_synchronized   = false;
            $bundle->trigger_category_id = $content->layout->trigger->broadsign_trigger_id;
        }

        if ($content->layout->separation_id) {
            $bundle->category_id = $content->layout->separation->broadsign_separation_id;
        }

        $bundle->create();

        // Assign the bundle ID to the content
        $schedule->external_id_1 = $bundle->id;
        $schedule->save();

        // Import the content's creatives
        /** @var Creative $creative */
        foreach ($content->creatives as $creative) {
            // If the creative has no ad_copy ID, it needs to be imported in BroadSign
            if ($creative->getExternalId($this->config->networkID) === null) {
                ImportCreativeInBroadSign::withChain([new AssociateAdCopyWithBundle($this->config, $bundle->id, $creative->id)])
                                         ->dispatch($this->config, $creative->id);
                continue;
            }

            // Apply a 120 seconds delay to the association as BroadSign returns an error if the Ad Copy hasn't finished uploading.
            AssociateAdCopyWithBundle::dispatch($this->config, $bundle->id, $creative->id)->delay(120);
        }
    }
}
