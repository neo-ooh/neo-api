<?php
/*
 * Copyright 2020 (c) Neo-OOH - All Rights Reserved
 * Unauthorized copying of this file, via any medium is strictly prohibited
 * Proprietary and confidential
 * Written by Valentin Dufois <vdufois@neo-ooh.com>
 *
 * @neo/api - CampaignsController.php
 */

namespace Neo\Modules\Broadcast\Http\Controllers;

use Auth;
use Exception;
use Illuminate\Http\Response;
use Illuminate\Support\Facades\DB;
use Neo\Http\Controllers\Controller;
use Neo\Modules\Broadcast\Enums\ScheduleStatus;
use Neo\Modules\Broadcast\Http\Requests\Campaigns\DestroyCampaignRequest;
use Neo\Modules\Broadcast\Http\Requests\Campaigns\ListCampaignsRequest;
use Neo\Modules\Broadcast\Http\Requests\Campaigns\ShowCampaignRequest;
use Neo\Modules\Broadcast\Http\Requests\Campaigns\StoreCampaignRequest;
use Neo\Modules\Broadcast\Http\Requests\Campaigns\UpdateCampaignRequest;
use Neo\Modules\Broadcast\Models\Campaign;
use Neo\Modules\Broadcast\Models\Schedule;

class CampaignsController extends Controller {
    /**
     * @param ListCampaignsRequest $request
     *
     * @return Response
     * @noinspection PhpUnusedParameterInspection
     */
    public function index(ListCampaignsRequest $request): Response {
        return new Response($request->user()->getCampaigns()->loadPublicRelations());
    }

    /**
     * @param StoreCampaignRequest $request
     *
     * @return Response
     * @throws Exception
     */
    public function store(StoreCampaignRequest $request): Response {
        $campaign = new Campaign();

        $campaign->creator_id     = Auth::id();
        $campaign->parent_id      = $request->input("parent_id");
        $campaign->name           = $request->input("name");
        $campaign->start_date     = $request->input("start_date");
        $campaign->start_time     = $request->input("start_time");
        $campaign->end_date       = $request->input("end_date");
        $campaign->end_time       = $request->input("end_time");
        $campaign->broadcast_days = $request->input("broadcast_days");

        $campaign->occurrences_in_loop = $request->input("occurrences_in_loop");
        $campaign->priority            = $request->input("priority");

        // We create the campaign and attach its location in a transaction as we want to prevent the campaign creation if there is a problem with the locations
        try {
            DB::beginTransaction();
            $campaign->save();

            // Set the campaign locations
            $locations = collect($request->input("locations"));
            $campaign->locations()
                     ->sync($locations->mapWithKeys(fn(array $locationDefinition) => [$locationDefinition["location_id"], ["format_id" => $locationDefinition["format_id"]]]));

            DB::commit();
        } catch (Exception $e) {
            DB::rollBack();

            throw $e;
        }

        $campaign->broadcast_tags()->sync($request->input("tags"));

        // Replicate the campaign in the appropriate broadcaster
        $campaign->promote();

        return new Response($campaign->withPublicRelations(), 201);
    }

    /**
     * @param ShowCampaignRequest $request
     * @param Campaign            $campaign
     *
     * @return Response
     */
    public function show(ShowCampaignRequest $request, Campaign $campaign): Response {
        return new Response($campaign->withPublicRelations());
    }

    /**
     * @param UpdateCampaignRequest $request
     * @param Campaign              $campaign
     *
     * @return Response
     */
    public function update(UpdateCampaignRequest $request, Campaign $campaign): Response {
        $campaign->parent_id      = $request->input("parent_id");
        $campaign->name           = $request->input("name");
        $campaign->start_date     = $request->input("start_date");
        $campaign->start_time     = $request->input("start_time");
        $campaign->end_date       = $request->input("end_date");
        $campaign->end_time       = $request->input("end_time");
        $campaign->broadcast_days = $request->input("broadcast_days");

        $campaign->occurrences_in_loop = $request->input("occurrences_in_loop");
        $campaign->priority            = $request->input("priority");
        $campaign->save();

        $campaign->broadcast_tags()->sync($request->input("tags"));

        $campaign->refresh();

        // We need to validate all the campaign's schedules' dates, times and days that haven't finished playing yet.
        $schedules = $campaign->schedules()->get();

        /** @var Schedule $schedule */
        foreach ($schedules as $schedule) {
            // Ignore schedules that will not play
            if ($schedule->status === ScheduleStatus::Expired || $schedule->status === ScheduleStatus::Rejected || $schedule->status === ScheduleStatus::Trashed) {
                continue;
            }

            // Dates
            $schedule->start_date = $schedule->start_date->isBetween($campaign->start_date, $campaign->end_date)
                ? $schedule->start_date
                : $campaign->start_date->copy();
            $schedule->end_date   = $schedule->end_date->isBetween($campaign->start_date, $campaign->end_date)
                ? $schedule->end_date
                : $campaign->end_date->copy();

            // Times
            $schedule->start_time = $schedule->start_time->isBetween($campaign->start_time, $campaign->end_time)
                ? $schedule->start_time
                : $campaign->start_time->copy();
            $schedule->end_time   = $schedule->end_time->isBetween($campaign->start_time, $campaign->end_time)
                ? $schedule->end_time
                : $campaign->end_time->copy();

            // Weekdays
            $schedule->broadcast_days &= $campaign->broadcast_days;

            $schedule->save();
        }

        $campaign->promote();

        return new Response($campaign->withPublicRelations());
    }

    public function destroy(DestroyCampaignRequest $request, Campaign $campaign): Response {
        $campaign->delete();

        return new Response(["result" => "ok"]);
    }
}
