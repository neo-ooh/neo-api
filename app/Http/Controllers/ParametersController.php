<?php
/*
 * Copyright 2020 (c) Neo-OOH - All Rights Reserved
 * Unauthorized copying of this file, via any medium is strictly prohibited
 * Proprietary and confidential
 * Written by Valentin Dufois <vdufois@neo-ooh.com>
 *
 * @neo/api - ParametersController.php
 */

namespace Neo\Http\Controllers;

use Illuminate\Http\Response;
use Illuminate\Http\UploadedFile;
use Illuminate\Routing\Controller;
use Illuminate\Support\Facades\Storage;
use Illuminate\Support\Str;
use Illuminate\Validation\ValidationException;
use Neo\Enums\CommonParameters;
use Neo\Http\Requests\Parameters\UpdateParameterRequest;
use Neo\Models\Actor;
use Neo\Models\Parameter;
use Symfony\Component\HttpFoundation\File\Exception\UploadException;

/**
 * Class ParametersController
 *
 * @package Neo\Http\Controllers
 */
class ParametersController extends Controller {
    public function index(): Response {
        return new Response(Parameter::query()->get());
    }

    /**
     * @param Parameter $parameter
     * @return Response
     */
    public function show(Parameter $parameter): Response {
        return new Response($parameter);
    }

    /**
     * @param UpdateParameterRequest $request
     * @param Parameter              $parameter
     *
     * @return Response
     * @throws ValidationException
     */
    public function update(UpdateParameterRequest $request, Parameter $parameter): Response {
        if (Str::startsWith($parameter->format, "file:")) {
            $file = $request->file("value");

            // Confirm upload success
            if (!$file->isValid()) {
                throw new UploadException();
            }

            $this->handleFileParameter($parameter, $file);
        }

        $parameter->value = $request->input("value");
        $parameter->save();

        return new Response($parameter);
    }

    protected function handleFileParameter(Parameter $parameter, UploadedFile $file): void {
        if ($parameter->slug === CommonParameters::TermsOfService->value) {
            $fileName = "$parameter->slug.pdf";
            if (Storage::disk("public")->exists($fileName)) {
                Storage::disk("public")->delete($fileName);
            }

            $parameter->value = Storage::disk("public")->putFileAs("/common", $file, $fileName, ["visibility" => "public"]);

            // Tos have been updated, now require everyone to accept them again
            Actor::query()->update(['tos_accepted' => false]);
        }
    }
}
