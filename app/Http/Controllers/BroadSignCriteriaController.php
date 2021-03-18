<?php

namespace Neo\Http\Controllers;

use Illuminate\Http\Request;
use Illuminate\Http\Response;
use Neo\Http\Requests\BroadSignCriteria\ListBroadSignCriteriaRequest;
use Neo\Http\Requests\BroadSignCriteria\ShowBroadSignCriteriaRequest;
use Neo\Http\Requests\BroadSignCriteria\StoreBroadSignCriteriaRequest;
use Neo\Http\Requests\BroadSignCriteria\UpdateBroadSignCriteriaRequest;
use Neo\Models\BroadSignCriteria;

class BroadSignCriteriaController extends Controller
{
    public function index(ListBroadSignCriteriaRequest $request) {
        return new Response(BroadSignCriteria::query()->orderBy("name")->get()->values());
    }

    public function show(ShowBroadSignCriteriaRequest $request, BroadSignCriteria $criteria) {
        return new Response($criteria);
    }

    public function store(StoreBroadSignCriteriaRequest $request) {
        $criteria = new BroadSignCriteria();
        [
            "name" => $criteria->name,
            "criteria_id" => $criteria->broadsign_criteria_id,
        ] = $request->validated();
        $criteria->save();

        return new Response($criteria, 201);
    }

    public function update(UpdateBroadSignCriteriaRequest $request, BroadSignCriteria $criteria) {
        [
            "name" => $criteria->name,
            "criteria_id" => $criteria->broadsign_criteria_id,
        ] = $request->validated();
        $criteria->save();

        return new Response($criteria, 200);
    }

    public function destroy(BroadSignCriteria $criteria) {
        // Todo: Wait for criteria implementation in Campaigns for this method
    }
}
