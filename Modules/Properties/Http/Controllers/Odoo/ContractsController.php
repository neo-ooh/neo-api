<?php
/*
 * Copyright 2023 (c) Neo-OOH - All Rights Reserved
 * Unauthorized copying of this file, via any medium is strictly prohibited
 * Proprietary and confidential
 * Written by Valentin Dufois <vdufois@neo-ooh.com>
 *
 * @neo/api - ContractsController.php
 */

namespace Neo\Modules\Properties\Http\Controllers\Odoo;

use Carbon\Carbon;
use Edujugon\Laradoo\Exceptions\OdooException;
use Illuminate\Http\Response;
use Illuminate\Support\Facades\Auth;
use Illuminate\Support\Facades\Log;
use Neo\Exceptions\Odoo\ContractIsNotDraftException;
use Neo\Exceptions\Odoo\ContractNotFoundException;
use Neo\Modules\Properties\Http\Requests\Odoo\Contracts\SendContractRequest;
use Neo\Modules\Properties\Http\Requests\Odoo\Contracts\ShowContractRequest;
use Neo\Modules\Properties\Jobs\Contracts\SendContractJob;
use Neo\Modules\Properties\Models\InventoryProvider;
use Neo\Modules\Properties\Services\Exceptions\InvalidInventoryAdapterException;
use Neo\Modules\Properties\Services\InventoryAdapterFactory;
use Neo\Modules\Properties\Services\Odoo\Models\Contract;
use Neo\Modules\Properties\Services\Odoo\Models\Contract as OdooContract;
use Neo\Modules\Properties\Services\Odoo\OdooAdapter;
use Neo\Resources\CampaignPlannerPlan\CompiledPlan\CPCompiledPlan;

class ContractsController {
	/**
	 * @throws InvalidInventoryAdapterException
	 * @throws OdooException
	 */
	public function show(ShowContractRequest $request, string $contractName) {
		$inventory = InventoryProvider::query()->find(1);
		/** @var OdooAdapter $odoo */
		$odoo = InventoryAdapterFactory::make($inventory);
		// Get the contract from Odoo
		$contract = OdooContract::findByName($odoo->getConfig()->getClient(), strtoupper($contractName));

		if ($contract === null) {
			throw new ContractNotFoundException($contractName);
		}

		if ($contract->isConfirmed()) {
			throw new ContractIsNotDraftException($contractName);
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
			                    "date_order"       => Carbon::createFromFormat("Y-m-d H:i:s", $contract->date_order)
			                                                ->toDateString(),
		                    ]);
	}

	/**
	 * @throws InvalidInventoryAdapterException
	 * @throws OdooException
	 */
	public function send(SendContractRequest $request, string $contractName) {
		set_time_limit(120);
		// Validate that contract exist before doing anything
		$inventory = InventoryProvider::query()->find(1);
		/** @var OdooAdapter $odoo */
		$odoo = InventoryAdapterFactory::make($inventory);
		/** @var Contract|null $contract */
		$contract = OdooContract::findByName($odoo->getConfig()->getClient(), strtoupper($contractName));

		if ($contract === null) {
			throw new ContractNotFoundException($contractName);
		}

		if ($contract->isConfirmed()) {
			throw new ContractIsNotDraftException($contract->name);
		}

		$plan = CPCompiledPlan::from($request->input("plan"));

		$messages = (new SendContractJob($contract, $plan, $request->input("clearOnSend")))->handle();

		Log::info("connect.log", [
			"action"    => "planner.odoo.sent",
			"contract"  => $contract->name,
			"sales_rep" => Auth::user()->name,
		]);

		return new Response($messages);
	}
}
