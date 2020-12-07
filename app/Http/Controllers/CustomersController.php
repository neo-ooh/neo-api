<?php

namespace Neo\Http\Controllers;

use Illuminate\Http\Response;
use Neo\BroadSign\Models\Customer;
use Neo\Models\Report;

class CustomersController extends Controller {
    public function index() {
        return new Response(Customer::all()->sortBy("name")->values());
    }

    public function show(int $customerId) {
        $customer = Customer::get($customerId);
        $customer->reports = Report::where("customer_id", "=", $customer->id)->get();
        $customer->campaigns = $customer->getCampaigns();
        return new Response($customer);
    }
}
