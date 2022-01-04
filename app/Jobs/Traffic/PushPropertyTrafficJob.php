<?php
/*
 * Copyright 2020 (c) Neo-OOH - All Rights Reserved
 * Unauthorized copying of this file, via any medium is strictly prohibited
 * Proprietary and confidential
 * Written by Valentin Dufois <vdufois@neo-ooh.com>
 *
 * @neo/api - PushPropertyTrafficJob.php
 */

namespace Neo\Jobs\Traffic;

use Edujugon\Laradoo\Exceptions\OdooException;
use Illuminate\Bus\Queueable;
use Illuminate\Contracts\Queue\ShouldQueue;
use Illuminate\Foundation\Bus\Dispatchable;
use Illuminate\Queue\InteractsWithQueue;
use Illuminate\Queue\SerializesModels;
use Illuminate\Support\Facades\Log;
use Neo\Models\Property;
use Neo\Services\Odoo\Models\WeeklyTraffic;
use Neo\Services\Odoo\OdooConfig;
use Symfony\Component\Console\Output\ConsoleOutput;

class PushPropertyTrafficJob implements ShouldQueue {
    use Dispatchable, InteractsWithQueue, Queueable, SerializesModels;

    public function __construct(protected int $propertyId) {
    }

    public function handle() {
        $output = new ConsoleOutput();

        /** @var Property|null $property */
        $property = Property::query()->with(["odoo", "traffic.weekly_data"])->find($this->propertyId);

        if (!$property) {
            Log::debug("Could not find property $this->propertyId");
            return;
        }

        if (!$property->odoo) {
            Log::debug("Property #$this->propertyId is not associated with Odoo");
            return;
        }


        $rollingWeeklyTraffic = $property->traffic->getRollingWeeklyTraffic();

        $config = OdooConfig::fromConfig();
        $client = $config->getClient();

        // Make sure Odoo has the proper model set up
        try {
            $client->client->fieldsOf(WeeklyTraffic::$slug);
        } catch (OdooException $e) {
            // Could not find model, do nothing
            return;
        }

        $odooPropertyTraffic = WeeklyTraffic::forProperty($client, $property->odoo->odoo_id);

        $toCreate = [];

        foreach ($rollingWeeklyTraffic as $week => $traffic) {
            /** @var WeeklyTraffic|null $odooTraffic */
            $odooTraffic = $odooPropertyTraffic->firstWhere("week_number", "=", $week - 1);
            $dayTraffic  = $traffic / 7;

            $output->writeln("Week #$week day traffic : $dayTraffic");

            if ($odooTraffic) {
                $odooTraffic->traffic = $dayTraffic;
                $odooTraffic->update(["traffic"]);
            } else {
                $toCreate[] = [
                    "partner_id"  => $property->odoo->odoo_id,
                    "week_number" => $week - 1,
                    "traffic"     => floor($dayTraffic)
                ];
            }
        }

        if (count($toCreate) > 0) {
            WeeklyTraffic::create($client, $toCreate, pullRecord: false);
        }
    }
}
