<?php /*
 * Copyright 2020 (c) Neo-OOH - All Rights Reserved
 * Unauthorized copying of this file, via any medium is strictly prohibited
 * Proprietary and confidential
 * Written by Valentin Dufois <Valentin Dufois>
 *
 * @neo/api - ReviewsTemplatesController.php
 */

/** @noinspection PhpUnusedParameterInspection */

namespace Neo\Http\Controllers;

use Illuminate\Contracts\Routing\ResponseFactory;
use Illuminate\Database\Query\Builder;
use Illuminate\Http\Response;
use Illuminate\Support\Facades\Auth;
use Neo\Http\Requests\ReviewsTemplates\DestroyReviewTemplateRequest;
use Neo\Http\Requests\ReviewsTemplates\ListReviewTemplatesRequest;
use Neo\Http\Requests\ReviewsTemplates\StoreReviewTemplateRequest;
use Neo\Http\Requests\ReviewsTemplates\UpdateReviewTemplateRequest;
use Neo\Models\ReviewTemplate;

class ReviewsTemplatesController extends Controller
{
    /**
     * @param ListReviewTemplatesRequest $request
     *
     * @return ResponseFactory|Response
     */
    public function index(ListReviewTemplatesRequest $request)
    {
        $actor = Auth::user();
        return new Response(ReviewTemplate::query()
                                          ->where("owner_id", "=", $actor->id)
                                          ->when($actor->parent_is_group,
                                              fn(Builder $query) => $query->orWhere("owner_id", "=", $actor->parent_id))
                                          ->get());
    }

    /**
     * @param StoreReviewTemplateRequest $request
     *
     * @return ResponseFactory|Response
     */
    public function store(StoreReviewTemplateRequest $request)
    {
        $template = new ReviewTemplate([
            "text" => $request->validated()["text"],
            "owner_id" => $request->validated()["owner_id"],
        ]);
        $template->save();

        $template->loadMissing('owner');
        return new Response($template->refresh(), 201);
    }

    /**
     * @param UpdateReviewTemplateRequest $request
     * @param ReviewTemplate $template
     *
     * @return ResponseFactory|Response
     */
    public function update(UpdateReviewTemplateRequest $request, ReviewTemplate $template)
    {
        $template->text = $request->validated()["text"];
        $template->owner_id = $request->validated()["owner_id"];
        $template->save();

        return new Response($template);
    }

    public function destroy(DestroyReviewTemplateRequest $request, ReviewTemplate $template): void
    {
        $template->delete();
    }
}
