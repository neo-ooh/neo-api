<?php
/*
 * Copyright 2023 (c) Neo-OOH - All Rights Reserved
 * Unauthorized copying of this file, via any medium is strictly prohibited
 * Proprietary and confidential
 * Written by Valentin Dufois <vdufois@neo-ooh.com>
 *
 * @neo/api - DedupProductsLocationsCommand.php
 */

namespace Neo\Console\Commands;

use Illuminate\Console\Command;
use Neo\Modules\Properties\Models\Product;

class DedupProductsLocationsCommand extends Command {
    protected $signature = 'products:dedup-locations';

    protected $description = 'Command description';

    public function handle() {
        $products = Product::query()->with(["property", "property.actor"])->whereHas("locations", null, ">", 1)->get();
        $warn     = $this->getOutput()->getOutput()->section();
        $info     = $this->getOutput()->getOutput()->section();

        $i = 0;

        foreach ($products as $product) {
            if ($i % 10 === 0) {
                $info->clear();
            }

            $allLocationsIds = $product->locations()->allRelatedIds();
            $locationsIds    = $allLocationsIds->unique();

            $info->write("<info>$product->name_en@{$product->property->actor->name}: OK</info>");

            if ($allLocationsIds->count() !== $locationsIds->count()) {
                $info->clear();
                $warn->writeln("<comment>$product->name_en@{$product->property->actor->name}: {$allLocationsIds->join(", ")} => {$locationsIds->join(", ")}</comment>");

                $product->locations()->sync([]);
                $product->locations()->sync($locationsIds);
            }

            ++$i;
        }

    }
}
