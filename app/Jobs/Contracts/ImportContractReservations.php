<?php
/*
 * Copyright 2020 (c) Neo-OOH - All Rights Reserved
 * Unauthorized copying of this file, via any medium is strictly prohibited
 * Proprietary and confidential
 * Written by Valentin Dufois <vdufois@neo-ooh.com>
 *
 * @neo/api - ImportContractReservations.php
 */

namespace Neo\Jobs\Contracts;

use Carbon\Carbon;
use Illuminate\Bus\Queueable;
use Illuminate\Contracts\Queue\ShouldQueue;
use Illuminate\Foundation\Bus\Dispatchable;
use Illuminate\Queue\InteractsWithQueue;
use Illuminate\Queue\SerializesModels;
use Illuminate\Support\Facades\Cache;
use Neo\Models\Contract;
use Neo\Models\ContractFlight;
use Neo\Models\ContractReservation;
use Neo\Modules\Broadcast\Services\BroadSign\API\BroadSignClient;
use Neo\Modules\Broadcast\Services\BroadSign\Models\Campaign;

/**
 * Class CreateSignupToken
 *
 * @package Neo\Jobs
 *
 */
class ImportContractReservations implements ShouldQueue {
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
        /** @var Contract $contract */
        $contract = Contract::find($this->contractId);

        if (!$contract) {
            // Contract does not exist, stop here
            return;
        }

        $contract->load("flights");

        $config          = Contract::getConnectionConfig();
        $broadsignClient = new BroadSignClient($config);


        // Because there is always problems witht he dashes in the name of reservations,
        // we are gonna do three requests, with variation on the hyphens, and merge all the results

        // Get all the Broadsign Reservations matching the report's contract Id
        $identifier   = strtoupper($contract->contract_id);
        $reservations = Campaign::search($broadsignClient, ["name" => $identifier]);

        // let's do another request, this time replacing hyphen-minus with hyphen...
//        $identifier   = strtoupper(str_replace('-', mb_chr(8208, 'UTF-8'), $contract->contract_id));
//        $reservations = $reservations->merge(Campaign::search($broadsignClient, ["name" => $identifier]));

        // Finally, we do a final one with an underscore this time
        $identifier   = strtoupper(str_replace('-', '_', $contract->contract_id));
        $reservations = $reservations->merge(Campaign::search($broadsignClient, ["name" => $identifier]));

        $reservations = $reservations->unique("id");

        $storedReservationsId = [];

        // Now make sure all reservations are properly associated with the report
        /** @var Campaign $reservation */
        foreach ($reservations as $reservation) {
            // In the case of contract with identical numbers but different prefix, the Broadsign API will return both. eg: NEO-092-21 and OTG-092-21. We need to validate the beggining of the campaign names as an additional filter step
//            if (!str_starts_with($reservation->name, $identifier)) {
//                continue;
//            }

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

            if ($contract->flights->count() === 1) {
                // If only one flight, assign by default
                $rr->flight_id = $contract->flights->first()->id;
            } else if (!$rr->flight_id) {
                $flight = $contract->flights()->where("start_date", "=", $reservation->start_date)
                                   ->where("end_date", "=", $reservation->end_date)
                                   ->when(str_ends_with($reservation->name, "BUA"), function ($query) {
                                       $query->where("type", "=", ContractFlight::BUA);
                                   })->when(!str_ends_with($reservation->name, "BUA"), function ($query) {
                        $query->where("type", "!=", ContractFlight::BUA);
                    })
                                   ->first();

                if ($flight) {
                    $rr->flight_id = $flight->id;
                }
            }

            $rr->save();

            $storedReservationsId[] = $rr->id;
        }

        $contract->reservations()->whereNotIn("id", $storedReservationsId)->delete();

        Cache::forget($contract->getContractPerformancesCacheKey());
    }
}
