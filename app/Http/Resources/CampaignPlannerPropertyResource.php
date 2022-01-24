<?php

namespace Neo\Http\Resources;

use Illuminate\Http\Request;
use Illuminate\Http\Resources\Json\JsonResource;

/** @mixin \Neo\Models\Property */
class CampaignPlannerPropertyResource extends JsonResource {
    /**
     * @param Request $request
     * @return array
     */
    public function toArray($request) {
        return [
            "id"                      => $this->actor_id,
            "name"                    => $this->actor->name,
            "address"                 => $this->address,
            "network_id"              => $this->network_id,
            "products"                => $this->products,
            "products_ids"            => $this->products->pluck("id"),
            "products_categories_ids" => array_map(
                static fn($categoryId, $products) => $products->pluck('id'),
                $this->products->groupBy("category_id")->all()
            ),
            "traffic"                 => $this->traffic,
            "data"                    => $this->data,
            "pictures"                => $this->pictures,
            "fields_values"           => $this->fields_values,
            "has_tenants"             => $this->has_tenants,
            "tenants"                 => $this->tenants->pluck('id'),
        ];
    }
}
