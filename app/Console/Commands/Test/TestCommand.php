<?php
/*
 * Copyright 2023 (c) Neo-OOH - All Rights Reserved
 * Unauthorized copying of this file, via any medium is strictly prohibited
 * Proprietary and confidential
 * Written by Valentin Dufois <vdufois@neo-ooh.com>
 *
 * @neo/api - TestCommand.php
 */

namespace Neo\Console\Commands\Test;

use Illuminate\Console\Command;
use Neo\Jobs\Contracts\ImportContractJob;
use Neo\Models\Contract;

class TestCommand extends Command {
    protected $signature = 'test:test';

    protected $description = 'Internal tests';

    /**
     */
    public function handle() {
//        $workbook = IOFactory::load(Storage::disk("local")->path("adapt-inventory-markets.xlsx"));
//        $sheet    = $workbook->getSheet(2);
//
//        $column = [
//            "Panel ID"                 => 0,
//            "Group"                    => 1,
//            "Name"                     => 2,
//            "Address"                  => 3,
//            "City"                     => 4,
//            "Province"                 => 5,
//            "FSA"                      => 6,
//            "Latitude"                 => 7,
//            "Longitude"                => 8,
//            "Screens"                  => 9,
//            "Gas"                      => 10,
//            "Daily Impressions"        => 11,
//            "COMMB Audited (Yes/No)"   => 12,
//            "Digital (Yes/No)"         => 13,
//            "Spot Length (Secs)"       => 14,
//            "# Spots in Loop"          => 15,
//            "Loop Length (Secs)"       => 16,
//            "Media Type"               => 17,
//            "Orientation"              => 18,
//            "Daily Hours of Operation" => 19,
//            "Market"                   => 20,
//            "Unit Price (Weekly)"      => 21,
//        ];
//
//        $cities       = collect();
//        $productLines = $sheet->toArray();
//        array_shift($productLines);
//
//        foreach ($productLines as $productLine) {
//            $cities[] = [
//                "city"              => trim($productLine[$column["City"]]),
//                "city_normalized"   => strtolower(Normalizer::normalize(trim($productLine[$column["City"]]), Normalizer::NFKD)),
//                "province"          => trim($productLine[$column["Province"]]),
//                "market"            => trim($productLine[$column["Market"]]),
//                "market_normalized" => strtolower(Normalizer::normalize(trim($productLine[$column["Market"]]), Normalizer::NFKD)),
//            ];
//        }
//
//        $cities = $cities->unique(fn(array $city) => $city["city_normalized"] . "-" . $city["province"]);
//        dump($cities->count());
//
//        $odooConfig = OdooConfig::fromConfig();
//        $odooClient = $odooConfig->getClient();
//
//        $odooCities = City::all($odooClient);
//        $odooCities->each(function (City $city) {
//            $city->name_normalized = strtolower(Normalizer::normalize($city->name, Normalizer::NFKD));
//        });
//        $odooMarkets = Market::all($odooClient);
//        $odooMarkets->each(function (Market $market) {
//            $market->name_normalized = strtolower(Normalizer::normalize($market->name, Normalizer::NFKD));
//        });
//
//        foreach ($cities as $city) {
//            if (!$city["market"]) {
//                continue;
//            }
//
//            /** @var City|null $odooCity */
//            $odooCity   = $odooCities->firstWhere("name_normalized", "=", $city["city_normalized"]);
//            $odooMarket = $odooMarkets->firstWhere("name_normalized", "=", $city["market_normalized"]);
//
//            dump($city["city"], $odooCity?->name, $odooCity?->market_id);
//            dump($city["market"], $odooMarket?->name, $odooMarket?->id);
//
////            $this->ask("continue ?");
//
//            if (!$odooCity) {
//                $odooCity                  = City::create($odooClient, [
//                    "name"        => $city["city"],
//                    "description" => $city["city"],
//                    "market_id"   => $odooMarket?->id,
//                ]);
//                $odooCity->name_normalized = strtolower(Normalizer::normalize($odooCity->name, Normalizer::NFKD));
//                $odooCities[]              = $odooCity;
//
//                continue;
//            }
//
//            if ($odooCity->market_id !== $odooMarket?->id) {
//                $odooCity->market_id = $odooMarket?->id;
//                $odooCity->update(["market_id"]);
//            }
//        }

        Contract::query()->where("contract_id", "=", "NEO-150-23")->delete();
        (new ImportContractJob("NEO-150-23", null))->handle();
    }
}
