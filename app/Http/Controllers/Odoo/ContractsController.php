<?php
/*
 * Copyright 2020 (c) Neo-OOH - All Rights Reserved
 * Unauthorized copying of this file, via any medium is strictly prohibited
 * Proprietary and confidential
 * Written by Valentin Dufois <vdufois@neo-ooh.com>
 *
 * @neo/api - ContractsController.php
 */

namespace Neo\Http\Controllers\Odoo;

use Illuminate\Http\Response;
use Neo\Http\Requests\Odoo\Contracts\SendContractRequest;
use Neo\Http\Requests\Odoo\Contracts\ShowContractRequest;
use Neo\Jobs\Odoo\SendContractJob;
use Neo\Services\Odoo\Models\Contract;
use Neo\Services\Odoo\OdooConfig;
use Symfony\Component\Routing\Exception\ResourceNotFoundException;

class ContractsController {
    public function show(ShowContractRequest $request, string $contractName) {
        // Get the contract from Odoo
        $contract = Contract::findByName(OdooConfig::fromConfig()->getClient(), strtoupper($contractName));

        if ($contract === null) {
            return new ResourceNotFoundException("Could not found any contract with name $contractName");
        }

        return new Response([
            "name"             => $contract->name,
            "display_name"     => $contract->display_name,
            "user"             => $contract->user_id,
            "partner"          => $contract->partner_id,
            "partner_invoice"  => $contract->partner_invoice_id,
            "analytic_account" => $contract->analytic_account_id,
            "order_line"       => $contract->order_line,
            "company"          => $contract->company_id,
            "campaign_ids"     => $contract->campaign_ids,
            "access_url"       => $contract->access_url,
            "date_order"       => $contract->date_order,
        ]);
    }

    public function send(SendContractRequest $request, string $contractName) {
        // Validate that contract exist before doing anything
        $contract = Contract::findByName(OdooConfig::fromConfig()->getClient(), strtoupper($contractName));

        if ($contract === null) {
            return new ResourceNotFoundException("Could not found any contract with name $contractName");
        }

        SendContractJob::dispatch($contract, $request->input("flights"), $request->input("clearOnSend"));

        return new Response([]);
    }
}
