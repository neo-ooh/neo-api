<?php
/*
 * Copyright 2023 (c) Neo-OOH - All Rights Reserved
 * Unauthorized copying of this file, via any medium is strictly prohibited
 * Proprietary and confidential
 * Written by Valentin Dufois <vdufois@neo-ooh.com>
 *
 * @neo/api - PropertyPicturesController.php
 */

namespace Neo\Modules\Properties\Http\Controllers;

use Illuminate\Http\Response;
use Illuminate\Support\Facades\Storage;
use Neo\Modules\Properties\Http\Requests\PropertiesPictures\StorePictureRequest;
use Neo\Modules\Properties\Http\Requests\PropertiesPictures\UpdatePictureRequest;
use Neo\Modules\Properties\Models\Property;
use Neo\Modules\Properties\Models\PropertyPicture;
use Symfony\Component\HttpFoundation\File\Exception\UploadException;

class PropertyPicturesController {
    public function index(Property $property): Response {
        return new Response($property->pictures);
    }

    public function store(StorePictureRequest $request, Property $property): Response {
        $image = $request->file("picture");

        if (!$image->isValid()) {
            throw new UploadException($image->getErrorMessage(), $image->getError());
        }

        [$width, $height] = getimagesize($image);

        // Image is valid, create a resource for it and store it
        $picture = new PropertyPicture([
                                           "name"        => $image->getClientOriginalName(),
                                           "order"       => $property->pictures()->count(),
                                           "width"       => $width,
                                           "height"      => $height,
                                           "property_id" => $property->getKey(),
                                           "extension"   => $image->getExtension(),
                                       ]);

        $picture->save();

        Storage::disk("public")
               ->putFileAs("/properties/pictures", $image, "$picture->uid.$picture->extension", ["visibility" => "public"]);

        return new Response($picture, 201);
    }

    public function update(UpdatePictureRequest $request, Property $property, PropertyPicture $propertyPicture): Response {
        $order = $request->input("order", $propertyPicture->order);

        if ($order !== $propertyPicture) {
            // Reorder pictures
            /** @var PropertyPicture $p */
            foreach ($property->pictures as $p) {
                if ($p->is($propertyPicture)) {
                    continue;
                }

                if ($p->order >= $propertyPicture->order) {
                    $p->decrement('order');
                }

                if ($p->order >= $order) {
                    $p->increment('order');
                }

                $p->save();
            }
        }

        $propertyPicture->name  = $request->input("name", $propertyPicture->name);
        $propertyPicture->order = $request->input("order", $propertyPicture->order);

        $propertyPicture->save();

        return new Response($propertyPicture);
    }

    public function destroy(Property $property, PropertyPicture $propertyPicture): Response {
        $propertyPicture->delete();
        return new Response();
    }
}