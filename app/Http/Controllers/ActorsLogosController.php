<?php
/*
 * Copyright 2020 (c) Neo-OOH - All Rights Reserved
 * Unauthorized copying of this file, via any medium is strictly prohibited
 * Proprietary and confidential
 * Written by Valentin Dufois <vdufois@neo-ooh.com>
 *
 * @neo/api - ActorsLogosController.php
 */

namespace Neo\Http\Controllers;

use Illuminate\Http\Response;
use Illuminate\Http\UploadedFile;
use Neo\Http\Requests\ActorsLogos\DestroyActorLogoRequest;
use Neo\Http\Requests\ActorsLogos\StoreActorLogoRequest;
use Neo\Models\Actor;

class ActorsLogosController extends Controller {
    public function store(StoreActorLogoRequest $request, Actor $actor) {
        // Get the file
        /** @var UploadedFile $uploadedFile */
        $uploadedFile = $request->file("file");

        if (!$uploadedFile->isValid()) {
            return new Response([
                "code"    => "upload.error",
                "message" => "Error during upload",
            ],
                400);
        }

        // Remove the actor logo if one is already present
        if ($actor->logo !== null) {
            $actor->logo->erase();
        }

        // The request has already validated that the file is an image, but we want to make sure we only store pngs
        $actorLogo = $actor->logo()->make([
            "original_name" => $uploadedFile->getClientOriginalName(),
        ]);
        $actorLogo->save();

        $actorLogo->store($uploadedFile);

        return new Response($actorLogo, 201);
    }

    public function destroy(DestroyActorLogoRequest $request, Actor $actor) {
        if ($actor->logo !== null) {
            $actor->logo->erase();
        }

        return new Response();
    }
}
