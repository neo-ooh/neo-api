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

use Carbon\Carbon;
use Illuminate\Http\Response;
use Illuminate\Support\Facades\Auth;
use Illuminate\Support\Facades\Log;
use InvalidArgumentException;
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

        Log::info("connect.log", [
            "action"    => "planner.assoc",
            "contract"  => $contract->name,
            "sales_rep" => Auth::user()->name
        ]);

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
            "date_order"       => Carbon::createFromFormat("Y-m-d H:i:s", $contract->date_order)->toDateString(),
        ]);
    }

    public function send(SendContractRequest $request, string $contractName) {
        // Validate that contract exist before doing anything
        $contract = Contract::findByName(OdooConfig::fromConfig()->getClient(), strtoupper($contractName));

        if ($contract === null) {
            return new ResourceNotFoundException("Could not found any contract with name $contractName");
        }

        if ($contract->state !== 'draft' && $contract->state !== 'sale') {
            return new InvalidArgumentException("Cannot update a contract whose state is " . $contract->state);
        }

        SendContractJob::dispatchSync($contract, $request->input("flights"), $request->input("clearOnSend"));

        Log::info("connect.log", [
            "action"    => "planner.odoo.sent",
            "contract"  => $contract->name,
            "sales_rep" => Auth::user()->name
        ]);

        return new Response([]);
    }
}
