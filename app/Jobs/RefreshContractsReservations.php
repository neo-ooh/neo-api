<?php
/*
 * Copyright 2020 (c) Neo-OOH - All Rights Reserved
 * Unauthorized copying of this file, via any medium is strictly prohibited
 * Proprietary and confidential
 * Written by Valentin Dufois <vdufois@neo-ooh.com>
 *
 * @neo/api - RefreshContractsReservations.php
 */

namespace Neo\Jobs;

use Carbon\Carbon;
use Illuminate\Bus\Queueable;
use Illuminate\Contracts\Queue\ShouldQueue;
use Illuminate\Foundation\Bus\Dispatchable;
use Illuminate\Queue\InteractsWithQueue;
use Illuminate\Queue\SerializesModels;
use Neo\BroadSign\Models\Campaign;
use Neo\Models\Contract;
use Neo\Models\ContractReservation;

/**
 * Class CreateSignupToken
 *
 * @package Neo\Jobs
 *
 */
class RefreshContractsReservations implements ShouldQueue {
    use Dispatchable, InteractsWithQueue, Queueable, SerializesModels;

    /**
     * Create a new job instance.
     */
    public function __construct() {
    }

    /**
     * Execute the job.
     *
     * @return void
     */
    public function handle(): void {
        $contracts = Contract::all();

        /** @var Contract $contract */
        foreach ($contracts as $contract) {
            // Get all the Broadsign Reservations matching the report's contract Id
            $reservations = Campaign::search(strtoupper($contract->contract_id));

            if (count($reservations) === 0) {
                // No campaigns where found, let's try again, replacing hyphens with hyphen-minus...
                $utfContract = str_replace('-', mb_chr(8208, 'UTF-8'), $contract->contract_id);

                $reservations = Campaign::search(strtoupper($utfContract));

                if (count($reservations) === 0) {
                    // Still nothing, let's try with an underscore this time
                    $utfContract = str_replace('-', '_', $contract->contract_id);

                    $reservations = Campaign::search(strtoupper($utfContract));
                }
            }

            // Now make sure all reservations are properly associated with the report
            /** @var Campaign $reservation */
            foreach ($reservations as $reservation) {
                /** @var ContractReservation $rr */
                $rr = ContractReservation::query()->firstOrCreate([
                    "external_id" => $reservation->id
                ], [
                    "contract_id" => $contract->id,
                    "name"        => $reservation->name,
                ]);

                // Make sure information about the campaign are up to date
                $rr->contract_id   = $contract->id;
                $rr->original_name = $reservation->name;
                $rr->start_date    = Carbon::parse($reservation->start_date . " " . $reservation->start_time);
                $rr->end_date      = Carbon::parse($reservation->end_date . " " . $reservation->end_time);
                $rr->save();
            }
        }
    }
}
