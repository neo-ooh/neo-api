<?php
/*
 * Copyright 2020 (c) Neo-OOH - All Rights Reserved
 * Unauthorized copying of this file, via any medium is strictly prohibited
 * Proprietary and confidential
 * Written by Valentin Dufois <vdufois@neo-ooh.com>
 *
 * @neo/api - BrandingsFilesController.php
 */

/** @noinspection PhpUnusedParameterInspection */

namespace Neo\Http\Controllers;

use Exception;
use Illuminate\Http\Response;
use Illuminate\Http\UploadedFile;
use Neo\Http\Requests\BrandingsFiles\DestroyBrandingFileRequest;
use Neo\Http\Requests\BrandingsFiles\ListBrandingFilesRequest;
use Neo\Http\Requests\BrandingsFiles\StoreBrandingFileRequest;
use Neo\Models\Branding;
use Neo\Models\BrandingFile;

class BrandingsFilesController extends Controller {
    /**
     * List all files in the specified branding with their type (logo, background, etc.)
     *
     * @param ListBrandingFilesRequest $request
     * @param Branding                 $branding
     *
     * @return Response
     */
    public function index(ListBrandingFilesRequest $request, Branding $branding): Response {
        return new Response($branding->files);
    }

    /**
     * Add a new file to the branding.
     *
     * If another file already exist for the specified type, it will be replaced
     *
     * @param StoreBrandingFileRequest $request
     * @param Branding                 $branding
     *
     * @return Response
     * @throws Exception
     */
    public function store(StoreBrandingFileRequest $request, Branding $branding): Response {
        ["type" => $type] = $request->validated();
        /** @var UploadedFile $uploadedFile */
        $uploadedFile = $request->file("file");

        if (!$uploadedFile->isValid()) {
            return new Response([
                "code"    => "upload.error",
                "message" => "Error during upload",
            ],
                400);
        }

        // Is there already a file in the branding for the specified type ?
        /** @var BrandingFile $file */
        $file = $branding->files()->where('type', '=', $type)->first();

        if (!is_null($file)) {
            // A file already exists, remove it
            $file->erase();
        }
        $filename = $branding->id . "_" . $type . '.' . $uploadedFile->extension();

        $file = new BrandingFile([
            "type"          => $type,
            "branding_id"   => $branding->id,
            "filename"      => $filename,
            "original_name" => $uploadedFile->getClientOriginalName(),
        ]);
        $file->save();

        $file->store($uploadedFile);

        return new Response($file, 201);
    }

    /**
     * Removes the specified file from the branding, leaving its place empty
     *
     * @param DestroyBrandingFileRequest $request
     *
     * @return Response
     * @throws Exception
     */
    public function destroy(DestroyBrandingFileRequest $request, Branding $branding, BrandingFile $file): Response {
        $file->erase();

        return new Response([]);
    }
}
