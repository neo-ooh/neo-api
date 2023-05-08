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
use Neo\Modules\Properties\Exceptions\Synchronization\UnsupportedInventoryFunctionalityException;
use Neo\Modules\Properties\Jobs\Products\PullProductJob;
use Neo\Modules\Properties\Models\InventoryProvider;
use Neo\Modules\Properties\Models\Product;
use Neo\Modules\Properties\Services\Exceptions\InvalidInventoryAdapterException;
use Neo\Modules\Properties\Services\Vistar\VistarAdapter;

class TestCommand extends Command {
    protected $signature = 'test:test';

    protected $description = 'Internal tests';

    /**
     * @return void
     * @throws UnsupportedInventoryFunctionalityException
     * @throws InvalidInventoryAdapterException
     */
    public function handle() {
//        (new PullFullInventoryJob(1, debug: true))->handle();
//        $file = BroadSignAudienceFile::make(Location::query()->firstWhere("external_id", "=", 387294643));
//        $file->build();

//        $product = Product::find(2450);
//        dump($product->toResource(4)->toArray());

//        dump(Carbon::now()->subMonths(2)->format("D, d M Y H:i:s \G\M\T"));
        $provider = InventoryProvider::query()->find(1);
        /** @var VistarAdapter $inventory */
        $inventory = $provider->getAdapter();

        $product = Product::query()->find(2450);
//        $job     = new CreateProductJob($product->inventory_resource_id, $inventory->getInventoryID(), [
//            "venue_type" => "Retail|Gas Stations",
//            "network_id" => "FkSuEcdwQxSfrEyUHbdjeQ",
//        ]);

//        $job = new PushProductJob($product->inventory_resource_id, $inventory->getInventoryID());
        $job = new PullProductJob($product->inventory_resource_id, $inventory->getInventoryID());
//        $job = new DestroyProductJob($product->inventory_resource_id, $inventory->getInventoryID());
        $job->handle();
        dump($job->getResult());
    }
}
