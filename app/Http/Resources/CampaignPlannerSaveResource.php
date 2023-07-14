<?php
/*
 * Copyright 2020 (c) Neo-OOH - All Rights Reserved
 * Unauthorized copying of this file, via any medium is strictly prohibited
 * Proprietary and confidential
 * Written by Valentin Dufois <vdufois@neo-ooh.com>
 *
 * @neo/api - CampaignPlannerSaveResource.php
 */

namespace Neo\Http\Resources;

use Illuminate\Http\Request;
use Illuminate\Http\Resources\Json\JsonResource;
use Neo\Models\CampaignPlannerSave;

/** @mixin CampaignPlannerSave */
class CampaignPlannerSaveResource extends JsonResource {
    /**
     * @param Request $request
     * @return array
     */
    public function toArray($request) {
        if (!array_key_exists("_meta", $this->data)) {
            // Legacy save format
            return [
                'id'         => $this->id,
                'uid'        => $this->uid,
                'name'       => $this->name,
                'actor_id'   => $this->actor_id,
                'data'       => $this->data,
                'created_at' => $this->created_at,
                'updated_at' => $this->updated_at,
            ];
        }

        return [
            "plan"  => $this->data["plan"],
            "_meta" => array_merge(
                $this->data["_meta"],
                [
                    "id"       => $this->id,
                    "uid"      => $this->uid,
                    "actor_id" => $this->actor_id,
                ],
            ),
        ];
    }
}
