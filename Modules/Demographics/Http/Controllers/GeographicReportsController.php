<?php
/*
 * Copyright 2024 (c) Neo-OOH - All Rights Reserved
 * Unauthorized copying of this file, via any medium is strictly prohibited
 * Proprietary and confidential
 * Written by Valentin Dufois <vdufois@neo-ooh.com>
 *
 * @neo/api - GeographicReportsController.php
 */

namespace Neo\Modules\Demographics\Http\Controllers;

use Illuminate\Database\Eloquent\Builder;
use Illuminate\Http\Response;
use Illuminate\Http\UploadedFile;
use Illuminate\Support\Facades\Validator;
use JsonException;
use Neo\Http\Controllers\Controller;
use Neo\Modules\Demographics\Http\Requests\GeographicReports\DestroyReportRequest;
use Neo\Modules\Demographics\Http\Requests\GeographicReports\ListReportsRequest;
use Neo\Modules\Demographics\Http\Requests\GeographicReports\ShowReportRequest;
use Neo\Modules\Demographics\Http\Requests\GeographicReports\StoreReportRequest;
use Neo\Modules\Demographics\Models\Enums\GeographicReportTemplateAreaType;
use Neo\Modules\Demographics\Models\Enums\GeographicReportType;
use Neo\Modules\Demographics\Models\Enums\ReportStatus;
use Neo\Modules\Demographics\Models\GeographicReport;
use Neo\Modules\Demographics\Models\GeographicReportTemplate;

class GeographicReportsController extends Controller {
    public function index(ListReportsRequest $request) {
        $query = GeographicReport::query()
                                 ->when($request->has("template_id"), function (Builder $query) use ($request) {
                                     $query->where("template_id", "=", $request->input("template_id"));
                                 })
                                 ->when($request->has("property_id"), function (Builder $query) use ($request) {
                                     $query->where("property_id", "=", $request->input("property_id"));
                                 })
                                 ->orderBy("requested_at");

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
     * @throws JsonException
     */
    public function store(StoreReportRequest $request) {
        /** @var GeographicReportTemplate $template */
        $template = GeographicReportTemplate::query()->findOrFail($request->input("template_id"));

        // Create the report
        $report = GeographicReport::fromTemplate($template, $request->input("template_configuration_index"));
        $report->property_id = $request->input("property_id");
        $report->status = ReportStatus::Pending;

        // Depending on the report type, do additional actions
        if($template->type === GeographicReportType::Customers) {
            // Validate additional requirements for customer files
            $values = Validator::validate(
                $request->all(),
                [
                    "source_file" => ["required", "file"],
                    "source_file_format" => ["required", "string"],
                ]
            );

            // Store the provided customer file for later processing
            /** @var UploadedFile|null $file */
            $file = $values["source_file"];

            if(!$file || $file->getError() !== UPLOAD_ERR_OK) {
                return new Response(["error" => "Missing source file"]);
            }

            $extension = $file->getExtension();
            if($extension === "") {
                $extension = $file->getClientOriginalExtension();
            }

            $report->metadata->source_file = $file->getClientOriginalName();
            $report->metadata->source_file_format = $values["source_file_format"];
            $report->metadata->source_file_type = $extension;
            $report->save();

            $report->storeSourceFile($file);
        }

        if($template->type === GeographicReportType::Area && $report->metadata->area_type === GeographicReportTemplateAreaType::Custom) {
            // Validate additional requirements for custom areas
            $values = Validator::validate(
                $request->all(),
                [
                    "area" => ["required", "json"],
                ]
            );

            $report->metadata->area = json_decode($values["area"], true, 512, JSON_THROW_ON_ERROR);
            $report->save();
        }

        $report->save();

        return new Response($report->loadPublicRelations(), 201);
    }

    public function show(ShowReportRequest $request, GeographicReport $geographicReport) {
        return new Response($geographicReport->loadPublicRelations());
    }

    public function destroy(DestroyReportRequest $request, GeographicReport $geographicReport) {
        $geographicReport->delete();

        return new Response(["status" => "ok"]);
    }
}
