<?php
/*
 * Copyright 2020 (c) Neo-OOH - All Rights Reserved
 * Unauthorized copying of this file, via any medium is strictly prohibited
 * Proprietary and confidential
 * Written by Valentin Dufois <vdufois@neo-ooh.com>
 *
 * @neo/api - HeadlinesController.php
 */

namespace Neo\Http\Controllers;

use Illuminate\Http\Response;
use Illuminate\Support\Facades\Auth;
use Illuminate\Support\Facades\Date;
use Neo\Http\Requests\Headlines\CurrentHeadlinesRequest;
use Neo\Http\Requests\Headlines\DestroyHeadlineRequest;
use Neo\Http\Requests\Headlines\ListHeadlinesRequest;
use Neo\Http\Requests\Headlines\ShowHeadlineRequest;
use Neo\Http\Requests\Headlines\StoreHeadlineRequest;
use Neo\Http\Requests\Headlines\UpdateHeadlineMessageRequest;
use Neo\Http\Requests\Headlines\UpdateHeadlineRequest;
use Neo\Models\Headline;
use Neo\Models\HeadlineMessage;

class HeadlinesController extends Controller {
    public function index(ListHeadlinesRequest $request) {
        return new Response(Headline::withTrashed()->orderBy("end_date", "desc")->with("messages")->get());
    }

    public function current(CurrentHeadlinesRequest $request) {
        $headlines = Headline::query()
                             ->orderBy("end_date", "desc")
                             ->whereDate("end_date", ">", Date::now())
                             ->with(["messages", "capabilities"])
                             ->get();


        $userCapabilities = Auth::user()->capabilities->pluck("id");

        $headlines = $headlines->filter(function($headline) use ($userCapabilities) {
             if($headline->capabilities->count() === 0) {
                 return true;
             }

             return $userCapabilities->diff($headline->capabilities->pluck("id"))
                                     ->count() !== $userCapabilities->count();
        });

        return new Response($headlines);
    }

    public function show(ShowHeadlineRequest $request, Headline $headline) {
        return new Response($headline->load(["messages", "capabilities"]));
    }

    public function store(StoreHeadlineRequest $request) {
        $inputs             = $request->validated();
        $headline           = new Headline();
        $headline->actor_id = Auth::id();
        $headline->style    = $inputs["style"];
        $headline->end_date = $inputs["end_date"];
        $headline->save();

        $headline->capabilities()->sync($request->input("capabilities", []));

        $messages = $inputs["messages"];

        // Store messages
        foreach ($messages as $message) {
            HeadlineMessage::query()->create([
                "headline_id" => $headline->id,
                "locale"      => $message["locale"],
                "message"     => $message["message"],
            ]);
        }

        return new Response($headline->refresh()->load("messages"), 201);
    }

    public function update(UpdateHeadlineRequest $request, Headline $headline) {
        $inputs             = $request->validated();
        $headline->style    = $inputs["style"];
        $headline->end_date = $inputs["end_date"];
        $headline->save();

        return new Response($headline->load("messages")->refresh());
    }

    public function updateMessage(UpdateHeadlineMessageRequest $request, Headline $headline, HeadlineMessage $headlineMessage) {
        $headlineMessage->message = $request->validated()["message"];
        $headlineMessage->save();

        return new Response($headlineMessage);
    }

    public function destroy(DestroyHeadlineRequest $request, Headline $headline) {
        $headline->delete();

        return new Response();
    }
}
