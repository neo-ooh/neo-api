<?php
/*
 * Copyright 2023 (c) Neo-OOH - All Rights Reserved
 * Unauthorized copying of this file, via any medium is strictly prohibited
 * Proprietary and confidential
 * Written by Valentin Dufois <vdufois@neo-ooh.com>
 *
 * @neo/api - FieldsCategoriesController.php
 */

namespace Neo\Modules\Properties\Http\Controllers;

use Illuminate\Http\Response;
use Illuminate\Support\Facades\Lang;
use Neo\Modules\Properties\Http\Requests\FieldsCategories\DestroyCategoryRequest;
use Neo\Modules\Properties\Http\Requests\FieldsCategories\ListCategoriesByIdRequest;
use Neo\Modules\Properties\Http\Requests\FieldsCategories\ListCategoriesRequest;
use Neo\Modules\Properties\Http\Requests\FieldsCategories\ReorderFieldsRequest;
use Neo\Modules\Properties\Http\Requests\FieldsCategories\StoreCategoryRequest;
use Neo\Modules\Properties\Http\Requests\FieldsCategories\UpdateCategoryRequest;
use Neo\Modules\Properties\Models\Field;
use Neo\Modules\Properties\Models\FieldsCategory;

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

    public function reorder(ReorderFieldsRequest $request, FieldsCategory $fieldsCategory) {
        $fieldsIds = $request->input("fields", []);
        /** @var Field $field */
        foreach ($fieldsCategory->fields as $field) {
            $field->order = array_search($field->id, $fieldsIds, true);
            $field->save();
        }

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
