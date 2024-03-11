<?php
/*
 * Copyright 2024 (c) Neo-OOH - All Rights Reserved
 * Unauthorized copying of this file, via any medium is strictly prohibited
 * Proprietary and confidential
 * Written by Valentin Dufois <vdufois@neo-ooh.com>
 *
 * @neo/api - IndexSetsController.php
 */

namespace Neo\Modules\Demographics\Http\Controllers;

use Illuminate\Http\Response;
use Neo\Exceptions\BaseException;
use Neo\Http\Controllers\Controller;
use Neo\Modules\Demographics\Http\Requests\IndexSets\DestroySetRequest;
use Neo\Modules\Demographics\Http\Requests\IndexSets\ListSetsRequest;
use Neo\Modules\Demographics\Http\Requests\IndexSets\ShowSetRequest;
use Neo\Modules\Demographics\Http\Requests\IndexSets\StoreSetRequest;
use Neo\Modules\Demographics\Models\Enums\ReportStatus;
use Neo\Modules\Demographics\Models\Extract;
use Neo\Modules\Demographics\Models\IndexSet;
use Neo\Modules\Demographics\Models\IndexSetTemplate;
use Neo\Modules\Demographics\Models\StructuredColumns\IndexSetMetadata;

class IndexSetsController extends Controller {
    public function index(ListSetsRequest $request) {
        $sets = IndexSet::query()->get();

        return new Response($sets->loadPublicRelations());
    }

    public function store(StoreSetRequest $request) {
        // Validate both specified extract are for the correct dataset and property
        /** @var Extract $primaryExtract */
        $primaryExtract   = Extract::query()->findOrFail($request->input("primary_extract_id"));
        /** @var Extract $referenceExtract */
        $referenceExtract = Extract::query()->findOrFail($request->input("reference_extract_id"));

        /** @var IndexSetTemplate $template */
        $template = IndexSetTemplate::query()->findOrFail($request->input("template_id"));

        if($template->primary_extract_template_id !== $primaryExtract->template_id
        || $template->reference_extract_template_id !== $referenceExtract->template_id) {
            throw new BaseException("Extracts templates must match the index set template configuration.", "datasets.extract-templates-mismatch");
        }

        if($primaryExtract->property_id !== $referenceExtract->property_id
        || $primaryExtract->property_id !== $request->input("property_id")) {
            throw new BaseException("Extracts must both be for the same property", "datasets.extract-property-id-mismatch");
        }

        $set = new IndexSet();
        $set->property_id = $request->input("property_id");
        $set->template_id = $template->getKey();
        $set->primary_extract_id = $primaryExtract->getKey();
        $set->reference_extract_id = $referenceExtract->getKey();
        $set->metadata = IndexSetMetadata::from([]);
        $set->status = ReportStatus::Pending;
        $set->save();

        return new Response($set->loadPublicRelations());
    }

    public function show(ShowSetRequest $request, IndexSet $indexSet) {
        return new Response($indexSet->loadPublicRelations());
    }

    public function destroy(DestroySetRequest $request, IndexSet $indexSet) {
        $indexSet->delete();

        return new Response(["status" => "ok"]);
    }
}
