<?php

namespace Neo\Http\Controllers;

use Illuminate\Http\Response;
use Illuminate\Support\Str;
use Neo\BroadSign\Models\Customer;
use Neo\Models\Report;

class CustomersController extends Controller {
    public function index() {
        $allCustomers = Customer::all();

        // Filter out unwanted customers
        $customers = $allCustomers->filter(function($customer) {
            if($customer->container_id === 14399115 || $customer->container_id === 368311855) {
                return false;
            }

            if(Str::startsWith($customer->name, "**")) {
                return false;
            }

            return true;
        });

        return new Response($customers->sortBy("name")->values());
    }

    public function show(int $customerId) {
        $customer = Customer::get($customerId);
        $customer->reports = Report::where("customer_id", "=", $customer->id)->get();
        return new Response($customer);
    }

    public function update() {

    }
}
