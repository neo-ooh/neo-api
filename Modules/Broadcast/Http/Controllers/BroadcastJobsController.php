<?php
/*
 * Copyright 2023 (c) Neo-OOH - All Rights Reserved
 * Unauthorized copying of this file, via any medium is strictly prohibited
 * Proprietary and confidential
 * Written by Valentin Dufois <vdufois@neo-ooh.com>
 *
 * @neo/api - BroadcastJobsController.php
 */

namespace Neo\Modules\Broadcast\Http\Controllers;

use Illuminate\Database\Eloquent\Builder;
use Illuminate\Http\Response;
use Neo\Http\Controllers\Controller;
use Neo\Modules\Broadcast\Enums\BroadcastJobStatus;
use Neo\Modules\Broadcast\Exceptions\CannotCancelNonPendingBroadcastJobException;
use Neo\Modules\Broadcast\Http\Requests\BroadcastJobs\ListJobsRequest;
use Neo\Modules\Broadcast\Http\Requests\BroadcastJobs\RetryJobRequest;
use Neo\Modules\Broadcast\Models\BroadcastJob;

class BroadcastJobsController extends Controller {
    public function index(ListJobsRequest $request) {
        $query = BroadcastJob::query();

        if ($request->has("status")) {
            $status = $request->enum("status", BroadcastJobStatus::class);
            if ($status === BroadcastJobStatus::Pending) {
                $query->whereIn("status", [$status, BroadcastJobStatus::PendingRetry]);
            } else {
                $query->where("status", "=", $status);
            }
        }

        if ($request->has("resource_type")) {
            $query->whereHas("resource", function (Builder $query) use ($request) {
                $query->where("type", "=", $request->input("resource_type"));
            });
        }

        $totalCount = $query->clone()->count();

        $page  = $request->input("page", 1);
        $count = $request->input("count", 500);
        $from  = ($page - 1) * $count;
        $to    = ($page * $count) - 1;

        $query->limit($count)
              ->offset($from);

        $query->orderBy("scheduled_at", 'desc');

        return new Response($query->get()->loadPublicRelations(), 200, [
            "Content-Range" => "items $from-$to/$totalCount",
        ]);
    }

    /**
     * @throws CannotCancelNonPendingBroadcastJobException
     */
    public function cancel(BroadcastJob $broadcastJob): Response {
        if ($broadcastJob->status !== BroadcastJobStatus::Pending && $broadcastJob->status === BroadcastJobStatus::PendingRetry) {
            throw new CannotCancelNonPendingBroadcastJobException();
        }

        $broadcastJob->status = BroadcastJobStatus::Cancelled;
        $broadcastJob->save();

        return new Response([]);
    }

    public function retry(RetryJobRequest $request, BroadcastJob $broadcastJob): Response {
        $broadcastJob->execute();

        return new Response(["status" => "ok"]);
    }
}
