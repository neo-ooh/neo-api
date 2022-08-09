<?php
/*
 * Copyright 2020 (c) Neo-OOH - All Rights Reserved
 * Unauthorized copying of this file, via any medium is strictly prohibited
 * Proprietary and confidential
 * Written by Valentin Dufois <vdufois@neo-ooh.com>
 *
 * @neo/api - NewsBackgroundsController.php
 */

namespace Neo\Http\Controllers;

use http\Exception\RuntimeException;
use Illuminate\Database\Eloquent\Builder;
use Illuminate\Http\Response;
use Neo\Http\Requests\NewsBackgrounds\DestroyBackgroundRequest;
use Neo\Http\Requests\NewsBackgrounds\ListBackgroundsRequest;
use Neo\Http\Requests\NewsBackgrounds\StoreBackgroundRequest;
use Neo\Models\NewsBackground;
use Storage;
use Symfony\Component\HttpFoundation\File\Exception\UploadException;

class NewsBackgroundsController extends Controller {
    public function index(ListBackgroundsRequest $request) {
        $backgrounds = NewsBackground::query()
                                     ->where("network", "=", $request->input("network"))
                                     ->when($request->filled("format_id"), function (Builder $query) use ($request) {
                                         $query->where("format_id", "=", $request->input("format_id"));
                                     })
                                     ->when($request->has("categories"), function (Builder $query) use ($request) {
                                         $query->whereIn("category", $request->input("categories"));
                                     })
                                     ->get();

        return new Response($backgrounds);
    }

    public function store(StoreBackgroundRequest $request) {
        $network  = $request->input("network");
        $formatId = $request->input("format_id");
        $category = $request->input("category");
        $locale   = $request->input("locale");

        // Check if we already have a background for the specified parameters
        $existingBackground = NewsBackground::query()
                                            ->where("category", "=", $category)
                                            ->where("network", "=", $network)
                                            ->where("format_id", "=", $formatId)
                                            ->where('locale', "=", $locale)
                                            ->first();

        $existingBackground?->delete();

        // Start by validating the uploaded file
        $file = $request->file("background");

        if (!$file->isValid()) {
            throw new UploadException("An error occurred while uploading the background");
        }

        // The request validated that we actually received an image, we validated that the transfer was successful, and we delegate the creative dimensions validation to the front-end.

        // Store the file
        $path = Storage::disk("public")
                       ->putFileAs("dynamics/news/backgrounds", $file, $file->hashName(), ["visibility" => "public"]);

        if (!$path) {
            // Error when storing
            throw new RuntimeException("Could not store file. CDN may be temporarily unavailable");
        }

        $background            = new NewsBackground();
        $background->category  = $category;
        $background->network   = $network;
        $background->format_id = $formatId;
        $background->locale    = $locale;
        $background->path      = $path;

        $background->save();

        return new Response($background, 201);
    }

    public function destroy(DestroyBackgroundRequest $request, NewsBackground $newsBackground) {
        $newsBackground->delete();

        return new Response([]);
    }
}
