<?php

namespace Neo\Http\Controllers;

use Illuminate\Http\Response;
use Neo\Http\Requests\Reports\ShowReportRequest;
use Neo\Http\Requests\Reports\StoreReportRequest;
use Neo\Models\Report;

class ReportsController extends Controller {
    public function store(StoreReportRequest $request): Response {
        $report = new Report();
        $report->customer_id = $request->get("customer_id");
        $report->reservation_id = $request->get("reservation_id");
        $report->name = $request->get("name");
        $report->save();

        return new Response($report);
    }

    public function show(ShowReportRequest $request, Report $report): Response {
        $with = $request->get("with", []);

        if(in_array("customer", $with, true)) {
            $report->append('customer');
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
