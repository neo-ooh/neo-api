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

use Illuminate\Console\Command;
use Neo\Models\Contract;
use Neo\Services\Broadcast\BroadSign\API\BroadsignClient;

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
    protected $signature = 'network:update-contracts';

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
            RefreshContractReservations::dispatchSync($contract->id);
        }

        return 0;
    }
}
