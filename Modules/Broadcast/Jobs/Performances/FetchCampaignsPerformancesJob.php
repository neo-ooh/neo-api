<?php
/*
 * Copyright 2023 (c) Neo-OOH - All Rights Reserved
 * Unauthorized copying of this file, via any medium is strictly prohibited
 * Proprietary and confidential
 * Written by Valentin Dufois <vdufois@neo-ooh.com>
 *
 * @neo/api - FetchCampaignsPerformancesJob.php
 */

namespace Neo\Modules\Broadcast\Jobs\Performances;

use Carbon\Carbon;
use Carbon\CarbonInterface;
use Carbon\CarbonPeriod;
use Illuminate\Bus\Queueable;
use Illuminate\Contracts\Queue\ShouldQueue;
use Illuminate\Database\Eloquent\Builder;
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
 * This job update all the
 */
class FetchCampaignsPerformancesJob implements ShouldQueue {
	use Dispatchable, InteractsWithQueue, Queueable, SerializesModels;

	/**
	 * @param int|null $networkId
	 * @param int|null $lookBack How many days in the past should we look at
	 */
	public function __construct(protected int|null $networkId = null, protected int|null $lookBack = 3, protected int|null $campaignId = null) {
	}

	/**
	 * @return void
	 */
	public function handle(): void {
		// List all campaigns currently active with their external representations
		$campaigns = Campaign::query()
		                     ->where("start_date", "<", DB::raw("NOW()"))
		                     ->when($this->campaignId !== null, function (Builder $query) {
			                     $query->where("id", "=", $this->campaignId);
		                     })
		                     ->when($this->lookBack !== null, function (Builder $query) {
			                     $query->where("end_date", ">=", DB::raw(/** @lang SQL */ "SUBDATE(NOW(), $this->lookBack)"));
		                     })
		                     ->with(["external_representations" => fn($q) => $q->withTrashed()])
		                     ->lazy(500);

		if ($this->lookBack) {
			$concernedDates = collect(
				CarbonPeriod::create(
					Carbon::now()->subDays($this->lookBack)->toDateString(),
					Carbon::now()->toDateString(),
					'1 day'
				)->toArray()
			)->map(fn(CarbonInterface $date) => $date->toDateString());
		} else {
			$concernedDates = collect();
		}

		// Group external representations together for batch queries
		/** @var Collection<PerformanceBatch> $batches */
		$batches = collect();
		$campaigns->each(function (Campaign $campaign) use ($batches) {
			$campaign->external_representations->each(function (ExternalResource $resource) use ($batches) {
				// Skip representation without a specific network as it's a UB
				if (!$resource->data->network_id) {
					return;
				}

				if ($this->networkId && $resource->data->network_id !== $this->networkId) {
					return;
				}

				$batch = $batches->firstWhere("network_id", "=", $resource->data->network_id);

				if (!$batch) {
					$batch = new PerformanceBatch(
						broadcaster_id: $resource->broadcaster_id,
						network_id    : $resource->data->network_id
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

			// List all the resources external ID
			$externalRepresentations = collect($batch->external_resources);
			$resources               = $externalRepresentations->map(fn(ExternalResource $resource) => $resource->toResource());

			// Pull the daily performances for the representations
			$dailyPerformances = collect();
			foreach ($resources->chunk(50) as $resourcesChunk) {
				/** @var Collection<CampaignPerformance> $dailyPerformances */
				$dailyPerformances = $dailyPerformances->merge($broadcaster->getCampaignsPerformances($resourcesChunk->all()));
			}

			// Filter out all the unwanted dates
			if ($this->lookBack) {
				$dailyPerformances = $dailyPerformances->whereIn("date", $concernedDates);
			}

			// We want to group daily performance data by campaign resource
			$groupedDailyPerformances = $dailyPerformances
				->map(fn(CampaignPerformance $datum) => new CampaignPerformanceDatum(
					representation: $externalRepresentations->firstWhere("data.external_id", "=", $datum->campaign->external_id),
					campaign      : $datum->campaign,
					date          : $datum->date,
					repetitions   : $datum->repetitions,
					impressions   : $datum->impressions,
				))
				->whereNotNull("representation")
				->groupBy([
					          "representation.resource_id",
					          "date",
					          "representation.data.formats_id.0",
				          ]);

			$section = $output->section();
			$section->writeln("Parsing daily performances");
			$section->writeln("");
			$performancesProgress = new ProgressBar($section, $groupedDailyPerformances->count());
			$performancesProgress->setMessage("");
			$performancesProgress->start();

			// Parse and store the performances
			/** @var Collection<Collection<Collection<CampaignPerformanceDatum>>> $campaignDailyPerformances */
			foreach ($groupedDailyPerformances as $campaignId => $campaignDailyPerformances) {
				$performancesProgress->advance();

				/** @var Collection<Collection<CampaignPerformanceDatum>> $campaignDayPerformances */
				foreach ($campaignDailyPerformances as $date => $campaignDayPerformances) {
					/** @var Collection<CampaignPerformanceDatum> $campaignDayFormatPerformances */
					foreach ($campaignDayPerformances as $campaignDayFormatPerformances) {
						/** @var ExternalResource $representation */
						$representation = $campaignDayFormatPerformances->first()->representation;

						$repetitions = $campaignDayFormatPerformances->sum("repetitions");
						$impressions = $campaignDayFormatPerformances->sum("impressions");

						$record = ResourcePerformance::query()
						                             ->where("resource_id", "=", $campaignId)
						                             ->where("recorded_at", "=", $date)
						                             ->where("data->network_id", $representation->data->network_id)
						                             ->whereJsonContains("data->formats_id", $representation->data->formats_id)
						                             ->first();

						if ($record) {
							ResourcePerformance::query()
							                   ->where("resource_id", "=", $campaignId)
							                   ->where("recorded_at", "=", $date)
							                   ->where("data->network_id", $representation->data->network_id)
							                   ->whereJsonContains("data->formats_id", $representation->data->formats_id)
							                   ->update([
								                            "repetitions" => $repetitions,
								                            "impressions" => $impressions,
							                            ]);

							continue;
						}

						$record = new ResourcePerformance([
							                                  "resource_id" => $campaignId,
							                                  "recorded_at" => $date,
							                                  "repetitions" => $repetitions,
							                                  "impressions" => $impressions,
							                                  "data"        => new ResourcePerformanceData(
								                                  network_id: $representation->data->network_id,
								                                  formats_id: $representation->data->formats_id,
							                                  ),
						                                  ]);

						$record->save();
					}
				}
			}

			$performancesProgress->finish();

			// Pull the locations performances for the representations
			/** @var Collection<CampaignLocationPerformance> $locationsPerformances */
			$locationsPerformances = collect();
			foreach ($resources->chunk(50) as $resourcesChunk) {
				/** @var Collection<CampaignPerformance> $dailyPerformances */
				$locationsPerformances = collect($broadcaster->getCampaignsPerformancesByLocations($resourcesChunk->all()));
			}

			DB::statement("DROP TABLE IF EXISTS `temp_locations_ids`", []);
			DB::statement("CREATE TEMPORARY TABLE `temp_locations_ids` (`external_id` bigint UNSIGNED)", []);

			DB::table("temp_locations_ids")->insert($locationsPerformances->pluck("location.external_id")
			                                                              ->unique()
			                                                              ->map(fn($locationID) => ["external_id" => $locationID])
			                                                              ->toArray());

			$groupedLocationsPerformances = $locationsPerformances->groupBy("location.external_id");

			// Load all locations in advance to limit queries
			$locations = Location::query()
			                     ->join("temp_locations_ids", "locations.external_id", "=", "temp_locations_ids.external_id", "inner")
			                     ->get();

			$section->writeln("Parsing locations performances");
			$section->writeln("");
			$locationsPerformancesProgress = new ProgressBar($section, $groupedLocationsPerformances->count());
			$locationsPerformancesProgress->setMessage("");
			$locationsPerformancesProgress->start();

			// Store the performances
			/** @var Collection<CampaignLocationPerformance> $locationPerformances */
			foreach ($groupedLocationsPerformances as $locationPerformances) {
				$locationsPerformancesProgress->advance();

				// Find back the associated representation
				/** @var ExternalResource|null $representation */
				$representation = $externalRepresentations->firstWhere("data.external_id", "=", $locationPerformances[0]->campaign->external_id);
				/** @var Location|null $location */
				$location = $locations->firstWhere("external_id", "=", $locationPerformances[0]->location->external_id);

				if (!$representation || !$location) {
					continue;
				}
				
				$didUpdate = ResourceLocationPerformance::query()
				                                        ->where("resource_id", "=", $representation->resource_id)
				                                        ->where("location_id", "=", $location->getKey())
				                                        ->where("data->network_id", $representation->data->network_id)
				                                        ->whereJsonContains("data->formats_id", $representation->data->formats_id)
				                                        ->update([
					                                                 "repetitions" => $locationPerformances->sum("repetitions"),
					                                                 "impressions" => $locationPerformances->sum("impressions"),
				                                                 ]);

				if (!$didUpdate) {
					$record = new ResourceLocationPerformance([
						                                          "resource_id" => $representation->resource_id,
						                                          "location_id" => $location->getKey(),
						                                          "repetitions" => $locationPerformances->sum("repetitions"),
						                                          "impressions" => $locationPerformances->sum("impressions"),
						                                          "data"        => new ResourcePerformanceData(
							                                          network_id: $representation->data->network_id,
							                                          formats_id: $representation->data->formats_id,
						                                          ),
					                                          ]);

					$record->save();
				}
			}

			$locationsPerformancesProgress->finish();

			$section->clear();
		}
	}
}
