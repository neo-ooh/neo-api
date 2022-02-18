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
use Neo\Models\Advertiser;

class AdvertisersController {
    public function byId(ListAdvertisersByIdRequest $request) {
        return new Response(Advertiser::query()->whereIn("id", $request->input("ids"))->orderBy("name")->get());
    }
}
