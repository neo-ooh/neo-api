<?php
/*
 * Copyright 2020 (c) Neo-OOH - All Rights Reserved
 * Unauthorized copying of this file, via any medium is strictly prohibited
 * Proprietary and confidential
 * Written by Valentin Dufois <vdufois@neo-ooh.com>
 *
 * @neo/api - RefreshContractsPerformancesJob.php
 */

namespace Neo\Jobs\Contracts;

use Carbon\Carbon;
use Illuminate\Bus\Queueable;
use Illuminate\Contracts\Queue\ShouldQueue;
use Illuminate\Database\Eloquent\Builder;
use Illuminate\Foundation\Bus\Dispatchable;
use Illuminate\Queue\InteractsWithQueue;
use Illuminate\Queue\SerializesModels;
use Illuminate\Support\Facades\Cache;
use Neo\Models\Contract;
use Neo\Models\ContractFlight;
use Neo\Models\ContractReservation;

class RefreshContractsPerformancesJob implements ShouldQueue {
    use Dispatchable, InteractsWithQueue, Queueable, SerializesModels;

    public function __construct(protected int|null $contract_id = null) {
    }

    public function handle() {
        Cache::tags("contract-performances")->clear();

        // We refresh contract performances for contracts who still have flights running
        $contracts = Contract::query()
                             ->when($this->contract_id !== null, function (Builder $query) {
                                 $query->where("id", "=", $this->contract_id);
                             })
                             ->when($this->contract_id === null, function (Builder $query) {
                                 $query->whereRelation("flights", function (Builder $query) {
                                     $query->where("end_date", ">", Carbon::now()->subDays(2));
                                 });
                             })
                             ->with(["flights", "reservations"])
                             ->get()
                             ->append(["performances"]);

        /** @var Contract $contract */
        foreach ($contracts as $contract) {
            $reservationExternalIds = $contract->reservations->filter(function (ContractReservation $reservation) use ($contract) {

                /** @var ContractFlight|null $flight */
                $flight = $contract->flights->firstWhere("id", "=", $reservation->flight_id);
                return $flight && $flight->type !== ContractFlight::BUA;
            })->pluck("external_id");

            $contract->received_impressions = $contract->performances->whereIn("reservable_id", $reservationExternalIds)
                                                                     ->sum("total_impressions");
            $contract->save();

        }
    }
}
