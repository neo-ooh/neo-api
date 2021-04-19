<?php

namespace Neo\Http\Controllers;

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
                                     ->when($request->has("network"), function (Builder $query) use ($request) {
                                         $query->where("network", "=", $request->input("network"));
                                     })
                                     ->when($request->has("format_id"), function (Builder $query) use ($request) {
                                         $query->where("format_id", "=", $request->input("format_id"));
                                     })
                                     ->when($request->has("locale"), function (Builder $query) use ($request) {
                                         $query->where("locale", "=", $request->input("locale"));
                                     })
                                     ->get();

        return new Response($backgrounds);
    }

    public function store(StoreBackgroundRequest $request) {
        $network = $request->input("network");
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

        if($existingBackground) {
            $existingBackground->delete();
        }

        // Start by validating the uploaded file
        $file = $request->file("background");

        if(!$file->isValid()) {
            throw new UploadException("An error occurred while uploading the background");
        }

        // The request validated that we actually received an image, we validated that the transfer was successful, and we delegate the creative dimensions validation to the front-end.

        $background = new NewsBackground();
        $background->category = $category;
        $background->network = $network;
        $background->format_id = $formatId;
        $background->locale = $locale;
        $background->path = $file->storePubliclyAs(Storage::path("dynamics/news/backgrounds/"), $file->hashName());
        $background->save();

        return new Response($background, 201);
    }

    public function destroy(DestroyBackgroundRequest $request, NewsBackground $newsBackground) {
        Storage::delete($newsBackground->path);
        $newsBackground->delete();

        return new Response([]);
    }
}
