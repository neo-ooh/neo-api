<?php
/*
 * Copyright 2024 (c) Neo-OOH - All Rights Reserved
 * Unauthorized copying of this file, via any medium is strictly prohibited
 * Proprietary and confidential
 * Written by Valentin Dufois <vdufois@neo-ooh.com>
 *
 * @neo/api - ExtractsController.php
 */

namespace Neo\Modules\Demographics\Http\Controllers;

use Illuminate\Database\Eloquent\Builder;
use Illuminate\Http\Response;
use Neo\Exceptions\BaseException;
use Neo\Http\Controllers\Controller;
use Neo\Modules\Demographics\Http\Requests\Extracts\DestroyExtractRequest;
use Neo\Modules\Demographics\Http\Requests\Extracts\ListExtractsRequest;
use Neo\Modules\Demographics\Http\Requests\Extracts\ShowExtractRequest;
use Neo\Modules\Demographics\Http\Requests\Extracts\StoreExtractRequest;
use Neo\Modules\Demographics\Models\Enums\ReportStatus;
use Neo\Modules\Demographics\Models\Extract;
use Neo\Modules\Demographics\Models\GeographicReport;
use Neo\Modules\Demographics\Models\StructuredColumns\ExtractMetadata;

class ExtractsController extends Controller {
    public function index(ListExtractsRequest $request): Response {
        $query = Extract::query()
                        ->when($request->has("template_id"), function (Builder $query) use ($request) {
                            $query->where("template_id", "=", $request->input("template_id"));
                        })
                        ->when($request->has("property_id"), function (Builder $query) use ($request) {
                            $query->where("property_id", "=", $request->input("property_id"));
                        });

        $totalCount = $query->clone()->count();

        $page  = $request->input("page", 1);
        $count = $request->input("count", 500);
        $from  = ($page - 1) * $count;
        $to    = ($page * $count) - 1;

        $query->limit($count)
              ->offset($from);

        return new Response($query->get()->loadPublicRelations(), 200, [
            "Content-Range" => "items $from-$to/$totalCount",
        ]);
    }

    /**
     * @throws BaseException
     */
    public function store(StoreExtractRequest $request): Response {
        // Validate the given geographic_report_id is for the same property as the one given
        $geographicReport = GeographicReport::query()->find($request->input("geographic_report_id"));
        if ($geographicReport->property_id !== (int)$request->input("property_id")) {
            throw new BaseException("The specified geographic_report_id does not match any geographic report of the specified property.", "demographic.incompatible-geographic-report-and-property");
        }

        $extract                       = new Extract();
        $extract->property_id          = $request->input("property_id");
        $extract->template_id          = $request->input("template_id");
        $extract->geographic_report_id = $geographicReport->getKey();
        $extract->metadata             = ExtractMetadata::from([]);
        $extract->status               = ReportStatus::Pending;

        $extract->save();

        return new Response($extract->loadPublicRelations());
    }

    public function show(ShowExtractRequest $request, Extract $extract): Response {
        return new Response($extract->loadPublicRelations());
    }

    public function destroy(DestroyExtractRequest $request, Extract $extract): Response {
        $extract->delete();

        return new Response(["status" => "ok"]);
    }
}
