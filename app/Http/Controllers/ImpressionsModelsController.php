<?php
/*
 * Copyright 2020 (c) Neo-OOH - All Rights Reserved
 * Unauthorized copying of this file, via any medium is strictly prohibited
 * Proprietary and confidential
 * Written by Valentin Dufois <vdufois@neo-ooh.com>
 *
 * @neo/api - ImpressionsModelsController.php
 */

namespace Neo\Http\Controllers;

use Illuminate\Http\Response;
use Neo\Http\Requests\ImpressionsModels\DestroyImpressionsModelRequest;
use Neo\Http\Requests\ImpressionsModels\ListImpressionsModelsRequest;
use Neo\Http\Requests\ImpressionsModels\StoreImpressionsModelRequest;
use Neo\Http\Requests\ImpressionsModels\UpdateImpressionsModelRequest;
use Neo\Models\ImpressionsModel;
use Neo\Models\Interfaces\WithImpressionsModels;
use Symfony\Component\ExpressionLanguage\ExpressionLanguage;

class ImpressionsModelsController {
    public function index(ListImpressionsModelsRequest $request, WithImpressionsModels $modelsHolder) {
        return new Response($modelsHolder->impressions_models()->get());
    }

    public function store(StoreImpressionsModelRequest $request, WithImpressionsModels $modelsHolder) {
        // Start by validating the formula
        $formula = $request->input("formula");

        // The formula linting throws an error if it fails
        $el = new ExpressionLanguage();
        $el->lint($formula, null);

        // Store the new Model
        $model = $modelsHolder->impressions_models()->create([
            "start_month" => $request->input("start"),
            "end_month"   => $request->input("end"),
            "formula"     => $request->input("formula"),
            "variables"   => $request->input("variables"),
        ]);

        return new Response($model, 201);
    }

    public function update(UpdateImpressionsModelRequest $request, WithImpressionsModels $modelsHolder, ImpressionsModel $impressionsModel) {
        // Start by validating the formula
        $formula = $request->input("formula");

        // The formula linting throws an error if it fails
        $el = new ExpressionLanguage();
        $el->lint($formula, null);

        // Store the new Model
        $impressionsModel->start_month = $request->input("start");
        $impressionsModel->end_month   = $request->input("end");
        $impressionsModel->formula     = $request->input("formula");
        $impressionsModel->variables   = $request->input("variables");
        $impressionsModel->save();

        return new Response($impressionsModel, 200);
    }

    public function destroy(DestroyImpressionsModelRequest $request, WithImpressionsModels $modelsHolder, ImpressionsModel $impressionsModel) {
        $impressionsModel->delete();

        return new Response(["status" => "OK"], 202);
    }
}
