<?php
/*
 * Copyright 2020 (c) Neo-OOH - All Rights Reserved
 * Unauthorized copying of this file, via any medium is strictly prohibited
 * Proprietary and confidential
 * Written by Valentin Dufois <vdufois@neo-ooh.com>
 *
 * @neo/api - ImportCreativeInBroadSign.php
 */

namespace Neo\Services\Broadcast\BroadSign\Jobs\Creatives;

use DateInterval;
use Exception;
use Illuminate\Bus\Queueable;
use Illuminate\Contracts\Queue\ShouldBeUnique;
use Illuminate\Foundation\Bus\Dispatchable;
use Illuminate\Queue\InteractsWithQueue;
use Illuminate\Queue\SerializesModels;
use Neo\Models\Creative;
use Neo\Services\Broadcast\BroadSign\BroadSignConfig;
use Neo\Services\Broadcast\BroadSign\Jobs\BroadSignJob;
use Neo\Services\Broadcast\BroadSign\Models\Creative as BSCreative;

/**
 * Class ImportCreative
 *
 * @package Neo\Jobs
 *
 * Imports the specified creative in BroadSign and register its BroadSign ID.
 */
class ImportCreativeInBroadSign extends BroadSignJob implements ShouldBeUnique {
    use Dispatchable, InteractsWithQueue, Queueable, SerializesModels;

    protected int $creativeID;
    protected string $creativeName;

    public function uniqueId(): int {
        return $this->creativeID;
    }

    /**
     * Create a new job instance.
     *
     * @param int $creativeID ID of the creative to import
     */
    public function __construct(BroadSignConfig $config, int $creativeID) {
        parent::__construct($config);

        $this->creativeID = $creativeID;
    }

    /**
     * Execute the job.
     *
     * @return void
     * @throws Exception
     */
    public function handle(): void {
        /** @var Creative $creative */
        $creative = Creative::query()->findOrFail($this->creativeID);

        if ($creative->getExternalId($this->config->networkID) !== null) {
            // This creative already has an id, do nothing.
            return;
        }

        // Depending on the creative type, we performed different operations
        switch ($creative->type) {
            case Creative::TYPE_STATIC:
                $this->importStaticCreative($creative);
                break;
            case Creative::TYPE_DYNAMIC:
                $this->importDynamicCreative($creative);
                break;
        }

        // Schedule job to target the creative accordingly
        TargetCreative::dispatch($this->config, $creative->id);
    }

    /**
     * @throws Exception
     */
    protected function importStaticCreative(Creative $creative): void {
        $attributes = "width={$creative->frame->width}\n";
        $attributes .= "height={$creative->frame->height}\n";

        if ($creative->properties->extension === "mp4") {
            $interval   = new DateInterval("PT" . $creative->content->duration . "S");
            $attributes .= "duration={$interval->format("H:I:S")}\n";
        }

        $bsCreative             = new BSCreative($this->getAPIClient());
        $bsCreative->attributes = $attributes;
        $bsCreative->name       = $creative->owner->email . " - " . $creative->original_name;
        $bsCreative->container_id       = $this->config->adCopiesContainerId;
        $bsCreative->parent_id  = $this->config->customerId;
        $bsCreative->url        = $creative->properties->file_url;

        $creative->external_ids()->create([
            "network_id" => $this->config->networkID,
            "external_id" => $bsCreative->import(),
        ]);
    }

    protected function importDynamicCreative(Creative $creative): void {
        $attributes = [
            "expire_on_empty_remote_dir" => "false",                        // Do not expire if connection lost
            "io_strategy"                => "esf",                                         // ???
            "source"                     => $creative->properties->url,                         // URL to the resource
            "source_append_id"           => "false",                                  // Append player ID to url (no)
            "source_expiry"              => "0",                                         // Not sure
            "source_refresh"             => $creative->properties->refresh_interval     // URL refresh interval (minutes)
        ];

        $bsCreativeId = BSCreative::makeDynamic($this->getAPIClient(), $creative->owner->email . " - " . $creative->original_name, $attributes);

        $creative->external_ids()->create([
            "network_id" => $this->config->networkID,
            "external_id" => $bsCreativeId,
        ]);
        $creative->save();
    }
}

