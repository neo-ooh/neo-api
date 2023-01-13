<?php
/*
 * Copyright 2020 (c) Neo-OOH - All Rights Reserved
 * Unauthorized copying of this file, via any medium is strictly prohibited
 * Proprietary and confidential
 * Written by Valentin Dufois <vdufois@neo-ooh.com>
 *
 * @neo/api - ImpressionsController.php
 */

namespace Neo\Http\Controllers;

use Illuminate\Http\Response;
use Neo\Documents\BroadSignAudienceFile\BroadSignAudienceFile;
use Neo\Documents\Exceptions\UnknownGenerationException;
use Neo\Exceptions\LocationNotAssociatedWithProductException;
use Neo\Http\Requests\Impressions\ExportBroadsignImpressionsRequest;
use Neo\Modules\Broadcast\Models\Location;

class ImpressionsController {
    /**
     * @param ExportBroadsignImpressionsRequest $request
     * @param int                               $displayUnitId
     * @return Response|void
     * @throws UnknownGenerationException
     */
    public function broadsignDisplayUnit(ExportBroadsignImpressionsRequest $request, int $displayUnitId) {
        /** @var Location|null $location */
        $location = Location::query()->where("external_id", "=", $displayUnitId)->first();

        if (!$location) {
            return new Response([
                                    "error" => true,
                                                                                 "type" => "unknown-value",
                                                                                 "message" => "The provided Display Unit Id is not registered on Connect.",
                                ], 400);
        }

        try {
            $audienceFile = BroadSignAudienceFile::make($location);
            $audienceFile->build();
            $audienceFile->output();
        } catch (LocationNotAssociatedWithProductException $e) {
            return new Response(400);
        }
    }
}
