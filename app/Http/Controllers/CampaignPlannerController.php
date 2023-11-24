<?php
/*
 * Copyright 2023 (c) Neo-OOH - All Rights Reserved
 * Unauthorized copying of this file, via any medium is strictly prohibited
 * Proprietary and confidential
 * Written by Valentin Dufois <vdufois@neo-ooh.com>
 *
 * @neo/api - CampaignPlannerController.php
 */

namespace Neo\Http\Controllers;

use Illuminate\Database\Eloquent\Builder;
use Illuminate\Database\Eloquent\Collection;
use Illuminate\Http\Request;
use Illuminate\Http\Response;
use Illuminate\Support\Facades\DB;
use Neo\Http\Requests\CampaignPlanner\GetCampaignPlannerDataRequest;
use Neo\Http\Requests\CampaignPlanner\GetCampaignPlannerDemographicValuesRequest;
use Neo\Http\Requests\CampaignPlanner\GetCampaignPlannerTrafficRequest;
use Neo\Http\Requests\CampaignPlanner\ShowProductRequest;
use Neo\Http\Requests\CampaignPlanner\ShowPropertyRequest;
use Neo\Http\Resources\Planner\ProductResource;
use Neo\Http\Resources\Planner\PropertyResource;
use Neo\Models\CampaignPlannerSave;
use Neo\Models\City;
use Neo\Models\Tag;
use Neo\Modules\Broadcast\Models\Format;
use Neo\Modules\Properties\Models\Brand;
use Neo\Modules\Properties\Models\DemographicValue;
use Neo\Modules\Properties\Models\DemographicVariable;
use Neo\Modules\Properties\Models\Field;
use Neo\Modules\Properties\Models\FieldsCategory;
use Neo\Modules\Properties\Models\MobileProduct;
use Neo\Modules\Properties\Models\Pricelist;
use Neo\Modules\Properties\Models\Product;
use Neo\Modules\Properties\Models\ProductCategory;
use Neo\Modules\Properties\Models\Property;
use Neo\Modules\Properties\Models\PropertyNetwork;
use Neo\Modules\Properties\Models\PropertyTrafficSnapshot;
use Neo\Modules\Properties\Models\PropertyType;
use Neo\Modules\Properties\Models\ScreenType;

class CampaignPlannerController {
	protected function getPropertiesQuery() {
		return Property::withoutEagerLoads()
		               ->whereHas("network", function (Builder $query) {
			               $query->where("ooh_sales", "=", true)
			                     ->orWhere("mobile_sales", "=", true);
		               })
		               ->whereHas("address", function (Builder $query) {
			               $query->whereNotNull("geolocation");
		               });
	}

	public function dataChunk_1(GetCampaignPlannerDataRequest $request) {
		set_time_limit(120);

		/** @var Collection<Property> $properties */
		$properties = $this->getPropertiesQuery()
		                   ->select([
			                            "actor_id",
			                            "address_id",
			                            "mobile_impressions_per_week",
			                            "network_id",
			                            "pricelist_id",
			                            "website",
			                            "has_tenants",
			                            "cover_picture_id",
			                            "type_id",
		                            ])
		                   ->get();

		$properties->loadMissing([
			                         "actor" => function ($relation) {
				                         $relation->setEagerLoads([]);
			                         },
			                         "actor.tags:id",
			                         "translations",
			                         "address",
		                         ]);

//		$propertiesObj = $properties->map(function (Property $property) {
//			return [
//				"id"                          => $property->actor_id,
//				"is_sellable"                 => true,
//				"name"                        => $property->actor->name,
//				"address"                     => $property->address->makeHidden(["created_at", "updated_at"]),
//				"mobile_impressions_per_week" => $property->mobile_impressions_per_week,
//				"network_id"                  => $property->network_id,
//				"pricelist_id"                => $property->pricelist_id,
//				"translations"                => $property->translations->makeHidden(["created_at", "updated_at"]),
//				"website"                     => $property->website,
//				"has_tenants"                 => $property->has_tenants,
//				"tags"                        => $property->actor->tags,
//				"cover_picture_id"            => $property->cover_picture_id,
//				"type_id"                     => $property->type_id,
//			];
//		});

		return new Response([
			                    "properties" => PropertyResource::collection($properties),
		                    ]);
	}

	public function dataChunk_2(GetCampaignPlannerDataRequest $request) {
		/** @var Collection<Property> $properties */
		$properties = $this->getPropertiesQuery()
		                   ->select(["actor_id", "address_id"])
		                   ->get();

		$properties->load([
			                  "fields_values" => fn($q) => $q->select(["property_id", "fields_segment_id", "value", "reference_value", "index"])
			                                                 ->whereNull("reference_value"),
			                  "tenants"       => fn($q) => $q->select(["id"]),
		                  ]);

		$openingHours = DB::table("opening_hours")
		                  ->select("property_id", "weekday", "is_closed", "open_at", "close_at")
		                  ->get()
		                  ->groupBy("property_id")
		                  ->keyBy("0.property_id");

		return [
			"properties" => $properties->map(fn(Property $property) => [
				"id"            => $property->actor_id,
				"fields_values" => $property->fields_values,
				"tenants"       => $property->tenants->pluck('id'),
				"opening_hours" => $openingHours[$property->getKey()] ?? [],
			]),
		];
	}

