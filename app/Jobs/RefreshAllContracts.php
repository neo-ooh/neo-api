<?php
/*
 * Copyright 2020 (c) Neo-OOH - All Rights Reserved
 * Unauthorized copying of this file, via any medium is strictly prohibited
 * Proprietary and confidential
 * Written by Valentin Dufois <vdufois@neo-ooh.com>
 *
 * @neo/api - RefreshAllContracts.php
 */

namespace Neo\Jobs;

use Illuminate\Console\Command;
use Neo\Jobs\Contracts\ImportContractDataJob;
use Neo\Jobs\Contracts\ImportContractReservations;
use Neo\Jobs\Contracts\RefreshContractsPerformancesJob;
use Neo\Models\Contract;

/**
 * Class CreateSignupToken
 *
 * @package Neo\Jobs
 *
 */
class RefreshAllContracts extends Command {
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
        $contracts = Contract::all();

        /** @var Contract $contract */
        foreach ($contracts as $contract) {
            ImportContractDataJob::dispatchSync($contract->id);
            ImportContractReservations::dispatchSync($contract->id);
            RefreshContractsPerformancesJob::dispatchSync($contract->id);
        }

        return 0;
    }
}
