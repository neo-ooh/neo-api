<?php /*
 * Copyright 2020 (c) Neo-OOH - All Rights Reserved
 * Unauthorized copying of this file, via any medium is strictly prohibited
 * Proprietary and confidential
 * Written by Valentin Dufois <vdufois@neo-ooh.com>
 *
 * @neo/api - ReviewsTemplatesController.php
 */

/** @noinspection PhpUnusedParameterInspection */

namespace Neo\Modules\Broadcast\Http\Controllers;

use Illuminate\Database\Eloquent\Builder;
use Illuminate\Http\Response;
use Illuminate\Support\Facades\Auth;
use Neo\Http\Controllers\Controller;
use Neo\Http\Requests\ReviewsTemplates\DestroyReviewTemplateRequest;
use Neo\Http\Requests\ReviewsTemplates\ListReviewTemplatesRequest;
use Neo\Http\Requests\ReviewsTemplates\StoreReviewTemplateRequest;
use Neo\Http\Requests\ReviewsTemplates\UpdateReviewTemplateRequest;
use Neo\Models\Actor;
use Neo\Modules\Broadcast\Models\ScheduleReviewTemplate;

class ReviewsTemplatesController extends Controller {
    /**
     * @param ListReviewTemplatesRequest $request
     *
     * @return Response
     */
    public function index(ListReviewTemplatesRequest $request): Response {
        /** @var Actor $actor */
        $actor = Auth::user();
        return new Response(ScheduleReviewTemplate::query()
                                                  ->where("owner_id", "=", $actor->id)
                                                  ->when($actor->parent->is_group ?? false,
                                                      fn(Builder $query) => $query->orWhere("owner_id", "=", $actor->parent_id))
                                                  ->get()->loadPublicRelations());
    }

    /**
     * @param StoreReviewTemplateRequest $request
     *
     * @return Response
     */
    public function store(StoreReviewTemplateRequest $request): Response {
        $template = new ScheduleReviewTemplate([
            "text"     => $request->validated()["text"],
            "owner_id" => $request->validated()["owner_id"],
        ]);
        $template->save();

        $template->loadMissing('owner');
        return new Response($template->refresh(), 201);
    }

    /**
     * @param UpdateReviewTemplateRequest $request
     * @param ScheduleReviewTemplate      $template
     *
     * @return Response
     */
    public function update(UpdateReviewTemplateRequest $request, ScheduleReviewTemplate $template) {
        $template->text     = $request->validated()["text"];
        $template->owner_id = $request->validated()["owner_id"];
        $template->save();

        return new Response($template);
    }

    public function destroy(DestroyReviewTemplateRequest $request, ScheduleReviewTemplate $template): void {
        $template->delete();
    }
}
