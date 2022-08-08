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
use Illuminate\Validation\ValidationException;
use Neo\Http\Requests\ImpressionsModels\DestroyImpressionsModelRequest;
use Neo\Http\Requests\ImpressionsModels\ListImpressionsModelsRequest;
use Neo\Http\Requests\ImpressionsModels\StoreImpressionsModelRequest;
use Neo\Http\Requests\ImpressionsModels\UpdateImpressionsModelRequest;
use Neo\Models\ImpressionsModel;
use Neo\Models\Interfaces\WithImpressionsModels;
use Neo\Models\Product;
use Neo\Models\ProductCategory;
use Symfony\Component\ExpressionLanguage\ExpressionLanguage;
use Symfony\Component\ExpressionLanguage\SyntaxError;

class ImpressionsModelsController {
    public function indexProductCategory(ListImpressionsModelsRequest $request, ProductCategory $productCategory) {
        return $this->index($request, $productCategory);
    }

    public function indexProduct(ListImpressionsModelsRequest $request, Product $product) {
        return $this->index($request, $product);
    }

    public function index(ListImpressionsModelsRequest $request, WithImpressionsModels $modelsHolder) {
        return new Response($modelsHolder->impressions_models()->get());
    }

    public function storeProductCategory(StoreImpressionsModelRequest $request, ProductCategory $productCategory) {
        return $this->store($request, $productCategory);
    }

    public function storeProduct(StoreImpressionsModelRequest $request, Product $product) {
        return $this->store($request, $product);
    }

    public function store(StoreImpressionsModelRequest $request, WithImpressionsModels $modelsHolder) {
        // Start by validating the formula
        $formula   = $request->input("formula");
        $variables = $request->input("variables", []);

        $this->validateFormula($formula, array_keys($variables));

        // Store the new OdooModel
        $model = $modelsHolder->impressions_models()->create([
            "start_month" => $request->input("start_month"),
            "end_month"   => $request->input("end_month"),
            "formula"     => $request->input("formula"),
            "variables"   => $request->input("variables"),
        ]);

        return new Response($model, 201);
    }

    public function updateProductCategory(UpdateImpressionsModelRequest $request, ProductCategory $productCategory, ImpressionsModel $impressionsModel) {
        return $this->update($request, $productCategory, $impressionsModel);
    }

    public function updateProduct(UpdateImpressionsModelRequest $request, Product $product, ImpressionsModel $impressionsModel) {
        return $this->update($request, $product, $impressionsModel);
    }

    public function update(UpdateImpressionsModelRequest $request, WithImpressionsModels $modelsHolder, ImpressionsModel $impressionsModel) {
        // Start by validating the formula
        $formula   = $request->input("formula");
        $variables = $request->input("variables", []);

        $this->validateFormula($formula, array_keys($variables));

        // Store the new OdooModel
        $impressionsModel->start_month = $request->input("start_month");
        $impressionsModel->end_month   = $request->input("end_month");
        $impressionsModel->formula     = $request->input("formula");
        $impressionsModel->variables   = $request->input("variables");
        $impressionsModel->save();

        return new Response($impressionsModel, 200);
    }

    protected function validateFormula(string $formula, array $variablesNames): void {
        // The formula linting throws an error if it fails
        try {
            $el = new ExpressionLanguage();
            $el->lint($formula, ["traffic", "faces", "spots", ...$variablesNames]);
        } catch (SyntaxError $error) {
            throw ValidationException::withMessages([
                "formula" => $error->getMessage()
            ]);
        }
    }

    public function destroyProductCategory(DestroyImpressionsModelRequest $request, ProductCategory $productCategory, ImpressionsModel $impressionsModel) {
        return $this->destroy($request, $productCategory, $impressionsModel);
    }

    public function destroyProduct(DestroyImpressionsModelRequest $request, Product $product, ImpressionsModel $impressionsModel) {
        return $this->destroy($request, $product, $impressionsModel);
    }

    public function destroy(DestroyImpressionsModelRequest $request, WithImpressionsModels $modelsHolder, ImpressionsModel $impressionsModel) {
        $impressionsModel->delete();

        return new Response(["status" => "OK"], 202);
    }
}
