<?php
/*
 * Copyright 2020 (c) Neo-OOH - All Rights Reserved
 * Unauthorized copying of this file, via any medium is strictly prohibited
 * Proprietary and confidential
 * Written by Valentin Dufois <vdufois@neo-ooh.com>
 *
 * @neo/api - ImportMissingContractsJob.php
 */

namespace Neo\Jobs\Contracts;

use Carbon\Carbon;
use Illuminate\Bus\Queueable;
use Illuminate\Contracts\Queue\ShouldQueue;
use Illuminate\Foundation\Bus\Dispatchable;
use Illuminate\Queue\InteractsWithQueue;
use Illuminate\Queue\SerializesModels;
use Neo\Services\Odoo\Models\Contract;
use Neo\Services\Odoo\OdooConfig;

class ImportMissingContractsJob implements ShouldQueue {
    use Dispatchable, InteractsWithQueue, Queueable, SerializesModels;

    public function __construct() {
    }

    public function handle() {
        // Our goal here is to list all contracts in Odoo that have been modified those past 4 days, keep the ones that has a contract sent/contract signed status, and make sure they are correctly imported in Odoo.

        $client        = OdooConfig::fromConfig()->getClient();
        $odooContracts = Contract::all($client, [
            ["write_date", ">", Carbon::now()->subDays(4)->toDateString()],
        ]);

        /** @var Contract $odooContract */
        foreach ($odooContracts as $odooContract) {
            // try to import the contract in Connect
            ImportContractJob::dispatch($odooContract->name, $odooContract);
        }
    }
}
