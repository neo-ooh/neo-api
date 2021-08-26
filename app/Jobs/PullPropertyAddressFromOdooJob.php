<?php
/*
 * Copyright 2020 (c) Neo-OOH - All Rights Reserved
 * Unauthorized copying of this file, via any medium is strictly prohibited
 * Proprietary and confidential
 * Written by Valentin Dufois <vdufois@neo-ooh.com>
 *
 * @neo/api - PullPropertyAddressFromBroadSignJob.php
 */

namespace Neo\Jobs;

use Grimzy\LaravelMysqlSpatial\Types\Point;
use Illuminate\Bus\Queueable;
use Illuminate\Contracts\Queue\ShouldQueue;
use Illuminate\Foundation\Bus\Dispatchable;
use Illuminate\Queue\InteractsWithQueue;
use Illuminate\Queue\SerializesModels;
use Neo\Jobs\Odoo\PushPropertyGeolocationJob;
use Neo\Models\Address;
use Neo\Models\City;
use Neo\Models\Location;
use Neo\Models\Property;
use Neo\Models\Province;
use Neo\Services\Broadcast\Broadcast;
use Neo\Services\Broadcast\BroadSign\API\BroadsignClient;
use Neo\Services\Odoo\OdooConfig;
use Symfony\Component\Console\Output\ConsoleOutput;

class PullPropertyAddressFromOdooJob implements ShouldQueue {
    use Dispatchable, InteractsWithQueue, Queueable, SerializesModels;

    public function __construct(protected int $property_id) {
    }

    public function handle() {
        // We start by checking by the property do exist and that it is properly linked with Odoo.
        /** @var Property $property */
        $property = Property::find($this->property_id);

        if(!$property || !$property->odoo) {
            // Do nothing
            return;
        }

        // Pull the property from Odoo
        $odooConfig = OdooConfig::fromConfig();
        $odooClient = $odooConfig->getClient();
        $odooProperty = \Neo\Services\Odoo\Models\Property::get($odooClient, $property->odoo->odoo_id);

        $address = $property->address ?? new Address();
        $address->line_1 = $odooProperty->street;
        $address->line_2 = $odooProperty->street2;
        $address->zipcode = $odooProperty->zip;

        /** @var Province $province */
        $province = Province::query()
                            ->where("slug", "=", $odooProperty->province->code)
                            ->first();

        /** @var City $city */
        $city = City::query()->firstOrCreate([
            "name" => $odooProperty->city,
            "province_id" => $province->id,
        ]);

        $address->city_id = $city->id;
        $address->save();

        $property->address()->associate($address);
        $property->save();

        PullAddressGeolocationJob::dispatchSync($address);

        if($property->odoo) {
            PushPropertyGeolocationJob::dispatchSync($this->property_id);
        }
    }
}
