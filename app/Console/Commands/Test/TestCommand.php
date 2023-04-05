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
use Neo\Modules\Properties\Models\InventoryProvider;
use Neo\Modules\Properties\Services\InventoryAdapterFactory;
use Neo\Modules\Properties\Services\Odoo\Models\Product;

class TestCommand extends Command {
    protected $signature = 'test:test';

    protected $description = 'Internal tests';

    /**
     */
    public function handle() {
        $inventory = InventoryProvider::find(1);
        $odoo      = InventoryAdapterFactory::make($inventory);

        $product = Product::search($odoo->getConfig()->getClient(), [
            ["default_code", "=", "Production"],
        ])->toArray();

        dump($product);
    }
}
