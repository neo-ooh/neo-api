<?php
/*
 * Copyright 2020 (c) Neo-OOH - All Rights Reserved
 * Unauthorized copying of this file, via any medium is strictly prohibited
 * Proprietary and confidential
 * Written by Valentin Dufois <vdufois@neo-ooh.com>
 *
 * @neo/api - BroadcastTagsExternalRepresentationsController.php
 */

namespace Neo\Modules\Broadcast\Http\Controllers;

use Illuminate\Http\Response;
use Neo\Http\Controllers\Controller;
use Neo\Modules\Broadcast\Enums\ExternalResourceType;
use Neo\Modules\Broadcast\Http\Requests\BroadcastTags\ListExternalRepresentationsRequest;
use Neo\Modules\Broadcast\Http\Requests\BroadcastTags\UpdateExternalRepresentationsRequest;
use Neo\Modules\Broadcast\Models\BroadcastTag;
use Neo\Modules\Broadcast\Models\StructuredColumns\ExternalResourceData;

class BroadcastTagsExternalRepresentationsController extends Controller {
    public function index(ListExternalRepresentationsRequest $request, BroadcastTag $broadcastTag): Response {
        return new Response($broadcastTag->external_representations);
    }

    /**
     * @param UpdateExternalRepresentationsRequest $request
     * @param BroadcastTag                         $broadcastTag
     * @return Response
     */
    public function update(UpdateExternalRepresentationsRequest $request, BroadcastTag $broadcastTag): Response {
        $broadcasterIds = [];

        foreach ($request->input("representations", []) as ["broadcaster_id" => $broadcasterId, "external_id" => $externalId]) {
            $broadcastTag->external_representations()->updateOrCreate([
                                                                          "broadcaster_id" => $broadcasterId,
                                                                          "type"           => ExternalResourceType::Tag,
                                                                      ], [
                                                                          "data" => new ExternalResourceData(
                                                                              external_id: $externalId,
                                                                              network_id : null,
                                                                              formats_id : null,
                                                                          ),
                                                                      ]);

            $broadcasterIds[] = $broadcasterId;
        }

        $broadcastTag->external_representations()->whereNotIn("broadcaster_id", $broadcasterIds)->delete();

        return new Response($broadcastTag->external_representations()->get());
    }
}
