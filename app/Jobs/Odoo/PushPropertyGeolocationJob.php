<?php
/*
 * Copyright 2020 (c) Neo-OOH - All Rights Reserved
 * Unauthorized copying of this file, via any medium is strictly prohibited
 * Proprietary and confidential
 * Written by Valentin Dufois <vdufois@neo-ooh.com>
 *
 * @neo/api - PushPropertyGeolocationJob.php
 */

namespace Neo\Jobs\Odoo;

use Illuminate\Bus\Queueable;
use Illuminate\Queue\SerializesModels;
use Illuminate\Queue\InteractsWithQueue;
use Illuminate\Contracts\Queue\ShouldQueue;
use Illuminate\Foundation\Bus\Dispatchable;
use Neo\Models\Property;
use Neo\Services\Odoo\OdooConfig;

class PushPropertyGeolocationJob implements ShouldQueue {
    use Dispatchable, InteractsWithQueue, Queueable, SerializesModels;

    public function __construct(protected int $propertyId) {
    }

    public function handle() {
        /** @var Property $property */
        $property = Property::find($this->propertyId);

        if(!$property || !$property->address || !$property->address->geolocation) {
            // Do nothing on missing data
            return;
        }

        if(!$property->odoo) {
            // Do nothing if not connected to Odoo
            return;
        }

        $odooConfig = OdooConfig::fromConfig();
        $odooClient = $odooConfig->getClient();

        $odooProperty = new \Neo\Services\Odoo\Models\Property($odooClient, [
            "id" => $property->odoo->odoo_id,
            "partner_latitude" => $property->address->geolocation->getLat(),
            "partner_longitude" => $property->address->geolocation->getLng(),
        ]);
        $odooProperty->update(["partner_latitude", "partner_longitude"]);
    }
}
