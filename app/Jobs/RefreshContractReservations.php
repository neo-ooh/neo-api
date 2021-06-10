<?php
/*
 * Copyright 2020 (c) Neo-OOH - All Rights Reserved
 * Unauthorized copying of this file, via any medium is strictly prohibited
 * Proprietary and confidential
 * Written by Valentin Dufois <vdufois@neo-ooh.com>
 *
 * @neo/api - RefreshContractReservations.php
 */

namespace Neo\Jobs;

use Carbon\Carbon;
use Illuminate\Bus\Queueable;
use Illuminate\Contracts\Queue\ShouldQueue;
use Illuminate\Foundation\Bus\Dispatchable;
use Illuminate\Queue\InteractsWithQueue;
use Illuminate\Queue\SerializesModels;
use Neo\Models\Contract;
use Neo\Models\ContractReservation;
use Neo\Services\Broadcast\BroadSign\API\BroadsignClient;
use Neo\Services\Broadcast\BroadSign\Models\Campaign;

/**
 * Class CreateSignupToken
 *
 * @package Neo\Jobs
 *
 */
class RefreshContractReservations implements ShouldQueue {
    use Dispatchable, InteractsWithQueue, Queueable, SerializesModels;

    protected int $contractId;

    /**
     * Create a new job instance.
     */
    public function __construct(int $contractId) {
        $this->contractId = $contractId;
    }

    /**
     * Execute the job.
     *
     * @return void
     */
    public function handle(): void {
        $config          = Contract::getConnectionConfig();
        $broadsignClient = new BroadsignClient($config);

        $contract = Contract::find($this->contractId);

        if (!$contract) {
            // Contract does not exist, stop here
            return;
        }

        // Get all the Broadsign Reservations matching the report's contract Id
        $reservations = Campaign::search($broadsignClient, ["name" => strtoupper($contract->contract_id)]);

        if (count($reservations) === 0) {
            // No campaigns where found, let's try again, replacing hyphen-minus with hyphen...
            $utfContract = str_replace('-', mb_chr(8208, 'UTF-8'), $contract->contract_id);

            $reservations = Campaign::search($broadsignClient, ["name" => strtoupper($utfContract)]);

            if (count($reservations) === 0) {
                // Still nothing, let's try with an underscore this time
                $utfContract = str_replace('-', '_', $contract->contract_id);

                $reservations = Campaign::search($broadsignClient, ["name" => strtoupper($utfContract)]);
            }
        }

        // Now make sure all reservations are properly associated with the report
        /** @var Campaign $reservation */
        foreach ($reservations as $reservation) {
            /** @var ContractReservation $rr */
            $rr = ContractReservation::query()->firstOrNew([
                "external_id" => $reservation->id
            ]);

            // Make sure information about the campaign are up to date
            $rr->contract_id   = $contract->id;
            $rr->name          = $reservation->name;
            $rr->original_name = $reservation->name;
            $rr->start_date    = Carbon::parse($reservation->start_date . " " . $reservation->start_time);
            $rr->end_date      = Carbon::parse($reservation->end_date . " " . $reservation->end_time);
            $rr->save();
        }
    }
}
