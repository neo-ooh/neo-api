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
use Neo\Models\City;

class TestCommand extends Command {
    protected $signature = 'test:test';

    protected $description = 'Internal tests';

    /**
     */
    public function handle() {
//        $actors = ActorsGetter::from(1344)
//                              ->selectChildren(recursive: true)
//                              ->getActors()
//                              ->where("type", "===", ActorType::Property);
//
//        $products = Product::query()->whereIn("property_id", $actors->pluck("id"))->lazy();
//
//        $externalResources = [];
//
//        /** @var Product $product */
//        foreach ($products as $product) {
//            $amId = trim(explode(" - ", $product->name_en, 2)[0]);
//
//            $externalResources[] = [
//                "resource_id"  => $product->inventory_resource_id,
//                "inventory_id" => 2,
//                "type"         => InventoryResourceType::Product,
//                "external_id"  => $amId,
//                "context"      => "[]",
//            ];
//        }
//
//        ExternalInventoryResource::query()->insert($externalResources);

        City::query()->whereDoesntHave("addresses")->delete();
    }
}
