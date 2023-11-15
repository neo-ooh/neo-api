<?php
/*
 * Copyright 2023 (c) Neo-OOH - All Rights Reserved
 * Unauthorized copying of this file, via any medium is strictly prohibited
 * Proprietary and confidential
 * Written by Valentin Dufois <vdufois@neo-ooh.com>
 *
 * @neo/api - ContractFlightsController.php
 */

namespace Neo\Http\Controllers;

use Carbon\Carbon;
use Illuminate\Database\Eloquent\Builder;
use Illuminate\Http\Response;
use Illuminate\Support\Facades\Gate;
use Neo\Enums\Capability;
use Neo\Http\Requests\ContractsFlights\ListFlightsRequest;
use Neo\Http\Requests\ContractsFlights\ShowFlightRequest;
use Neo\Models\Actor;
use Neo\Modules\Properties\Models\ContractFlight;
use Neo\Modules\Properties\Models\Product;

class ContractFlightsController extends Controller {
	public function index(ListFlightsRequest $request) {
		$query = ContractFlight::query();

		if (!Gate::allows(Capability::contracts_manage->value)) {
			$query->whereHas("contract", function (Builder $query) {
				$query->where("salesperson_id", "=", Actor::id());
			});
		}

		if ($request->has("property_id")) {
			$products = Product::query()->where("property_id", "=", $request->input("property_id"));

			$query->whereHas("lines", function (Builder $query) use ($products) {
				$query->whereIn("product_id", $products->pluck("id"));
			});
		}

		if ($request->has("product_id")) {
			$query->whereHas("lines", function (Builder $query) use ($request) {
				$query->where("product_id", "=", $request->input("product_id"));
			});
		}

		if ($request->input("current", false)) {
			$query->where("start_date", "<=", Carbon::now())
			      ->where("end_date", ">=", Carbon::now());
		}

		if ($request->input("past", false)) {
			$query->where("end_date", "<", Carbon::now());
		}

		if ($request->input("future", false)) {
			$query->where("start_date", ">", Carbon::now());
		}

		return new Response($query->distinct()->get()->loadPublicRelations());
	}

	public function show(ShowFlightRequest $request, ContractFlight $flight) {
		return new Response($flight->loadPublicRelations());
	}
}
