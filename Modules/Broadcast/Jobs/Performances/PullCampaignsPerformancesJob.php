<?php
/*
 * Copyright 2022 (c) Neo-OOH - All Rights Reserved
 * Unauthorized copying of this file, via any medium is strictly prohibited
 * Proprietary and confidential
 * Written by Valentin Dufois <vdufois@neo-ooh.com>
 *
 * @neo/api - PullCampaignsPerformancesJob.php
 */

namespace Neo\Modules\Broadcast\Jobs\Performances;

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
use Neo\Modules\Broadcast\Models\ExternalResource;
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

        // Group external representations together for batch queries
        /** @var Collection<PerformanceBatch> $batches */
        $batches = collect();
        $campaigns->each(function (Campaign $campaign) use ($batches) {
            $campaign->external_representations->each(function (ExternalResource $resource) use ($batches) {
                // Skip representation without a specific network as it's a UB
                if (!$resource->data->network_id) {
                    return;
                }

                $batch = $batches->firstWhere("network_id", "=", $resource->data->network_id);

                if (!$batch) {
                    $batch = new PerformanceBatch(
                        broadcaster_id: $resource->broadcaster_id,
                        network_id: $resource->data->network_id
                    );
                    $batches->push($batch);
                }

                $batch->external_resources[] = $resource;
            });
        });

        $output   = new ConsoleOutput();
        $progress = new ProgressBar($output, $batches->count());
        $progress->setFormat("%current%/%max% [%bar%] %percent:3s%% %message%");
        $progress->setMessage("");
        $progress->start();

        // For each batch, pull and store performances
        /** @var PerformanceBatch $batch */
        foreach ($batches as $batch) {
            $progress->advance();
            $progress->setMessage("Batch B#$batch->broadcaster_id N#$batch->network_id");

            try {
                /** @var BroadcasterOperator & BroadcasterReporting $broadcaster */
                $broadcaster = BroadcasterAdapterFactory::makeForNetwork($batch->network_id);
            } catch (InvalidBroadcasterAdapterException) {
                continue;
            }

            // Validate the broadcaster supports broadcasting
            if (!$broadcaster->hasCapability(BroadcasterCapability::Reporting)) {
                continue;
            }

            // List all the resource external ID
            $externalRepresentations = collect($batch->external_resources);
            $resources               = $externalRepresentations->map(fn(ExternalResource $resource) => $resource->toResource());

            // Pull the performances for the representations
            /** @var Collection<CampaignPerformance> $performances */
            $performances = collect($broadcaster->getCampaignsPerformances($resources->all()));

            // Filter out all the unwanted dates
            $performances = $performances->whereIn("date", $concernedDates);

            $section = $output->section();
            $section->writeln("");
            $performancesProgress = new ProgressBar($section, $performances->count());
            $performancesProgress->setMessage("");
            $performancesProgress->start();

            // Store the performances
            /** @var CampaignPerformance $performance */
            foreach ($performances as $performance) {
                $performancesProgress->advance();

                // Find back the associated representation
                /** @var ExternalResource|null $representation */
                $representation = $externalRepresentations->firstWhere("data.external_id", "=", $performance->campaign->external_id);

                if (!$representation) {
                    continue;
                }

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

            $performancesProgress->finish();
            $section->clear();
        }
    }
}
