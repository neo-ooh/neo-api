<?php
/*
 * Copyright 2023 (c) Neo-OOH - All Rights Reserved
 * Unauthorized copying of this file, via any medium is strictly prohibited
 * Proprietary and confidential
 * Written by Valentin Dufois <vdufois@neo-ooh.com>
 *
 * @neo/api - MobileProductsBracketsController.php
 */

namespace Neo\Modules\Properties\Http\Controllers;

use Illuminate\Http\Response;
use Neo\Http\Controllers\Controller;
use Neo\Http\Requests\MobileProductsBrackets\DestroyMobileProductBracketRequest;
use Neo\Http\Requests\MobileProductsBrackets\ListMobileProductBracketsRequest;
use Neo\Http\Requests\MobileProductsBrackets\ShowMobileProductBracketRequest;
use Neo\Http\Requests\MobileProductsBrackets\StoreMobileProductBracketRequest;
use Neo\Http\Requests\MobileProductsBrackets\UpdateMobileProductBracketRequest;
use Neo\Modules\Properties\Models\MobileProduct;
use Neo\Modules\Properties\Models\MobileProductBracket;

class MobileProductsBracketsController extends Controller {
	public function index(ListMobileProductBracketsRequest $request, MobileProduct $mobileProduct): Response {
		return new Response($mobileProduct->brackets->loadPublicRelations());
	}

	public function store(StoreMobileProductBracketRequest $request, MobileProduct $mobileProduct): Response {
		$mobileProductBracket                    = new MobileProductBracket();
		$mobileProductBracket->mobile_product_id = $mobileProduct->getKey();
		$mobileProductBracket->cpm               = $request->input("cpm");
		$mobileProductBracket->budget_min        = $request->input("budget_min");
		$mobileProductBracket->budget_max        = $request->input("budget_max", null);
		$mobileProductBracket->impressions_min   = $request->input("impressions_min");
		$mobileProductBracket->save();

		return new Response($mobileProductBracket, 201);
	}

	public function show(ShowMobileProductBracketRequest $request, MobileProduct $mobileProduct, MobileProductBracket $mobileProductBracket): Response {
		return new Response($mobileProductBracket->loadPublicRelations());
	}

	public function update(UpdateMobileProductBracketRequest $request, MobileProduct $mobileProduct, MobileProductBracket $mobileProductBracket): Response {
		$mobileProductBracket->cpm             = $request->input("cpm");
		$mobileProductBracket->budget_min      = $request->input("budget_min");
		$mobileProductBracket->budget_max      = $request->input("budget_max", null);
		$mobileProductBracket->impressions_min = $request->input("impressions_min");
		$mobileProductBracket->save();

		return new Response($mobileProductBracket->loadPublicRelations());
	}

	public function delete(DestroyMobileProductBracketRequest $request, MobileProduct $mobileProduct, MobileProductBracket $mobileProductBracket): Response {
		$mobileProductBracket->delete();

		return new Response(["status" => "ok"]);
	}
}
