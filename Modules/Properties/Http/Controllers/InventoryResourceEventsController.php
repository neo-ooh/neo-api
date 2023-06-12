<?php
/*
 * Copyright 2023 (c) Neo-OOH - All Rights Reserved
 * Unauthorized copying of this file, via any medium is strictly prohibited
 * Proprietary and confidential
 * Written by Valentin Dufois <vdufois@neo-ooh.com>
 *
 * @neo/api - InventoryResourceEventsController.php
 */

namespace Neo\Modules\Properties\Http\Controllers;

use Carbon\Carbon;
use Illuminate\Database\Eloquent\Builder;
use Illuminate\Http\Response;
use Illuminate\Support\Facades\Auth;
use Neo\Http\Controllers\Controller;
use Neo\Modules\Properties\Http\Requests\InventoryResourceEvents\ListEventsRequest;
use Neo\Modules\Properties\Http\Requests\InventoryResourceEvents\UpdateEventRequest;
use Neo\Modules\Properties\Models\InventoryResource;
use Neo\Modules\Properties\Models\InventoryResourceEvent;
use Neo\Modules\Properties\Services\Resources\Enums\InventoryResourceType;

class InventoryResourceEventsController extends Controller {
    public function index(ListEventsRequest $request) {
        $query = InventoryResourceEvent::query()
                                       ->whereHas("resource", function (Builder $query) {
                                           $query->where("type", "=", InventoryResourceType::Product);
                                       });

        if ($request->has("inventory_id")) {
            $query->where("inventory_id", "=", $request->input("inventory_id"));
        }

        if ($request->input("only_failed", false)) {
            $query->where("is_success", "=", false);
        }

        $totalCount = $query->clone()->count();

        $page = $request->input("page", 1);
        clock($page, $request->input("page"));
        $count = $request->input("count", 500);
        $from  = ($page - 1) * $count;
        $to    = ($page * $count) - 1;

        $query->limit($count)
              ->offset($from);

        $query->orderBy("triggered_at", 'desc');

        return new Response($query->get()->loadPublicRelations(), 200, [
            "Content-Range" => "items $from-$to/$totalCount",
        ]);
    }

    public function update(UpdateEventRequest $request, InventoryResource $resource, InventoryResourceEvent $inventoryResourceEvent) {
        if ($request->input("is_reviewed", false) && !$inventoryResourceEvent->reviewed_at) {
            $inventoryResourceEvent->reviewed_at = Carbon::now();
            $inventoryResourceEvent->reviewed_by = Auth::id();
            $inventoryResourceEvent->save();
        }

        return new Response($inventoryResourceEvent->loadPublicRelations());
    }
}
