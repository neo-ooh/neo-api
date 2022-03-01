<?php
/*
 * Copyright 2020 (c) Neo-OOH - All Rights Reserved
 * Unauthorized copying of this file, via any medium is strictly prohibited
 * Proprietary and confidential
 * Written by Valentin Dufois <vdufois@neo-ooh.com>
 *
 * @neo/api - MigrateContractsJob.php
 */

namespace Neo\Jobs\Contracts;

use Illuminate\Bus\Queueable;
use Illuminate\Contracts\Queue\ShouldQueue;
use Illuminate\Foundation\Bus\Dispatchable;
use Illuminate\Queue\InteractsWithQueue;
use Illuminate\Queue\SerializesModels;
use Neo\Models\Contract;
use Neo\Services\Odoo\OdooConfig;
use Symfony\Component\Console\Output\ConsoleOutput;

class MigrateContractsJob implements ShouldQueue {
    use Dispatchable, InteractsWithQueue, Queueable, SerializesModels;

    public function __construct() {
        $client = OdooConfig::fromConfig()->getClient();

        $output    = new ConsoleOutput();
        $contracts = Contract::query()->whereDoesntHave("flights")->inRandomOrder()->get();

        /** @var Contract $contract */
        foreach ($contracts as $contract) {
            $section = $output->section();
            $section->writeln("<info>" . $contract->contract_id . "...</info>");

            // Check if the contract is present in ODOO
            $odooContract = \Neo\Services\Odoo\Models\Contract::findByName($client, $contract->contract_id);

            if (!$odooContract) {
                // Contract does not exist on Odoo's side, delete it
                $contract->delete();

                $section->clear();
                $section->writeln("<error>" . $contract->contract_id . ": Not found. Removed!</error>");
                continue;
            }

            if ($odooContract->state === 'draft') {
                // This is still a proposal!
                $contract->delete();

                $section->clear();
                $section->writeln("<error>" . $contract->contract_id . ": Still a proposal. Removed!</error>");
            }

            ImportContractDataJob::dispatchSync($contract->getKey(), $odooContract);
            ImportContractReservations::dispatchSync($contract->getKey());

            $section->writeln($contract->contract_id . ": OK");
        }
    }

    public function handle() {
        //
    }
}
