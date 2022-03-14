<?php
/*
 * Copyright 2020 (c) Neo-OOH - All Rights Reserved
 * Unauthorized copying of this file, via any medium is strictly prohibited
 * Proprietary and confidential
 * Written by Valentin Dufois <vdufois@neo-ooh.com>
 *
 * @neo/api - FieldsCategoriesController.php
 */

namespace Neo\Http\Controllers;

use Illuminate\Http\Response;
use Illuminate\Support\Facades\Lang;
use Neo\Http\Requests\FieldsCategories\DestroyCategoryRequest;
use Neo\Http\Requests\FieldsCategories\ListCategoriesByIdRequest;
use Neo\Http\Requests\FieldsCategories\ListCategoriesRequest;
use Neo\Http\Requests\FieldsCategories\StoreCategoryRequest;
use Neo\Http\Requests\FieldsCategories\UpdateCategoryRequest;
use Neo\Models\Field;
use Neo\Models\FieldsCategory;

class FieldsCategoriesController {
    public function index(ListCategoriesRequest $request) {
        $sortKey = "name_" . Lang::locale();
        return new Response(FieldsCategory::query()->orderBy($sortKey)->get());
    }

    public function byId(ListCategoriesByIdRequest $request) {
        $sortKey    = "name_" . Lang::locale();
        $categories = FieldsCategory::query()->whereIn("id", $request->input("ids", []))->orderBy($sortKey)->get();
        return new Response($categories);
    }

    public function store(StoreCategoryRequest $request) {
        $fieldsCategory = new FieldsCategory([
            "name_en" => $request->input("name_en"),
            "name_fr" => $request->input("name_fr"),
        ]);

        $fieldsCategory->save();
        return new Response($fieldsCategory, 201);
    }

    public function update(UpdateCategoryRequest $request, FieldsCategory $fieldsCategory) {
        $fieldsCategory->name_en = $request->input("name_en");
        $fieldsCategory->name_fr = $request->input("name_fr");
        $fieldsCategory->save();

        return new Response($fieldsCategory);
    }

    public function destroy(DestroyCategoryRequest $request, FieldsCategory $fieldsCategory) {
        $fieldsCategory->fields->each(function (Field $field) {
            $field->category_id = null;
            $field->save();
        });

        $fieldsCategory->delete();

        return new Response();
    }
}
