<?php
/*
 * Copyright 2020 (c) Neo-OOH - All Rights Reserved
 * Unauthorized copying of this file, via any medium is strictly prohibited
 * Proprietary and confidential
 * Written by Valentin Dufois <vdufois@neo-ooh.com>
 *
 * @neo/api - ImportContractJob.php
 */

namespace Neo\Jobs\Contracts;

use Illuminate\Bus\Queueable;
use Illuminate\Contracts\Queue\ShouldBeUnique;
use Illuminate\Contracts\Queue\ShouldQueue;
use Illuminate\Foundation\Bus\Dispatchable;
use Illuminate\Queue\InteractsWithQueue;
use Illuminate\Queue\SerializesModels;
use Illuminate\Support\Facades\Auth;
use Neo\Models\Actor;
use Neo\Models\Contract;
use Neo\Services\Odoo\OdooConfig;

class ImportContractJob implements ShouldQueue, ShouldBeUnique {
    use Dispatchable, InteractsWithQueue, Queueable, SerializesModels;

    public function uniqueId(): string {
        return $this->contract_name;
    }

    public function __construct(protected string $contract_name, protected \Neo\Services\Odoo\Models\Contract|null $odooContract) {
    }

    public function handle() {
        if (Contract::query()->where("contract_id", "=", $this->contract_name)->exists()) {
            // A contract with this name already exist, ignore
            return;
        }

        $odooClient = OdooConfig::fromConfig()->getClient();

        if (!$this->odooContract) {
            // Pull the contract from Odoo
            $this->odooContract = \Neo\Services\Odoo\Models\Contract::findByName($odooClient, $this->contract_name);
        }

        if (!$this->odooContract) {
            // Could not found contract, ignore
            return;
        }

        if (!$this->odooContract->isConfirmed()) {
            // Contract is not in a confirmed state
            return;
        }

        $salesperson = Actor::query()->where("name", "=", $this->odooContract->user_id[1])->first();
        if (!$salesperson) {
            $currentUser = Auth::user();
            if (!$currentUser) {
                return;
            }

            $salesperson = $currentUser;
        }

        $contract = new Contract([
            "contract_id"    => $this->odooContract->name,
            "salesperson_id" => $salesperson->getKey(),
        ]);

        $contract->save();

        ImportContractDataJob::dispatchSync($contract->getKey(), $this->odooContract);
        ImportContractReservations::dispatchSync($contract->getKey());
    }
}
