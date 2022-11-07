<?php
/*
 * Copyright 2022 (c) Neo-OOH - All Rights Reserved
 * Unauthorized copying of this file, via any medium is strictly prohibited
 * Proprietary and confidential
 * Written by Valentin Dufois <vdufois@neo-ooh.com>
 *
 * @neo/api - PullCampaignsPerformancesJob.php
 */

namespace Neo\Modules\Broadcast\Jobs;

use Carbon\Carbon;
use Carbon\CarbonInterface;
use Carbon\CarbonPeriod;
use Illuminate\Bus\Queueable;
use Illuminate\Contracts\Queue\ShouldQueue;
use Illuminate\Foundation\Bus\Dispatchable;
use Illuminate\Queue\InteractsWithQueue;
use Illuminate\Queue\SerializesModels;
use Illuminate\Support\Collection;
use Illuminate\Support\Facades\DB;
use Neo\Modules\Broadcast\Exceptions\InvalidBroadcasterAdapterException;
use Neo\Modules\Broadcast\Models\Campaign;
use Neo\Modules\Broadcast\Models\ResourcePerformance;
use Neo\Modules\Broadcast\Services\BroadcasterAdapterFactory;
use Neo\Modules\Broadcast\Services\BroadcasterCapability;
use Neo\Modules\Broadcast\Services\BroadcasterOperator;
use Neo\Modules\Broadcast\Services\BroadcasterReporting;
use Neo\Modules\Broadcast\Services\Resources\CampaignPerformance;
use Spatie\DataTransferObject\Exceptions\UnknownProperties;
use Symfony\Component\Console\Helper\ProgressBar;
use Symfony\Component\Console\Output\ConsoleOutput;

/**
 * This job update all the
 */
class PullCampaignsPerformancesJob implements ShouldQueue {
    use Dispatchable, InteractsWithQueue, Queueable, SerializesModels;

    /**
     * @var int How many days in the past should we look at
     */
    protected int $lookBack = 3;

    /**
     * @throws UnknownProperties
     */
    public function handle(): void {
        // List all campaigns currently active with their external representations
        $campaigns = Campaign::query()
                             ->where("start_date", "<", DB::raw("NOW()"))
                             ->where("end_date", ">=", DB::raw(/** @lang SQL */ "SUBDATE(NOW(), $this->lookBack)"))
                             ->with(["external_representations"])
                             ->lazy(500);

        $concernedDates = collect(
            CarbonPeriod::create(
                Carbon::now()->subDays($this->lookBack)->toDateString(),
                Carbon::now()->toDateString(),
                '1 day'
            )->toArray()
        )->map(fn(CarbonInterface $date) => $date->toDateString());

        $output   = new ConsoleOutput();
        $progress = new ProgressBar($output, $campaigns->count());
        $progress->setFormat("%current%/%max% [%bar%] %percent:3s%% %message%");
        $progress->setMessage("");
        $progress->start();

        // For each campaign, pull the performances if each of its representations and update our db
        /** @var Campaign $campaign */
        foreach ($campaigns as $campaign) {
            $progress->setMessage("Campaign #{$campaign->getKey()}");
            $progress->advance();

            foreach ($campaign->external_representations as $representation) {
                // Skip representation without a specific network as it's a UB
                if (!$representation->data->network_id) {
                    continue;
                }

                try {
                    /** @var BroadcasterOperator & BroadcasterReporting $broadcaster */
                    $broadcaster = BroadcasterAdapterFactory::makeForNetwork($representation->data->network_id);
                } catch (InvalidBroadcasterAdapterException) {
                    continue;
                }

                // Validate the broadcaster supports broadcasting
                if (!$broadcaster->hasCapability(BroadcasterCapability::Reporting)) {
                    continue;
                }

                // Pull the performances for the representation
                /** @var Collection<CampaignPerformance> $performances */
                $performances = collect($broadcaster->getCampaignsPerformances([$representation->toResource()]));

                // Filter out all the unwanted dates
                $performances = $performances->whereIn("date", $concernedDates);

                // Store the performances
                /** @var CampaignPerformance $performance */
                foreach ($performances as $performance) {
                    $record = ResourcePerformance::query()
                                                 ->where("resource_id", "=", $representation->resource_id)
                                                 ->whereDate("recorded_at", "=", $performance->date)
                                                 ->where("data->network_id", $representation->data->network_id)
                                                 ->whereJsonContains("data->formats_id", $representation->data->formats_id)
                                                 ->first();

                    if ($record) {
                        ResourcePerformance::query()
                                           ->where("resource_id", "=", $representation->resource_id)
                                           ->whereDate("recorded_at", "=", $performance->date)
                                           ->where("data->network_id", $representation->data->network_id)
                                           ->whereJsonContains("data->formats_id", $representation->data->formats_id)
                                           ->update([
                                               "repetitions" => $performance->repetitions,
                                               "impressions" => $performance->impressions,
                                           ]);

                        continue;
                    }

                    $record = new ResourcePerformance([
                        "resource_id" => $representation->resource_id,
                        "recorded_at" => $performance->date,
                        "repetitions" => $performance->repetitions,
                        "impressions" => $performance->impressions,
                        "data"        => [
                            "network_id" => $representation->data->network_id,
                            "formats_id" => $representation->data->formats_id,
                        ],
                    ]);

                    $record->save();
                }
            }
        }
    }
}
