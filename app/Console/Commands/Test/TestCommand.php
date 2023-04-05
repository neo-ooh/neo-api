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
use Neo\Modules\Properties\Models\ExternalInventoryResource;
use Neo\Modules\Properties\Models\InventoryProvider;
use Neo\Modules\Properties\Models\Property;
use Neo\Modules\Properties\Services\InventoryAdapterFactory;
use Neo\Modules\Properties\Services\Odoo\OdooAdapter;
use Neo\Modules\Properties\Services\Resources\IdentifiableProduct;

class TestCommand extends Command {
    protected $signature = 'test:test';

    protected $description = 'Internal tests';

    /**
     */
    public function handle() {
        $inventory = InventoryProvider::find(1);
        /** @var OdooAdapter $odoo */
        $odoo = InventoryAdapterFactory::make($inventory);

        $property = Property::query()->find(1080);
        /** @var ExternalInventoryResource $representation */
        $representation = $property->external_representations->firstWhere("inventory_id", "=", $odoo->getInventoryID());

        dump(collect($odoo->listPropertyProducts($representation->toInventoryResourceId()))
                 ->map(fn(IdentifiableProduct $p) => [$p->product->name[0]->value, $p->resourceId->external_id])
                 ->toArray());
    }
}
