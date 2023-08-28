<?php
/*
 * Copyright 2023 (c) Neo-OOH - All Rights Reserved
 * Unauthorized copying of this file, via any medium is strictly prohibited
 * Proprietary and confidential
 * Written by Valentin Dufois <vdufois@neo-ooh.com>
 *
 * @neo/api - StatsController.php
 */

namespace Neo\Modules\Properties\Http\Controllers\Public;

use Illuminate\Database\Eloquent\Builder;
use Illuminate\Http\Response;
use Neo\Http\Controllers\Controller;
use Neo\Modules\Properties\Http\Requests\Public\Stats\ShowStatsRequest;
use Neo\Modules\Properties\Models\Product;
use Neo\Modules\Properties\Models\Property;

class StatsController extends Controller {
	public function show(ShowStatsRequest $request) {
		$properties = Property::query()
		                      ->when($request->has("product_id"), function (Builder $query) use ($request) {
			                      $query->whereHas("products", function (Builder $query) use ($request) {
				                      $query->where("id", "=", $request->input("product_id"));
			                      });
		                      })
		                      ->when($request->has("category_id"), function (Builder $query) use ($request) {
			                      $query->whereHas("products", function (Builder $query) use ($request) {
				                      $query->where("category_id", "=", $request->input("category_id"));
			                      });
		                      })
		                      ->when($request->has("market_id"), function (Builder $query) use ($request) {
			                      $query->whereHas("address", function (Builder $query) use ($request) {
				                      $query->whereHas("city", function (Builder $query) use ($request) {
					                      $query->where("market_id", "=", $request->input("market_id"));
				                      });
			                      });
		                      })
		                      ->where("is_sellable", "=", true)
		                      ->whereHas("products", function (Builder $query) {
			                      $query->where("is_bonus", "=", false);
			                      $query->where("is_sellable", "=", true);
		                      })
		                      ->whereHas("address", function (Builder $query) {
			                      $query->whereNotNull("geolocation");
		                      })
		                      ->with("traffic.weekly_data")
		                      ->get();

		$products = Product::query()
		                   ->when($request->has("product_id"), function (Builder $query) use ($request) {
			                   $query->where("id", "=", $request->input("product_id"));
		                   })
		                   ->when($request->has("category_id"), function (Builder $query) use ($request) {
			                   $query->where("category_id", "=", $request->input("category_id"));
		                   })
		                   ->when($request->has("market_id"), function (Builder $query) use ($request) {
			                   $query->whereHas("property", function (Builder $query) use ($request) {
				                   $query->whereHas("address", function (Builder $query) use ($request) {
					                   $query->whereHas("city", function (Builder $query) use ($request) {
						                   $query->where("market_id", "=", $request->input("market_id"));
					                   });
				                   });
			                   });
		                   })
		                   ->where("is_bonus", "=", false)
		                   ->where("is_sellable", "=", true)
		                   ->whereHas("property", function (Builder $query) use ($request) {
			                   $query->where("is_sellable", "=", true);
			                   $query->whereHas("address", function (Builder $query) {
				                   $query->whereNotNull("geolocation");
			                   });
		                   })->get();

		return new Response([
			                    "properties_count" => $properties->count(),
			                    "products_count"   => $products->count(),
			                    "faces_count"      => $products->sum("quantity"),
			                    "networks_count"   => $properties->pluck("network_id")->unique()->count(),
			                    "traffic"          => $properties->flatMap(fn(Property $p) => $p->traffic->weekly_data->slice(-4)
			                                                                                                          ->pluck("traffic"))
			                                                     ->sum(),
			                    "impressions" =>
		                    ]);
	}
}
