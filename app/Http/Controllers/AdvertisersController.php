<?php
/*
 * Copyright 2020 (c) Neo-OOH - All Rights Reserved
 * Unauthorized copying of this file, via any medium is strictly prohibited
 * Proprietary and confidential
 * Written by Valentin Dufois <vdufois@neo-ooh.com>
 *
 * @neo/api - AdvertisersController.php
 */

namespace Neo\Http\Controllers;

use Illuminate\Http\Response;
use Neo\Http\Requests\Advertisers\ListAdvertisersByIdRequest;
use Neo\Http\Requests\Advertisers\ListAdvertisersRequest;
use Neo\Http\Requests\Advertisers\ShowAdvertiserRequest;
use Neo\Http\Requests\Advertisers\UpdateAdvertiserRequest;
use Neo\Models\Advertiser;

class AdvertisersController {
    public function index(ListAdvertisersRequest $request): Response {
        return new Response(Advertiser::query()->get()->loadPublicRelations());
    }

    public function byId(ListAdvertisersByIdRequest $request): Response {
        return new Response(Advertiser::query()
                                      ->whereIn("id", $request->input("ids"))
                                      ->orderBy("name")
                                      ->get()
                                      ->loadPublicRelations());
    }

    public function show(ShowAdvertiserRequest $request, Advertiser $advertiser): Response {
        return new Response($advertiser->loadPublicRelations());
    }

    public function update(UpdateAdvertiserRequest $request, Advertiser $advertiser): Response {
        $advertiser->name    = $request->input("name");
        $advertiser->odoo_id = $request->input("odoo_id");
        $advertiser->save();

        return new Response($advertiser);
    }
}
