<?php
/*
 * Copyright 2020 (c) Neo-OOH - All Rights Reserved
 * Unauthorized copying of this file, via any medium is strictly prohibited
 * Proprietary and confidential
 * Written by Valentin Dufois <vdufois@neo-ooh.com>
 *
 * @neo/api - FieldsController.php
 */

namespace Neo\Http\Controllers;

use Illuminate\Http\Response;
use Neo\Http\Requests\Fields\DestroyFieldRequest;
use Neo\Http\Requests\Fields\ListFieldsRequest;
use Neo\Http\Requests\Fields\StoreFieldRequest;
use Neo\Http\Requests\Fields\UpdateFieldRequest;
use Neo\Models\Field;

class FieldsController {
    public function index(ListFieldsRequest $request): Response {
        return new Response(Field::all());
    }

    public function show(ListFieldsRequest $request, Field $field): Response {
        return new Response($field->load("category"));
    }

    public function store(StoreFieldRequest $request): Response {
        $field = new Field([
            "category_id" => $request->input("category_id"),
            "name_en"     => $request->input("name_en"),
            "name_fr"     => $request->input("name_fr"),
            "type"        => $request->input("type"),
            "unit"        => $request->input("unit"),
            "is_filter"   => $request->input("is_filter"),
        ]);
        $field->save();

        // And add a first, default segment
        $field->segments()->create([
            "name_en" => "Default",
            "name_fr" => "Default",
            "order"   => 0
        ]);

        return new Response($field->load("segments"), 201);
    }

    public function update(UpdateFieldRequest $request, Field $field): Response {
        $field->category_id = $request->input("category_id");
        $field->name_en     = $request->input("name_en");
        $field->name_fr     = $request->input("name_fr");
        $field->type        = $request->input("type");
        $field->unit        = $request->input("unit");
        $field->is_filter   = $request->input("is_filter");
        $field->save();

        return new Response($field);
    }

    public function destroy(DestroyFieldRequest $request, Field $field): Response {
        $field->delete();

        return new Response();
    }
}
