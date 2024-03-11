<?php
/*
 * Copyright 2024 (c) Neo-OOH - All Rights Reserved
 * Unauthorized copying of this file, via any medium is strictly prohibited
 * Proprietary and confidential
 * Written by Valentin Dufois <vdufois@neo-ooh.com>
 *
 * @neo/api - ExtractsTemplatesController.php
 */

namespace Neo\Modules\Demographics\Http\Controllers;

use Illuminate\Http\Response;
use Neo\Http\Controllers\Controller;
use Neo\Modules\Demographics\Http\Requests\ExtractsTemplates\DestroyTemplateRequest;
use Neo\Modules\Demographics\Http\Requests\ExtractsTemplates\ListTemplatesRequest;
use Neo\Modules\Demographics\Http\Requests\ExtractsTemplates\ShowTemplateRequest;
use Neo\Modules\Demographics\Http\Requests\ExtractsTemplates\StoreTemplateRequest;
use Neo\Modules\Demographics\Http\Requests\ExtractsTemplates\UpdateTemplateRequest;
use Neo\Modules\Demographics\Models\ExtractTemplate;

class ExtractsTemplatesController extends Controller {
    public function index(ListTemplatesRequest $request): Response {
        $templates = ExtractTemplate::query()->get();

        return new Response($templates->loadPublicRelations());
    }

    public function store(StoreTemplateRequest $request): Response {
        $template = new ExtractTemplate();
        $template->name = $request->input("name");
        $template->description = $request->input("description", "");
        $template->dataset_version_id = $request->input("dataset_version_id");
        $template->geographic_report_template_id = $request->input("geographic_report_template_id");
        $template->save();

        return new Response($template->loadPublicRelations());
    }

    public function show(ShowTemplateRequest $request, ExtractTemplate $extractTemplate): Response {
        return new Response($extractTemplate->loadPublicRelations());
    }

    public function update(UpdateTemplateRequest $request, ExtractTemplate $extractTemplate): Response {
        $extractTemplate->name = $request->input("name");
        $extractTemplate->description = $request->input("description");
        $extractTemplate->save();

        return new Response($extractTemplate);
    }

    public function destroy(DestroyTemplateRequest $request, ExtractTemplate $extractTemplate): Response {
        $extractTemplate->delete();

        return new Response(["status" => "ok"]);
    }
}
