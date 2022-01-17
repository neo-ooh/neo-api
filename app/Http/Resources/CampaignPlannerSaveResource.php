<?php

namespace Neo\Http\Resources;

use Illuminate\Http\Request;
use Illuminate\Http\Resources\Json\JsonResource;

/** @mixin \Neo\Models\CampaignPlannerSave */
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
