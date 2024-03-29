<?php
/*
 * Copyright 2023 (c) Neo-OOH - All Rights Reserved
 * Unauthorized copying of this file, via any medium is strictly prohibited
 * Proprietary and confidential
 * Written by Valentin Dufois <vdufois@neo-ooh.com>
 *
 * @neo/api - Kernel.php
 */

namespace Neo\Console;

use DateTimeZone;
use Illuminate\Console\Scheduling\Schedule;
use Illuminate\Foundation\Console\Kernel as ConsoleKernel;
use Neo\Console\Commands\Data\PopulateCensusDivisionsCommand;
use Neo\Console\Commands\Data\PopulateCensusFederalElectoralDistrictsCommand;
use Neo\Console\Commands\Data\PopulateCensusForwardSortationAreasCommand;
use Neo\Console\Commands\Data\PopulateCensusSubdivisionsCommand;
use Neo\Console\Commands\OneOff\SwitchCampaignTargetingToLocationsCommand;
use Neo\Console\Commands\OneOff\UpdateOtgFramesCommand;
use Neo\Console\Commands\PullPropertyTraffic;
use Neo\Console\Commands\Test\TestCommand;
use Neo\Modules\Broadcast\Console\Commands\FetchCampaignsPerformancesCommand;
use Neo\Modules\Broadcast\Console\Commands\SynchronizeNetworkCommand;
use Neo\Modules\Dynamics\Console\Commands\SynchronizeNewsRecordsCommand;
use Neo\Modules\Properties\Console\Commands\ImportMobilePropertiesCommand;
use Neo\Modules\Properties\Jobs\Contracts\RefreshContracts;
use Neo\Modules\Properties\Jobs\Contracts\Screenshots\DeleteOldScreenshots;
use Neo\Modules\Properties\Jobs\Contracts\Screenshots\SendScreenshotRequests;
use Neo\Modules\Properties\Jobs\CreateTrafficSnapshotJob;
use Neo\Modules\Properties\Jobs\Traffic\FillMissingTrafficValueJob;
use Neo\Modules\Properties\Jobs\Traffic\PullLatestTrafficData;
use Neo\Modules\Properties\Jobs\Traffic\TrafficRequiredReminder;

class Kernel extends ConsoleKernel {
	/**
	 * The Artisan commands provided by your application.
	 *
	 * @var array
	 */
	protected $commands = [
        TestCommand::class,

        // contracts:update
        RefreshContracts::class,

        // contracts:clear-screenshots
        DeleteOldScreenshots::class,

        // property:pull-traffic {property}
        PullPropertyTraffic::class,

        // Broadcast -------------------

        // network:sync {network}
        SynchronizeNetworkCommand::class,

        // campaigns:fetch-performances {--network=null} {--lookback=3}
        FetchCampaignsPerformancesCommand::class,

        // Data -------------------
        PopulateCensusDivisionsCommand::class,
        PopulateCensusSubdivisionsCommand::class,
        PopulateCensusFederalElectoralDistrictsCommand::class,
        PopulateCensusForwardSortationAreasCommand::class,
        ImportMobilePropertiesCommand::class,
        SynchronizeNewsRecordsCommand::class,
        UpdateOtgFramesCommand::class,
        SwitchCampaignTargetingToLocationsCommand::class,
    ];

	/**
	 * Define the application's command schedule.
	 *
	 * @param Schedule $schedule
	 * @return void
	 */
	protected function schedule(Schedule $schedule): void {
		// Only register recurring tasks on the production environment
		if (config('app.env') !== 'production') {
			return;
		}

		/* -----------------
		 * Every-minute tasks
		 */

		// Send screenshots requests to player
		$schedule->job(SendScreenshotRequests::class)->everyMinute();


		/* -----------------
		 * Hourly tasks
		 */

		// Refresh Contracts performances
		$schedule->command("contracts:update")->everyThreeHours();

		// Refresh news records
		$schedule->command("news:synchronize-records")->everyThreeHours();


		/* -----------------
		 * Daily tasks
		 */

		// Take a snapshot of properties' traffic
		$schedule->job(CreateTrafficSnapshotJob::class)->dailyAt("01:00");

		$schedule->command('contracts:clear-screenshots')->daily();

		// End of schedule email
		/*$schedule->job(NotifyEndOfSchedules::class)->weekdays()
				 ->timezone('America/Toronto')
				 ->at("06:00");*/


		/* -----------------
		 * Monthly tasks
		 */

		// Pull traffic data for property with Linkett pairing
		$schedule->job(PullLatestTrafficData::class)->monthly();
		$schedule->job(FillMissingTrafficValueJob::class)->monthlyOn(2);

		// Send Reminder about traffic data to users
		$schedule->job(TrafficRequiredReminder::class)->monthlyOn(7);
	}

	/**
	 * Get the timezone that should be used by default for scheduled events.
	 *
	 * @return DateTimeZone|string|null
	 */
	protected function scheduleTimezone(): DateTimeZone|string|null {
		return 'America/Toronto';
	}

	/**
	 * Register the commands for the application.
	 *
	 * @return void
	 */
	protected function commands(): void {
		$this->load(__DIR__ . '/Commands');
	}
}
