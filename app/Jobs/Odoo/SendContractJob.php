<?php
/*
 * Copyright 2020 (c) Neo-OOH - All Rights Reserved
 * Unauthorized copying of this file, via any medium is strictly prohibited
 * Proprietary and confidential
 * Written by Valentin Dufois <vdufois@neo-ooh.com>
 *
 * @neo/api - SendContractJob.php
 */

namespace Neo\Jobs\Odoo;

use Carbon\Carbon;
use Illuminate\Bus\Queueable;
use Illuminate\Contracts\Queue\ShouldQueue;
use Illuminate\Foundation\Bus\Dispatchable;
use Illuminate\Queue\InteractsWithQueue;
use Illuminate\Queue\SerializesModels;
use Illuminate\Support\Facades\Bus;
use Neo\Models\Odoo\ProductCategory;
use Neo\Models\Odoo\ProductType;
use Neo\Models\Property;
use Neo\Services\Odoo\Models\Campaign;
use Neo\Services\Odoo\Models\Contract;
use Neo\Services\Odoo\Models\OrderLine;
use Neo\Services\Odoo\Models\Product;
use Neo\Services\Odoo\OdooConfig;

class SendContractJob implements ShouldQueue {
    use Dispatchable, InteractsWithQueue, Queueable, SerializesModels;

    public function __construct(protected Contract $contract, protected array $flights, protected bool $clearOnSend) {
    }

    public function handle() {
        clock()->event('Send contract')->color('purple')->begin();

        $flightsJobs = [];

        // We parse each flight of the contract, if it should be sent, we create a campaign in odoo for it, and add all the required orderlines
        foreach ($this->flights as $flight) {
            if (!$flight['send']) {
                continue;
            }

            $flightsJobs[] = new SendContactFlightJob($this->contract, $flight);
        }

        Bus::chain($flightsJobs);

        clock()->event('Send contract')->end();
    }
}
