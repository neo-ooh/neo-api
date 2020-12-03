<?php
/*
 * Copyright 2020 (c) Neo-OOH - All Rights Reserved
 * Unauthorized copying of this file, via any medium is strictly prohibited
 * Proprietary and confidential
 * Written by Valentin Dufois <Valentin Dufois>
 *
 * @neo/api - $file.filePath
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
use Neo\Models\Param;

/**
 * Class ParamsController
 *
 * @package Neo\Http\Controllers
 */
class ParamsController extends Controller {
    /**
     * @param Param $param
     *
     * @return Response
     */
    public function show (Param $param): Response {
        return new Response($param);
    }

    /**
     * @param Request $request
     * @param Param   $param
     *
     * @return Application|ResponseFactory|Response
     * @throws ValidationException
     */
    public function update (Request $request, Param $param) {
        if (Str::startsWith($param->format, "file:")) {
            $file = $request->file("value");

            // Confirm upload success
            if (!$file->isValid()) {
                return new Response([
                    "code"    => "upload.error",
                    "message" => "Error during upload",
                ],
                    400);
            }

            $fileType = explode(":", $param->format)[1];

            // Validate the file
            $validator = Validator::make([ "value" => $file ], [ "value" => "file|mimes:{$fileType}" ]);
            if ($validator->fails()) {
                throw new ValidationException($validator);
            }

            // File is OK, store it properly
            if ($param->slug === "tos") {
                $fileName = "terms-of-service.pdf";
                if (Storage::exists($fileName)) {
                    Storage::delete($fileName);
                }

                Storage::putFile($fileName, $file, [ "visibility" => "public" ]);

                $param->value = Storage::url($fileName);
            }
        } else {
            $param->value = $request->validated()["value"];
        }

        $param->save();

        return new Response($param);
    }
}
