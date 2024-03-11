<?php
/*
 * Copyright 2024 (c) Neo-OOH - All Rights Reserved
 * Unauthorized copying of this file, via any medium is strictly prohibited
 * Proprietary and confidential
 * Written by Valentin Dufois <vdufois@neo-ooh.com>
 *
 * @neo/api - GeographicReportsTemplatesController.php
 */

namespace Neo\Modules\Demographics\Http\Controllers;

use Illuminate\Http\Response;
use Neo\Http\Controllers\Controller;
use Neo\Modules\Demographics\Http\Requests\GeographicReportsTemplates\DestroyTemplateRequest;
use Neo\Modules\Demographics\Http\Requests\GeographicReportsTemplates\ListTemplatesRequest;
use Neo\Modules\Demographics\Http\Requests\GeographicReportsTemplates\ShowTemplateRequest;
use Neo\Modules\Demographics\Http\Requests\GeographicReportsTemplates\StoreTemplateRequest;
use Neo\Modules\Demographics\Http\Requests\GeographicReportsTemplates\UpdateTemplateRequest;
use Neo\Modules\Demographics\Models\Enums\GeographicReportType;
use Neo\Modules\Demographics\Models\GeographicReportTemplate;
use Neo\Modules\Demographics\Models\StructuredColumns\GeographicReportTemplateConfiguration;

class GeographicReportsTemplatesController extends Controller {
    public function index(ListTemplatesRequest $request): Response {
        $templates = GeographicReportTemplate::query()->get();

        return new Response($templates->loadPublicRelations());
    }

    public function store(StoreTemplateRequest $request): Response {
        $template                = new GeographicReportTemplate();
        $template->name          = $request->input("name");
        $template->description   = $request->input("description", "");
        $template->type          = GeographicReportType::from($request->input("type"));
        $template->configuration = GeographicReportTemplateConfiguration::collection($request->input("configuration"));
        $template->save();

        return new Response($template->loadPublicRelations(), 201);
    }

    public function show(ShowTemplateRequest $request, GeographicReportTemplate $geographicReportTemplate): Response {
        return new Response($geographicReportTemplate->loadPublicRelations());
    }

    public function update(UpdateTemplateRequest $request, GeographicReportTemplate $template): Response {
        $template->name          = $request->input("name");
        $template->description   = $request->input("description", "");
        $template->configuration = GeographicReportTemplateConfiguration::collection($request->input("configuration"));
        $template->save();

        return new Response($template->loadPublicRelations());
    }

    public function destroy(DestroyTemplateRequest $request, GeographicReportTemplate $template): Response {
        $template->delete();

        return new Response(["status" => "ok"]);
    }
}