	public function dataChunk_3(GetCampaignPlannerDataRequest $request) {
		/** @var Collection<Property> $properties */
		$properties = $this->getPropertiesQuery()
		                   ->select(["actor_id"])
		                   ->get();

		$properties->load([
			                  "products",
			                  "products.impressions_models",
		                  ]);

		return [
			"properties" => $properties->map(fn(Property $property) => [
				"id"                      => $property->actor_id,
				"products"                => ProductResource::collection($property->products),
				"products_ids"            => $property->products->pluck("id"),
				"products_categories_ids" => $property->products->groupBy("category_id")
				                                                ->map(static fn($products) => $products->pluck('id')),
			]),
		];
	}

	public function dataChunk_4(GetCampaignPlannerDataRequest $request) {
		$properties = $this->getPropertiesQuery()->get(["actor_id", "pricelist_id"]);

		$categories           = ProductCategory::query()
		                                       ->select(["id", "inventory_resource_id", "name_en", "name_fr", "type", "format_id", "allowed_media_types", "allows_audio", "allows_motion", "production_cost", "programmatic_price", "screen_size_in", "screen_type_id"])
		                                       ->with(["impressions_models", "attachments"])->get();
		$fieldCategories      = FieldsCategory::query()->select([
			                                                        "id",
			                                                        "name_en",
			                                                        "name_fr",
		                                                        ])->get();
		$fields               = Field::query()->select([
			                                               "id", "category_id", "order", "name_en", "name_fr", "type", "unit", "is_filter",
			                                               "demographic_filled", "visualization",
		                                               ])
		                             ->with(["segments.stats", "networks:id"])
		                             ->get();
		$demographicVariables = DemographicVariable::query()
		                                           ->select(["id", "label", "provider"])
		                                           ->get();
		$networks             = PropertyNetwork::query()
		                                       ->select(["id", "name", "slug", "color", "ooh_sales", "mobile_sales"])
		                                       ->get();
		$brands               = Brand::query()
		                             ->select(["id", "name_en", "name_fr", "parent_id"])
		                             ->with("child_brands:id,parent_id")->get();
		$pricelists           = Pricelist::query()
		                                 ->select(["id", "name", "description"])
		                                 ->whereIn("id", $properties->pluck("pricelist_id")->whereNotNull()->unique())
		                                 ->with(["categories_pricings", "products_pricings"])
		                                 ->get();
		$formats              = Format::query()
		                              ->select(["id", "slug", "network_id", "name", "content_length", "main_layout_id"])
		                              ->with("loop_configurations")
		                              ->get();
		$propertyTypes        = PropertyType::query()
		                                    ->select(["id", "name_en", "name_fr"])
		                                    ->get();
		$screenTypes          = ScreenType::query()
		                                  ->select(["id", "name_en", "name_fr"])
		                                  ->get();

		$mobileProducts = MobileProduct::query()
		                               ->select(["id", "name_en", "name_fr"])
		                               ->with([
			                                      "brackets" => fn($q) => $q->select(["id", "mobile_product_id", "budget_min", "budget_max", "cpm"]),
		                                      ])
		                               ->get();


		$cities = City::query()
		              ->get();

		$tags = Tag::query()->select(["id", "name", "color"])->get();

		return [
			"categories"            => $categories,
			"networks"              => $networks,
			"fields_categories"     => $fieldCategories,
			"fields"                => $fields,
			"demographic_variables" => $demographicVariables,
			"brands"                => $brands,
			"mobile_products"       => $mobileProducts,
			"pricelists"            => $pricelists,
			"formats"               => $formats,
			"property_types"        => $propertyTypes,
			"screen_types"          => $screenTypes,
			"cities"                => $cities,
			"tags"                  => $tags,
		];
	}

	public function trafficChunk(GetCampaignPlannerTrafficRequest $request) {
		$date = $request->input("date");

		// If no date is provided, we use the most recent snapshot available
		if (!$date) {
			$date = DB::query()->select("date")
			          ->from((new PropertyTrafficSnapshot())->getTable())
			          ->orderBy("date", "desc")
			          ->first()->date;
		}

		$snapshots = PropertyTrafficSnapshot::query()->where("date", "=", $date)->get();

		return [
			"traffic" => $snapshots,
		];
	}

	public function demographicValues(GetCampaignPlannerDemographicValuesRequest $request) {
		return new Response(DemographicValue::query()->whereIn("value_id", $request->input("variables"))->get());
	}

	public function save(Request $request, CampaignPlannerSave $campaignPlannerSave) {
		return new Response($campaignPlannerSave);
	}

	public function property(ShowPropertyRequest $request, CampaignPlannerSave $campaignPlannerSave) {
		$property = Property::query()
		                    ->find($request->input("property_id"))
		                    ->load([
			                           "address.city",
			                           "fields_values",
			                           "network.properties_fields",
			                           "opening_hours",
			                           "products.unavailabilities",
			                           "actor.tags",
			                           "translations",
			                           "unavailabilities",
			                           "pictures",
		                           ]);

		return new Response($property);
	}

	public function product(ShowProductRequest $request, CampaignPlannerSave $campaignPlannerSave) {
		$product = Product::query()
		                  ->find($request->input("product_id"))
		                  ->loadMissing([
			                                "attachments",
			                                "pricelist.categories_pricings",
			                                "pricelist.products_pricings",
			                                "pictures",
			                                "cover_picture",
			                                "format",
			                                "category.format",
			                                "pictures",
			                                "loop_configurations",
			                                "category.loop_configurations",
			                                "screen_type",
			                                "category.screen_type",
			                                "site_type",
			                                "property.type",
		                                ]);

		return new Response($product);
	}
}
