<?php

namespace Neo\Http\Controllers;

use Illuminate\Http\Request;
use Illuminate\Http\Response;
use Illuminate\Support\Facades\Auth;
use Neo\Http\Requests\Headlines\CurrentHeadlinesRequest;
use Neo\Http\Requests\Headlines\DestroyHeadlineRequest;
use Neo\Http\Requests\Headlines\ListHeadlinesRequest;
use Neo\Http\Requests\Headlines\ShowHeadlineRequest;
use Neo\Http\Requests\Headlines\StoreHeadlineRequest;
use Neo\Http\Requests\Headlines\UpdateHeadlineRequest;
use Neo\Models\Headline;
use Neo\Models\HeadlineMessage;

class HeadlinesController extends Controller
{
    public function index(ListHeadlinesRequest $request) {
        return new Response(Headline::withTrashed()->with("messages")->get());
    }

    public function current(CurrentHeadlinesRequest $request) {
        return new Response(Headline::query()->orderBy("end_date", "desc")->where("end_date", "<", "NOW()")->with("messages")->get());
    }

    public function show(ShowHeadlineRequest $request, Headline $headline) {
        return new Response($headline);
    }

    public function store(StoreHeadlineRequest $request) {
        $inputs = $request->validated();
        $headline = new Headline();
        $headline->actor_id = Auth::id();
        $headline->style = $inputs["style"];
        $headline->end_date = $inputs["end_date"];
        $headline->save();

        $messages = $inputs["messages"];

        // Store messages
        foreach ($messages as $message) {
            HeadlineMessage::query()->create([
                "headline_id" => $headline->id,
                "locale" => $message["locale"],
                "message" => $message["message"],
            ]);
        }

        return new Response($headline->refresh()->load("messages"), 201);
    }

    public function update(UpdateHeadlineRequest $request, Headline $headline) {
        $inputs = $request->validated();
        $headline->style = $inputs["style"];
        $headline->end_date = $inputs["end_date"];
        $headline->save();

        return new Response($headline->refresh());
    }

    public function destroy(DestroyHeadlineRequest $request, Headline $headline) {
        $headline->delete();

        return new Response();
    }
}
