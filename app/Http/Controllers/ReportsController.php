<?php

namespace Neo\Http\Controllers;

use Auth;
use Illuminate\Http\Response;
use Neo\Http\Requests\Reports\ShowReportRequest;
use Neo\Http\Requests\Reports\StoreReportRequest;
use Neo\Jobs\RefreshReportReservations;
use Neo\Models\Report;

class ReportsController extends Controller {
    public function store(StoreReportRequest $request): Response {
        // First, create the contract
        $report = new Report();
        $report->customer_id = $request->get("customer_id");
        $report->contract_id = $request->get("contract_id");
        $report->name = $request->get("name");
        $report->created_by = Auth::id();
        $report->save();

        // Then associate the reservations
        RefreshReportReservations::dispatchSync($report->id);

        return new Response($report);
    }

    public function show(ShowReportRequest $request, Report $report): Response {
        $with = $request->get("with", []);

        if(in_array("customer", $with, true)) {
            $report->append('customer');
        }

        if(in_array("performances", $with, true)) {
            $report->append('performances');
        }

        if(in_array("reservations", $with, true)) {
            $report->load('reservations');
        }

        if(in_array("available_locations", $with, true)) {
            $report->append('available_locations');
        }

        if(in_array("bursts", $with, true)) {
            $report->load('bursts', 'bursts.screenshots', 'bursts.location');
        }

        return new Response($report);
    }
}
