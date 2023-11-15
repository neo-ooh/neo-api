<?php
/*
 * Copyright 2023 (c) Neo-OOH - All Rights Reserved
 * Unauthorized copying of this file, via any medium is strictly prohibited
 * Proprietary and confidential
 * Written by Valentin Dufois <vdufois@neo-ooh.com>
 *
 * @neo/api - ExternalContractsController.php
 */

namespace Neo\Modules\Properties\Http\Controllers;

use Illuminate\Http\Response;
use Illuminate\Support\Facades\Auth;
use Illuminate\Support\Facades\Log;
use Neo\Exceptions\Odoo\ContractIsNotDraftException;
use Neo\Exceptions\Odoo\ContractNotFoundException;
use Neo\Http\Controllers\Controller;
use Neo\Jobs\Contracts\ImportContractJob;
use Neo\Jobs\Odoo\SendContractJob;
use Neo\Modules\Properties\Exceptions\Synchronization\UnsupportedInventoryFunctionalityException;
use Neo\Modules\Properties\Http\Requests\ExternalContracts\ExportContractRequest;
use Neo\Modules\Properties\Http\Requests\ExternalContracts\ImportContractRequest;
use Neo\Modules\Properties\Http\Requests\ExternalContracts\ShowContractRequest;
use Neo\Modules\Properties\Http\Resources\ExternalContractResource;
use Neo\Modules\Properties\Models\Contract;
use Neo\Modules\Properties\Models\InventoryProvider;
use Neo\Modules\Properties\Services\Exceptions\InvalidInventoryAdapterException;
use Neo\Modules\Properties\Services\Exceptions\InventoryResourceNotFound;
use Neo\Modules\Properties\Services\InventoryAdapterFactory;
use Neo\Modules\Properties\Services\InventoryCapability;
use Neo\Modules\Properties\Services\Resources\ContractResource;
use Neo\Modules\Properties\Services\Resources\Enums\ContractState;
use Neo\Resources\CampaignPlannerPlan\CompiledPlan\CPCompiledPlan;
use Symfony\Component\HttpKernel\Exception\HttpException;

class ExternalContractsController extends Controller {
	/**
	 * @throws InvalidInventoryAdapterException
	 * @throws UnsupportedInventoryFunctionalityException
	 */
	public function show(ShowContractRequest $request, string $contract_id) {
		// First, check if the specified inventory supports reading contracts
		$inventoryProvider = InventoryProvider::query()->find($request->input("inventory_id"));
		$inventory         = InventoryAdapterFactory::make($inventoryProvider);

		if (!$inventory->hasCapability(InventoryCapability::ContractsRead)) {
			throw new UnsupportedInventoryFunctionalityException($inventory->getInventoryID(), InventoryCapability::ContractsRead);
		}

		// Load and return the contract base information
		try {
			$externalContract = $inventory->findContract($contract_id);
		} catch (InventoryResourceNotFound) {
			throw new ContractNotFoundException($contract_id);
		}

		return new Response(ExternalContractResource::make($externalContract));
	}

	public function import(ImportContractRequest $request) {
		// First, check if the specified inventory supports reading contracts
		$inventoryProvider = InventoryProvider::query()->find($request->input("inventory_id"));
		$inventory         = InventoryAdapterFactory::make($inventoryProvider);

		if (!$inventory->hasCapability(InventoryCapability::ContractsRead)) {
			throw new UnsupportedInventoryFunctionalityException($inventory->getInventoryID(), InventoryCapability::ContractsRead);
		}

		// Import the contract base info right now to valid its state allows importing. Deffer the actual importing to a job
		$contractName = $request->input("contract_id");
		try {
			$externalContract = $inventory->findContract($contractName);
		} catch (InventoryResourceNotFound) {
			throw new ContractNotFoundException($contractName);
		}

//		if ($externalContract->state !== ContractState::Locked) {
//			 Cannot import a contract that is not locked
//			throw new ContractIsDraftException($contractName);
//		}

		$importer = new ImportContractJob($contractName, $externalContract);
		$importer->handle();

		if (!$importer->getImportedContractId()) {
			throw new HttpException(400, "Could not import contract.");
		}

		$contract = Contract::query()->find($importer->getImportedContractId());

		return new Response($contract, 201);
	}

	public function export(ExportContractRequest $request) {
		set_time_limit(120);
		// Validate that contract exist before doing anything
		$provider = InventoryProvider::query()->find($request->input("inventory_id"));

		$inventory = InventoryAdapterFactory::make($provider);
		if (!$inventory->hasCapability(InventoryCapability::ContractsWrite)) {
			throw new UnsupportedInventoryFunctionalityException($inventory->getInventoryID(), InventoryCapability::ContractsRead);
		}

		$contractName = $request->input("contract_id");

		/** @var ContractResource|null $contract */
		$contract = $inventory->findContract($contractName);

		if ($contract === null) {
			throw new ContractNotFoundException($contractName);
		}

		if ($contract->state !== ContractState::Draft) {
			throw new ContractIsNotDraftException($contract->name);
		}

		$plan = CPCompiledPlan::from($request->input("plan"));

		$messages = (new SendContractJob($contract, $plan, $request->input("clear")))->handle();

		Log::info("connect.log", [
			"action"       => "planner.contract.sent",
			"contract"     => $contract->name,
			"inventory_id" => $contract->contract_id->inventory_id,
			"sales_rep"    => Auth::user()->name,
		]);

		return new Response($messages);
	}
}
