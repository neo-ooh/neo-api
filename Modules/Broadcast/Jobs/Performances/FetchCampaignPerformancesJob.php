<?php
/*
 * Copyright 2023 (c) Neo-OOH - All Rights Reserved
 * Unauthorized copying of this file, via any medium is strictly prohibited
 * Proprietary and confidential
 * Written by Valentin Dufois <vdufois@neo-ooh.com>
 *
 * @neo/api - FetchCampaignPerformancesJob.php
 */

namespace Neo\Modules\Broadcast\Jobs\Performances;

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
use Neo\Modules\Broadcast\Models\Location;
use Neo\Modules\Broadcast\Models\ResourceLocationPerformance;
use Neo\Modules\Broadcast\Models\ResourcePerformance;
use Neo\Modules\Broadcast\Models\StructuredColumns\ResourcePerformanceData;
use Neo\Modules\Broadcast\Services\BroadcasterAdapterFactory;
use Neo\Modules\Broadcast\Services\BroadcasterCapability;
use Neo\Modules\Broadcast\Services\BroadcasterOperator;
use Neo\Modules\Broadcast\Services\BroadcasterReporting;
use Neo\Modules\Broadcast\Services\Resources\CampaignLocationPerformance;
use Neo\Modules\Broadcast\Services\Resources\CampaignPerformance;
use Symfony\Component\Console\Helper\ProgressBar;
use Symfony\Component\Console\Output\ConsoleOutput;

/**
 * This job update the performances of a single Connect campaign
 */
class FetchCampaignPerformancesJob implements ShouldQueue {
	use Dispatchable, InteractsWithQueue, Queueable, SerializesModels;

	/**
	 * @param int $campaignId
	 */
	public function __construct(protected int $campaignId) {
	}

	/**
	 * @return void
	 */
	public function handle(): void {
		// Load the campaign with its external representations
		$campaigns = Campaign::query()
		                     ->find($this->campaignId)
		                     ->load(["external_representations"]);

		// Group external representations together for batch queries
		/** @var Collection<PerformanceBatch> $batches */
		$batches = $campaigns->external_representations->groupBy("data.network_id")
		                                               ->map(fn(Collection $representations) => new PerformanceBatch(
			                                               broadcaster_id    : $representations->first()->broadcaster_id,
			                                               network_id        : $representations->first()->data->network_id,
			                                               external_resources: $representations->all()
		                                               ));


		$output = new ConsoleOutput();
		$output->writeln("Processing batches");
		$progress = new ProgressBar($output, $batches->count());
		$progress->setFormat("%current%/%max% [%bar%] %percent:3s%% %message%");
		$progress->setMessage("");
		$progress->start();

		// For each batch, pull and store performances
		/** @var PerformanceBatch $batch */
		foreach ($batches as $batch) {
			$progress->setMessage("Batch B#$batch->broadcaster_id N#$batch->network_id");
			$progress->advance();

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

			// List all the resources external ID
			$externalRepresentations = collect($batch->external_resources);
			$resources               = $externalRepresentations->map(fn(ExternalResource $resource) => $resource->toResource());

			// Pull the daily performances for the representations
			/** @var Collection<CampaignPerformance> $performances */
			$performances = collect($broadcaster->getCampaignsPerformances($resources->all()));

			$section = $output->section();
			$section->writeln("Processing daily performances");
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
				                             ->where("recorded_at", "=", $performance->date)
				                             ->where("data->network_id", $representation->data->network_id)
				                             ->whereJsonContains("data->formats_id", $representation->data->formats_id)
				                             ->first();

				if ($record) {
					ResourcePerformance::query()
					                   ->where("resource_id", "=", $representation->resource_id)
					                   ->where("recorded_at", "=", $performance->date)
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
					                                  "data"        => new ResourcePerformanceData(
						                                  network_id: $representation->data->network_id,
						                                  formats_id: $representation->data->formats_id,
					                                  ),
				                                  ]);

				$record->save();
			}

			$performancesProgress->finish();

			// Pull the locations performances for the representations
			/** @var Collection<CampaignLocationPerformance> $locationsPerformances */
			$locationsPerformances = collect($broadcaster->getCampaignsPerformancesByLocations($resources->all()));

			DB::statement("DROP TABLE IF EXISTS `temp_locations_ids`", []);
			DB::statement("CREATE TEMPORARY TABLE `temp_locations_ids` (`external_id` bigint UNSIGNED)", []);

			DB::table("temp_locations_ids")->insert($locationsPerformances->pluck("location.external_id")
			                                                              ->unique()
			                                                              ->map(fn($locationID) => ["external_id" => $locationID])
			                                                              ->toArray());

			// Load all locations in advance to limit queries
			$locations = Location::query()
			                     ->join("temp_locations_ids", "locations.external_id", "=", "temp_locations_ids.external_id", "inner")
			                     ->get();

			$section->writeln("Processing products performances");
			$locationsPerformancesProgress = new ProgressBar($section, $locationsPerformances->count());
			$locationsPerformancesProgress->setMessage("");
			$locationsPerformancesProgress->start();

			// Store the performances
			/** @var CampaignLocationPerformance $locationPerformance */
			foreach ($locationsPerformances as $locationPerformance) {
				$locationsPerformancesProgress->advance();

				// Find back the associated representation
				/** @var ExternalResource|null $representation */
				$representation = $externalRepresentations->firstWhere("data.external_id", "=", $locationPerformance->campaign->external_id);
				/** @var Location|null $location */
				$location = $locations->firstWhere("external_id", "=", $locationPerformance->location->external_id);

				if (!$representation || !$location) {
					continue;
				}

				$record = ResourceLocationPerformance::query()
				                                     ->where("resource_id", "=", $representation->resource_id)
				                                     ->where("location_id", "=", $location->getKey())
				                                     ->where("data->network_id", $representation->data->network_id)
				                                     ->whereJsonContains("data->formats_id", $representation->data->formats_id)
				                                     ->first();

				if ($record) {
					ResourceLocationPerformance::query()
					                           ->where("resource_id", "=", $representation->resource_id)
					                           ->where("location_id", "=", $location->getKey())
					                           ->where("data->network_id", $representation->data->network_id)
					                           ->whereJsonContains("data->formats_id", $representation->data->formats_id)
					                           ->update([
						                                    "repetitions" => $locationPerformance->repetitions,
						                                    "impressions" => $locationPerformance->impressions,
					                                    ]);

					continue;
				}

				$record = new ResourceLocationPerformance([
					                                          "resource_id" => $representation->resource_id,
					                                          "location_id" => $location->getKey(),
					                                          "repetitions" => $locationPerformance->repetitions,
					                                          "impressions" => $locationPerformance->impressions,
					                                          "data"        => new ResourcePerformanceData(
						                                          network_id: $representation->data->network_id,
						                                          formats_id: $representation->data->formats_id,
					                                          ),
				                                          ]);

				$record->save();
			}

			$locationsPerformancesProgress->finish();
			$section->clear();
		}
	}
}
