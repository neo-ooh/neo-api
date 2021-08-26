<?php
/*
 * Copyright 2020 (c) Neo-OOH - All Rights Reserved
 * Unauthorized copying of this file, via any medium is strictly prohibited
 * Proprietary and confidential
 * Written by Valentin Dufois <vdufois@neo-ooh.com>
 *
 * @neo/api - TestCommand.php
 */

namespace Neo\Console\Commands\Test;

use Illuminate\Console\Command;
use Neo\Jobs\PullAddressGeolocationJob;
use Neo\Models\Property;

class TestCommand extends Command {
    protected $signature = 'test:test';

    protected $description = 'Internal tests';

    public function handle() {
//        $httpClient = new \Http\Adapter\Guzzle7\Client();
//        $provider = new \Geocoder\Provider\GoogleMaps\GoogleMaps($httpClient, null, env('GOOGLE_MAPS_API_KEY'));
//        $geocoder = new \Geocoder\StatefulGeocoder($provider, 'en');
//
//        $result = $geocoder->geocodeQuery(GeocodeQuery::create('Buckingham Palace, London'));

//        dd(Geocoder::geocode('5200 ru\e Parthenais, Montreal H2H, Quebec')->get());

        PullAddressGeolocationJob::dispatchSync(Property::find(106)->address);
    }
}
