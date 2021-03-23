<?php
/*
 * Copyright 2020 (c) Neo-OOH - All Rights Reserved
 * Unauthorized copying of this file, via any medium is strictly prohibited
 * Proprietary and confidential
 * Written by Valentin Dufois <vdufois@neo-ooh.com>
 *
 * @neo/api - RefreshReportReservations.php
 */

namespace Neo\Jobs;

use Carbon\Carbon;
use Database\Seeders\ParamsSeeder;
use Illuminate\Bus\Queueable;
use Illuminate\Contracts\Queue\ShouldQueue;
use Illuminate\Foundation\Bus\Dispatchable;
use Illuminate\Queue\InteractsWithQueue;
use Illuminate\Queue\SerializesModels;
use Neo\BroadSign\Models\Campaign;
use Neo\Models\Report;
use Neo\Models\ReportReservation;

/**
 * Class CreateSignupToken
 *
 * @package Neo\Jobs
 *
 */
class RefreshReportReservations implements ShouldQueue {
    use Dispatchable, InteractsWithQueue, Queueable, SerializesModels;

    /**
     * @var int Id of the report
     */
    protected int $reportId;


    /**
     * Create a new job instance.
     *
     * @param int $reportId
     */
    public function __construct(int $reportId) {
        $this->reportId = $reportId;
    }

    /**
     * Execute the job.
     *
     * @return void
     */
    public function handle(): void {
        // Get the report
        /** @var Report $report */
        $report = Report::query()->findOrFail($this->reportId);

        // Get all the Broadsign Reservations matching the report's contract Id
        $reservations = Campaign::search(strtoupper($report->contract_id));

        if(count($reservations) === 0) {
            // No campaigns where found, let's try again, replacing hyphens with hyphen-minus...
            $utfContract = str_replace('-', mb_chr(8208, 'UTF-8'), $report->contract_id);

            $reservations = Campaign::search(strtoupper($utfContract));

            if(count($reservations) === 0) {
                // Still nothing, let's try with an underscore this time
                $utfContract = str_replace('-', '_', $report->contract_id);

                $reservations = Campaign::search(strtoupper($utfContract));
            }
        }

        // Now make sure all reservations are properly associated with the report
        /** @var Campaign $reservation */
        foreach ($reservations as $reservation) {
            /** @var ReportReservation $rr */
            $rr = ReportReservation::query()->firstOrCreate([
                "broadsign_reservation_id" => $reservation->id
            ], [
                "report_id" => $report->id,
                "name" => $reservation->name,
            ]);

            // Make sure information about the campaign are up to date
            $rr->report_id = $report->id;
            $rr->internal_name = $reservation->name;
            $rr->start_date = Carbon::parse($reservation->start_date ." ". $reservation->start_time);
            $rr->end_date = Carbon::parse($reservation->end_date ." ". $reservation->end_time);
            $rr->save();
        }

    }
}
