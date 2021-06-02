<?php
/*
 * Copyright 2020 (c) Neo-OOH - All Rights Reserved
 * Unauthorized copying of this file, via any medium is strictly prohibited
 * Proprietary and confidential
 * Written by Valentin Dufois <vdufois@neo-ooh.com>
 *
 * @neo/api - TermsOfServiceController.php
 */

namespace Neo\Http\Controllers;

use Illuminate\Http\Response;
use Illuminate\Support\Facades\Auth;
use Neo\Http\Requests\TermsOfService\AcceptTermsOfServiceRequest;
use Neo\Models\Actor;
use Neo\Models\Param;

/**
 * Class TermsOfServiceController
 *
 * @package Neo\Http\Controllers
 */
class TermsOfServiceController extends Controller {
    public function show(): Response {
        /** @var Param $tos */
        $tos = Param::query()->find('tos');

        return new Response(["url" => $tos->value, "updated" => $tos->updated_at]);
    }

    public function accept(AcceptTermsOfServiceRequest $request): Response {
        /** @var Actor $actor */
        $actor               = Auth::user();
        $actor->tos_accepted = $request->validated()['accept'];
        $actor->save();

        return new Response(["token" => $actor->getJWT()]);
    }
}
