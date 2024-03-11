<?php
/*
 * Copyright 2024 (c) Neo-OOH - All Rights Reserved
 * Unauthorized copying of this file, via any medium is strictly prohibited
 * Proprietary and confidential
 * Written by Valentin Dufois <vdufois@neo-ooh.com>
 *
 * @neo/api - IndexSetsTemplatesController.php
 */

namespace Neo\Modules\Demographics\Http\Controllers;

use Illuminate\Http\Response;
use Neo\Exceptions\BaseException;
use Neo\Http\Controllers\Controller;
use Neo\Modules\Demographics\Http\Requests\IndexSetsTemplates\DestroyTemplateRequest;
use Neo\Modules\Demographics\Http\Requests\IndexSetsTemplates\ListTemplatesRequest;
use Neo\Modules\Demographics\Http\Requests\IndexSetsTemplates\ShowTemplateRequest;
use Neo\Modules\Demographics\Http\Requests\IndexSetsTemplates\StoreTemplateRequest;
use Neo\Modules\Demographics\Http\Requests\IndexSetsTemplates\UpdateTemplateRequest;
use Neo\Modules\Demographics\Models\ExtractTemplate;
use Neo\Modules\Demographics\Models\IndexSetTemplate;

class IndexSetsTemplatesController extends Controller {
    public function index(ListTemplatesRequest $request): Response {
        $templates = IndexSetTemplate::query()->get();

        return new Response($templates->loadPublicRelations());
    }

    public function store(StoreTemplateRequest $request): Response {
        // Validate both templates are linked to the same dataset version
        $datasetVersionId = $request->input("dataset_version_id");
        $primaryTemplate = ExtractTemplate::query()->findOrFail($request->input("primary_extract_template_id"));
        $referenceTemplate = ExtractTemplate::query()->findOrFail($request->input("reference_extract_template_id"));


        if($datasetVersionId !== $primaryTemplate->dataset_version_id || $datasetVersionId !== $referenceTemplate->dataset_version_id) {
            throw new BaseException("Primary and reference templates must use the same dataset version as the one specified for the index set.", "datasets.template-dataset-mismatch");
        }

        $template = new IndexSetTemplate();
        $template->name = $request->input("name");
        $template->description = $request->input("description", "");
        $template->dataset_version_id = $datasetVersionId;
        $template->primary_extract_template_id = $primaryTemplate->getKey();
        $template->reference_extract_template_id = $referenceTemplate->getKey();
        $template->save();

        return new Response($template, 201);
    }

    public function show(ShowTemplateRequest $request, IndexSetTemplate $indexSetTemplate): Response {
        return new Response($indexSetTemplate->loadPublicRelations());
    }

    public function update(UpdateTemplateRequest $request, IndexSetTemplate $indexSetTemplate): Response {
        $indexSetTemplate->name = $request->input("name");
        $indexSetTemplate->description = $request->input("description", "");
        $indexSetTemplate->save();

        return new Response($indexSetTemplate->loadPublicRelations());
    }

    public function destroy(DestroyTemplateRequest $request, IndexSetTemplate $indexSetTemplate): Response {
        $indexSetTemplate->delete();

        return new Response(["status" => "ok"]);
    }
}
