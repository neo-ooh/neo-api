<?php
/*
 * Copyright 2020 (c) Neo-OOH - All Rights Reserved
 * Unauthorized copying of this file, via any medium is strictly prohibited
 * Proprietary and confidential
 * Written by Valentin Dufois <Valentin Dufois>
 *
 * @neo/api - ParamsController.php
 */

namespace Neo\Http\Controllers;

use Illuminate\Contracts\Foundation\Application;
use Illuminate\Contracts\Routing\ResponseFactory;
use Illuminate\Http\Request;
use Illuminate\Http\Response;
use Illuminate\Routing\Controller;
use Illuminate\Support\Facades\Storage;
use Illuminate\Support\Facades\Validator;
use Illuminate\Support\Str;
use Illuminate\Validation\ValidationException;
use Neo\Models\Actor;
use Neo\Models\Param;

/**
 * Class ParamsController
 *
 * @package Neo\Http\Controllers
 */
class ParamsController extends Controller {
    /**
     * @param Param $parameter
     * @return Response
     */
    public function show (Param $parameter): Response {
        return new Response($parameter);
    }

    /**
     * @param Request $request
     * @param Param   $parameter
     *
     * @return Application|ResponseFactory|Response
     * @throws ValidationException
     */
    public function update (Request $request, Param $parameter) {
        if (Str::startsWith($parameter->format, "file:")) {
            $file = $request->file("value");

            // Confirm upload success
            if (!$file->isValid()) {
                return new Response([
                    "code"    => "upload.error",
                    "message" => "Error during upload",
                ],
                    400);
            }

            $fileType = explode(":", $parameter->format)[1];

            // Validate the file
            $validator = Validator::make([ "value" => $file ], [ "value" => "file|mimes:{$fileType}" ]);
            if ($validator->fails()) {
                throw new ValidationException($validator);
            }

            // File is OK, store it properly
            if ($parameter->slug === "tos") {
                $fileName = "terms-of-service.pdf";
                if (Storage::exists($fileName)) {
                    Storage::delete($fileName);
                }

                $file->storePubliclyAs('/', $fileName);
                $parameter->value = Storage::url($fileName);

                // Tos have been updated, now require everyone to accept it again
                Actor::query()->update(['tos_accepted' => false]);
            }
        } else {
            $parameter->value = $request->get("value");
        }

        $parameter->save();

        return new Response($parameter);
    }
}
