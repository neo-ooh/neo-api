<?php
/*
 * Copyright 2023 (c) Neo-OOH - All Rights Reserved
 * Unauthorized copying of this file, via any medium is strictly prohibited
 * Proprietary and confidential
 * Written by Valentin Dufois <vdufois@neo-ooh.com>
 *
 * @neo/api - MobileProductsController.php
 */

namespace Neo\Modules\Properties\Http\Controllers;

use Illuminate\Http\Response;
use Neo\Http\Controllers\Controller;
use Neo\Http\Requests\MobileProducts\DestroyMobileProductRequest;
use Neo\Http\Requests\MobileProducts\ListMobileProductsRequest;
use Neo\Http\Requests\MobileProducts\ShowMobileProductRequest;
use Neo\Http\Requests\MobileProducts\StoreMobileProductRequest;
use Neo\Http\Requests\MobileProducts\UpdateMobileProductRequest;
use Neo\Modules\Properties\Models\MobileProduct;

class MobileProductsController extends Controller {
	public function index(ListMobileProductsRequest $request) {
		return new Response(MobileProduct::query()->get()->loadPublicRelations());
	}

	public function store(StoreMobileProductRequest $request) {
		$mobileProduct          = new MobileProduct();
		$mobileProduct->name_en = $request->input("name_en");
		$mobileProduct->name_fr = $request->input("name_fr");
		$mobileProduct->save();

		return new Response($mobileProduct, 201);
	}

	public function show(ShowMobileProductRequest $request, MobileProduct $mobileProduct) {
		return new Response($mobileProduct->loadPublicRelations());
	}

	public function update(UpdateMobileProductRequest $request, MobileProduct $mobileProduct) {
		$mobileProduct->name_en = $request->input("name_en");
		$mobileProduct->name_fr = $request->input("name_fr");
		$mobileProduct->save();

		return new Response($mobileProduct->loadPublicRelations());
	}

	public function delete(DestroyMobileProductRequest $request, MobileProduct $mobileProduct) {
		$mobileProduct->delete();

		return new Response($mobileProduct);
	}
}
