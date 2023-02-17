<?php
/*
 * Copyright 2023 (c) Neo-OOH - All Rights Reserved
 * Unauthorized copying of this file, via any medium is strictly prohibited
 * Proprietary and confidential
 * Written by Valentin Dufois <vdufois@neo-ooh.com>
 *
 * @neo/api - PropertiesTranslationsController.php
 */

namespace Neo\Modules\Properties\Http\Controllers;

use Illuminate\Http\Response;
use Neo\Http\Controllers\Controller;
use Neo\Modules\Properties\Http\Requests\PropertiesTranslations\UpdatePropertyTranslationRequest;
use Neo\Modules\Properties\Models\Property;

class PropertiesTranslationsController extends Controller {
    public function update(UpdatePropertyTranslationRequest $request, Property $property, string $locale) {
        $property->translations()->where("locale", "=", $locale)
                 ->update([
                              "description" => $request->input("description", ""),
                          ]);

        return new Response($property->translations()->where("locale", "=", $locale)->first());
    }
}
