<?php

use Illuminate\Database\Migrations\Migration;
use Neo\BroadSign\Models\Customer;
use Neo\Models\Burst;
use Neo\Models\Client;
use Neo\Models\Contract;
use Neo\Models\ContractBurst;
use Neo\Models\ContractReservation;
use Neo\Models\ContractScreenshot;
use Neo\Models\Report;
use Neo\Models\ReportReservation;

class MigrateReportsToContracts extends Migration {
    /**
     * Run the migrations.
     *
     * @return void
     */
    public function up() {
        // We need to move all resources from the Reports/Bursts setup to the new Contract setup
        // Moving is made by copying then removing.
        $reports = Report::all();

        foreach ($reports as $report) {
            // Is the client already registered ?
            $client = Client::query()->where("broadsign_customer_id", "=", $report->customer_id)->first();

            if (!$client) {
                // Load the client info from Broadsign
                $bsClient = Customer::get($report->customer_id);
                // Create the client
                $client = new Client([
                    "broadsign_customer_id" => $bsClient->id,
                    "name"                  => $bsClient->name,
                ]);
                $client->save();
            }

            // Client is good, register the contract
            $contract = new Contract([
                "contract_id" => $report->contract_id,
                "client_id"   => $client->id,
            ]);
            $contract->save();

            // Contract is good, now the reservations
            /** @var ReportReservation $reportReservation */
            foreach ($report->reservations as $reportReservation) {
                $reservation = new ContractReservation([
                    "contract_id"              => $contract->id,
                    "broadsign_reservation_id" => $reportReservation->broadsign_reservation_id,
                    "network"                  => "-",
                    "name"                     => $reportReservation->name,
                    "original_name"            => $reportReservation->name,
                    "start_date"               => $reportReservation->start_date,
                    "end_date"                 => $reportReservation->end_date
                ]);
                $reservation->save();
            }

            // Reservations are good, now the bursts
            $reportBursts = $report->bursts;

            /** @var Burst $reportBurst */
            foreach ($reportBursts as $reportBurst) {
                $burst = new ContractBurst([
                    "id"            => $reportBurst->id,
                    "contract_id"   => $contract->id,
                    "actor_id"      => $reportBurst->requested_by,
                    "location_id"   => $reportBurst->location_id,
                    "start_at"      => $reportBurst->start_at,
                    "status"        => $reportBurst->started ? "OK" : "PENDING",
                    "scale_percent" => $reportBurst->scale_factor,
                    "duration_ms"   => $reportBurst->duration_ms,
                    "frequency_ms"  => $reportBurst->frequency_ms,
                    "created_at"    => $reportBurst->created_at,
                    "updated_at"    => $reportBurst->updated_at,
                ]);
                $burst->timestamps = false;
                $burst->save();
                DB::enableQueryLog();

                // Burst is good, now move its screenshots
                foreach ($reportBurst->screenshots as $reportScreenshot) {
                    $screenshot = new ContractScreenshot([
                        "id"        => $reportScreenshot->id,
                        "burst_id"  => $burst->id,
                        "is_locked" => false,
                    ]);
                    $screenshot->save();
                }

                // Screenshots are good
            }
        }
    }

    /**
     * Reverse the migrations.
     *
     * @return void
     */
    public function down() {
    }
}
