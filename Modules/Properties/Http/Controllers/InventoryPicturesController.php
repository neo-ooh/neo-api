<?php
/*
 * Copyright 2023 (c) Neo-OOH - All Rights Reserved
 * Unauthorized copying of this file, via any medium is strictly prohibited
 * Proprietary and confidential
 * Written by Valentin Dufois <vdufois@neo-ooh.com>
 *
 * @neo/api - InventoryPicturesController.php
 */

namespace Neo\Modules\Properties\Http\Controllers;

use Illuminate\Http\Response;
use Illuminate\Support\Facades\Storage;
use Neo\Modules\Broadcast\Utils\ThumbnailCreator;
use Neo\Modules\Properties\Http\Requests\InventoryPictures\ListPicturesRequest;
use Neo\Modules\Properties\Http\Requests\InventoryPictures\StorePictureRequest;
use Neo\Modules\Properties\Http\Requests\InventoryPictures\UpdatePictureRequest;
use Neo\Modules\Properties\Http\Requests\Products\DestroyProductRequest;
use Neo\Modules\Properties\Models\InventoryPicture;
use Symfony\Component\HttpFoundation\File\Exception\UploadException;

class InventoryPicturesController {
    public function index(ListPicturesRequest $request): Response {
        $query = InventoryPicture::query();

        if ($request->has("property_id")) {
            $query->where("property_id", "=", $request->input("property_id"));
        }

        if ($request->has("product_id")) {
            $query->where("product_id", "=", $request->input("property_id"));
        }

        $pictures = $query->get();

        return new Response($pictures);
    }

    public function store(StorePictureRequest $request): Response {
        $image = $request->file("picture");

        if (!$image->isValid()) {
            throw new UploadException($image->getErrorMessage(), $image->getError());
        }

        [$width, $height] = getimagesize($image);

        // Image is valid, create a resource for it and store it
        $picture = new InventoryPicture([
                                            "name"        => $image->getClientOriginalName(),
                                            "order"       => 0,
                                            "width"       => $width,
                                            "height"      => $height,
                                            "property_id" => $request->input("property_id", null),
                                            "product_id"  => $request->input("product_id", null),
                                            "extension"   => $image->extension(),
                                        ]);

        $picture->save();

        Storage::disk("public")
               ->putFileAs("/properties/pictures", $image, "$picture->uid.$picture->extension", ["visibility" => "public"]);

        $creator = new ThumbnailCreator($image);
        Storage::disk("public")
               ->writeStream($picture->thumbnail_path, $creator->getThumbnailAsStream(), ["visibility" => "public"]);

        return new Response($picture->refresh(), 201);
    }

    public function update(UpdatePictureRequest $request, InventoryPicture $picture): Response {
        $picture->name        = $request->input("name") ?? "";
        $picture->description = $request->input("description") ?? "";
        $picture->product_id  = $request->input("product_id", null);
        $picture->order       = $request->input("order");

        $picture->save();

        return new Response($picture);
    }

    public function destroy(DestroyProductRequest $request, InventoryPicture $picture): Response {
        $picture->delete();
        return new Response();
    }
}
