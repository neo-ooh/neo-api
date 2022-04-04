<?php
/*
 * Copyright 2020 (c) Neo-OOH - All Rights Reserved
 * Unauthorized copying of this file, via any medium is strictly prohibited
 * Proprietary and confidential
 * Written by Valentin Dufois <vdufois@neo-ooh.com>
 *
 * @neo/api - DemographicValuesController.php
 */

namespace Neo\Http\Controllers;

use Illuminate\Http\Response;
use Illuminate\Http\UploadedFile;
use Neo\Http\Requests\DemograhicValues\ListPipelinesRequest;
use Neo\Http\Requests\DemograhicValues\StoreDemographicValuesRequest;
use Neo\Jobs\Demographics\IngestDemographicFileJob;
use Neo\Jobs\Properties\UpdateDemographicFieldsJob;
use Neo\Models\Property;

class DemographicValuesController {
    public function listPipelines(ListPipelinesRequest $request) {
        return new Response([
            [
                "slug"    => "environics-default",
                "name_en" => "Environics - Default",
                "name_fr" => "Environics - Défaut",
            ]
        ]);
    }

    public function store(StoreDemographicValuesRequest $request, Property $property) {
        $files = array_map(null, array_values($request->file("files")), $request->input("formats"));

        /**
         * @var UploadedFile $file
         * @var string       $format
         */
        foreach ($files as [$file, $format]) {
            $tempName = tempnam(sys_get_temp_dir(), "connect_prop_demo_");
            file_put_contents($tempName, $file->getContent());
            IngestDemographicFileJob::dispatchSync($property->getKey(), $tempName, $format);
        }

        UpdateDemographicFieldsJob::dispatchSync($property->getKey(), null);
    }
}
