<?php
/*
 * Copyright 2022 (c) Neo-OOH - All Rights Reserved
 * Unauthorized copying of this file, via any medium is strictly prohibited
 * Proprietary and confidential
 * Written by Valentin Dufois <vdufois@neo-ooh.com>
 *
 * @neo/api - AdvertiserRepresentationsController.php
 */

namespace Neo\Http\Controllers;

use Illuminate\Http\Response;
use Neo\Exceptions\RepresentationAlreadyExistException;
use Neo\Http\Requests\AdvertisersRepresentations\DestroyAdvertiserRepresentationRequest;
use Neo\Http\Requests\AdvertisersRepresentations\StoreAdvertiserRepresentationRequest;
use Neo\Http\Requests\AdvertisersRepresentations\UpdateAdvertiserRepresentationRequest;
use Neo\Models\Advertiser;
use Neo\Models\AdvertiserRepresentation;
use Neo\Modules\Broadcast\Models\BroadcasterConnection;
use Symfony\Component\HttpKernel\Exception\NotFoundHttpException;

class AdvertiserRepresentationsController extends Controller {
    /**
     * @throws RepresentationAlreadyExistException
     */
    public function store(StoreAdvertiserRepresentationRequest $request, Advertiser $advertiser): Response {
        // make sure there isn't already a representation for the given broadcaster
        /** @var AdvertiserRepresentation|null $representation */
        $representation = $advertiser->representations()
                                     ->where("broadcaster_id", "=", $request->input("broadcaster_id"))
                                     ->first();

        if ($representation) {
            throw new RepresentationAlreadyExistException("advertiser representation");
        }

        $representation                 = new AdvertiserRepresentation();
        $representation->broadcaster_id = $request->input("broadcaster_id");
        $representation->advertiser_id  = $advertiser->getKey();
        $representation->external_id    = $request->input("external_id");

        $representation->save();

        return new Response($representation);
    }

    /**
     * @param UpdateAdvertiserRepresentationRequest $request
     * @param Advertiser                            $advertiser
     * @param BroadcasterConnection                 $broadcaster
     * @return Response
     */
    public function update(UpdateAdvertiserRepresentationRequest $request, Advertiser $advertiser, BroadcasterConnection $broadcaster): Response {
        /** @var AdvertiserRepresentation|null $representation */
        $representation = $advertiser->representations()->where("broadcaster_id", "=", $broadcaster->getKey())->first();

        if (!$representation) {
            throw new NotFoundHttpException();
        }

        $representation->external_id = $request->input("external_id");
        $representation->save();

        return new Response($representation);
    }

    /**
     * @param DestroyAdvertiserRepresentationRequest $request
     * @param Advertiser                             $advertiser
     * @param BroadcasterConnection                  $broadcaster
     * @return Response
     */
    public function destroy(DestroyAdvertiserRepresentationRequest $request, Advertiser $advertiser, BroadcasterConnection $broadcaster): Response {
        /** @var AdvertiserRepresentation|null $representation */
        $representation = $advertiser->representations()->where("broadcaster_id", "=", $broadcaster->getKey())->first();

        if (!$representation) {
            throw new NotFoundHttpException();
        }

        $representation->delete();

        return new Response(["status" => "ok"]);
    }
}
