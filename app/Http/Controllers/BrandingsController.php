<?php
/*
 * Copyright 2020 (c) Neo-OOH - All Rights Reserved
 * Unauthorized copying of this file, via any medium is strictly prohibited
 * Proprietary and confidential
 * Written by Valentin Dufois <vdufois@neo-ooh.com>
 *
 * @neo/api - BrandingsController.php
 */

/** @noinspection PhpUnusedParameterInspection */

namespace Neo\Http\Controllers;

use Exception;
use Illuminate\Http\Response;
use Neo\Http\Requests\Brandings\DestroyBrandingRequest;
use Neo\Http\Requests\Brandings\ListBrandingsRequest;
use Neo\Http\Requests\Brandings\StoreBrandingRequest;
use Neo\Http\Requests\Brandings\UpdateBrandingRequest;
use Neo\Models\Branding;

class BrandingsController extends Controller {
    /**
     * List all brandings in the system
     *
     * @param ListBrandingsRequest $request
     *
     * @return Response
     */
    public function index (ListBrandingsRequest $request): Response {
        return new Response(Branding::all());
    }

    /**
     * Creates a new, empty, branding with the specified name
     *
     * @param StoreBrandingRequest $request
     *
     * @return Response
     */
    public function store (StoreBrandingRequest $request): Response {
        [ 'name' => $name ] = $request->validated();

        $branding = new Branding([
            "name" => $name,
        ]);
        $branding->save();

        return new Response($branding, 201);
    }

    /**
     * Returns the request branding
     *
     * @param Branding $branding
     *
     * @return Response
     */
    public function show (Branding $branding): Response {
        return new Response($branding);
    }

    /**
     * Update the name of the specified branding
     *
     * @param UpdateBrandingRequest $request
     * @param Branding              $branding
     *
     * @return Response
     */
    public function update (UpdateBrandingRequest $request, Branding $branding): Response {
        [ 'name' => $name ] = $request->validated();

        $branding->name = $name;
        $branding->save();

        return new Response($branding);
    }

    /**
     * Deletes the specified branding with all its files
     *
     * @param DestroyBrandingRequest $request
     * @param Branding               $branding
     *
     * @return Response
     * @throws Exception
     */
    public function destroy (DestroyBrandingRequest $request, Branding $branding): Response {
        $files = $branding->files;

        foreach ($files as $file) {
            $file->delete();
        }

        $branding->delete();

        return new Response([]);
    }
}
