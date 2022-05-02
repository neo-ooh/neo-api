<?php
/*
 * Copyright 2020 (c) Neo-OOH - All Rights Reserved
 * Unauthorized copying of this file, via any medium is strictly prohibited
 * Proprietary and confidential
 * Written by Valentin Dufois <vdufois@neo-ooh.com>
 *
 * @neo/api - RefreshContracts.php
 */

namespace Neo\Jobs\Contracts;

use Illuminate\Console\Command;
use Neo\Models\Contract;
use Neo\Services\Odoo\OdooConfig;
use Symfony\Component\Console\Output\ConsoleOutput;

/**
 * Class CreateSignupToken
 *
 * @package Neo\Jobs
 *
 */
class RefreshContracts extends Command {
    /**
     * The name and signature of the console command.
     *
     * @var string
     */
    protected $signature = 'contracts:update';

    /**
     * The console command description.
     *
     * @var string
     */
    protected $description = 'Update all contracts reservations and performances';

    /**
     * Execute the console command.
     *
     * @return int
     */
    public function handle(): int {
        // Import contract that may be missing from Connect.
        ImportMissingContractsJob::dispatch();

        $contracts  = Contract::query()
//                              ->where("start_date", ">", Carbon::now()->subWeek())
                              ->get();
        $odooClient = OdooConfig::fromConfig()->getClient();

        (new ConsoleOutput())->writeln("Parsing {$contracts->count()} contracts...");

        /** @var Contract $contract */
        foreach ($contracts as $contract) {
            // Before doing anything, we want to check the status of the contract.
            // Only contracted contracts are imported in Connect, but a contract in Odoo may change to another status, such as cancelled at anytime. In this case, it is important to purge the contract from Connect. Otherwise, this may break availabilities.
            $odooContract = \Neo\Services\Odoo\Models\Contract::get($odooClient, $contract->external_id);

            // If the contract could no be found, or is not in a confirmed state, we remove it from Connect.
            if (!$odooContract || !$odooContract->isConfirmed()) {
                (new ConsoleOutput())->writeln("Removed contract $contract->contract_id");
                $contract->delete();
                continue;
            }

            ImportContractDataJob::dispatchSync($contract->id, $odooContract);
            ImportContractReservations::dispatchSync($contract->id);
            RefreshContractsPerformancesJob::dispatchSync($contract->id);
        }

        return 0;
    }
}
