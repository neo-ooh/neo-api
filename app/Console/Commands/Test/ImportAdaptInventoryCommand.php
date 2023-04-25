<?php
/*
 * Copyright 2023 (c) Neo-OOH - All Rights Reserved
 * Unauthorized copying of this file, via any medium is strictly prohibited
 * Proprietary and confidential
 * Written by Valentin Dufois <vdufois@neo-ooh.com>
 *
 * @neo/api - ImportAdaptInventoryCommand.php
 */

namespace Neo\Console\Commands\Test;

use Carbon\Carbon;
use Illuminate\Console\Command;
use Illuminate\Database\Eloquent\Builder;
use Illuminate\Support\Facades\Storage;
use MatanYadaev\EloquentSpatial\Objects\Point;
use Neo\Models\Actor;
use Neo\Modules\Properties\Enums\TrafficFormat;
use Neo\Modules\Properties\Jobs\Products\ImportProductJob;
use Neo\Modules\Properties\Models\ExternalInventoryResource;
use Neo\Modules\Properties\Models\InventoryProvider;
use Neo\Modules\Properties\Models\Product;
use Neo\Modules\Properties\Models\Property;
use Neo\Modules\Properties\Services\InventoryAdapterFactory;
use Neo\Modules\Properties\Services\Odoo\OdooAdapter;
use Neo\Modules\Properties\Services\Resources\Enums\InventoryResourceType;
use Neo\Modules\Properties\Services\Resources\IdentifiableProduct;
use PhpOffice\PhpSpreadsheet\IOFactory;
use Traversable;

class ImportAdaptInventoryCommand extends Command {
    protected $signature = 'test:test';

    protected $description = 'Internal tests';

    /**
     */
    public function handle() {
        $this->output->info("Loading xlsx...");
        $workbook = IOFactory::load(Storage::disk("local")->path("adapt-digital.xlsx"));
        $sheet    = $workbook->getSheet(0);

        /* $column = [
             "Panel ID"                  => 0,
             "Group"                     => 1,
             "Classification"            => 2,
             "Name"                      => 3,
             "Address"                   => 4,
             "City"                      => 5,
             "Province"                  => 6,
             "FSA"                       => 7,
             "Latitude"                  => 8,
             "Longitude"                 => 9,
             "Screens"                   => 10,
             "Gas"                       => 11,
             "Daily Impressions"         => 12,
             "COMMB Audited (Yes/No)"    => 13,
             "Digital (Yes/No)"          => 14,
             "Spot Length (Secs)"        => 15,
             "# Spots in Loop"           => 16,
             '#Loop Length (Secs)'       => 17,
             '#Media Type'               => 18,
             '#Orientation'              => 19,
             "#Daily Hours of Operation" => 20,
         ];*/

        $provider = InventoryProvider::query()->find(1);
        /** @var OdooAdapter $odoo */
        $odoo       = InventoryAdapterFactory::make($provider);
        $odooClient = $odoo->getConfig()->getClient();

        $productLines = $sheet->toArray();
        $productLines = array_slice($productLines, 1 + 236);

        // Load all our properties in advance
        $properties = Property::query()->with(["address", "external_representations"])->get();
        $this->output->info("Loading odoo odooProducts...");

        $this->output->info("Preloading ok!");

        $progress = $this->output->createProgressBar(count($productLines));
        $progress->setFormat("%current%/%max% [%bar%] %percent:3s%% %message%");
        $progress->start();

        foreach ($productLines as $productLine) {
            if (!$productLine[2]) {
                continue;
            }

            $line = AdaptProductLine::fromLine($productLine);
            $progress->setMessage($line->id . " - " . $line->name);
            $progress->advance();

            $propertyName = $line->name . " - " . $line->address;
            // Using the product address, find a matching property in odoo

            /** @var \Neo\Modules\Properties\Services\Odoo\Models\Property $odooProperty */
            $odooProperty = \Neo\Modules\Properties\Services\Odoo\Models\Property::findBy($odooClient, "street", $line->address)
                                                                                 ->first();

            if (!$odooProperty) {
                // ignore if not found
                continue;
            }

            // Find if the property already exist in Connect
            $property = Property::query()->whereHas("external_representations", function (Builder $query) use ($odooProperty) {
                $query->where("external_id", "=", $odooProperty->getKey());
            })->first();

            if ($property) {
                // If the property already exist, assume its odooProducts have already been imported
                $this->output->writeln("Property already imported: {$propertyName}");
                continue;
            }

            // Import the property
            // Depending on the product type, we don't put the property in the same place
            $this->output->writeln("Create property: {$propertyName}");
            $actor           = new Actor();
            $actor->name     = $propertyName;
            $actor->locale   = 'en';
            $actor->is_group = true;
            $actor->save();

            $parent = Actor::query()->find($line->getParentId());
            $actor->moveTo($parent);

            $property                 = new Property();
            $property->actor_id       = $actor->getKey();
            $property->network_id     = 3;
            $property->last_review_at = Carbon::now();
            $property->pricelist_id   = $line->getPricelist();
            $property->is_sellable    = true;
            $property->save();

            $property->traffic()->create([
                                             "format"            => TrafficFormat::DailyConstant->value,
                                             "placeholder_value" => $line->getDailyTraffic(),
                                         ]);
            dump($line->getDailyTraffic());
            $property->translations()->insert([
                                                  [
                                                      "property_id" => $property->getKey(),
                                                      "locale"      => "fr-CA",
                                                  ],
                                                  [
                                                      "property_id" => $property->getKey(),
                                                      "locale"      => "en-CA",
                                                  ],
                                              ]);
            $property->actor->tags()->sync($line->getTags());
            $property->refresh();

            $property->fields_values()->insert([
                                                   "fields_segment_id" => 3,
                                                   "value"             => $line->getDwellTime(),
                                                   "property_id"       => $property->getKey(),
                                               ]);

            // Associate the property
            $this->output->writeln("Store external representation for property: $propertyName");
            $externalResource               = new ExternalInventoryResource();
            $externalResource->resource_id  = $property->inventory_resource_id;
            $externalResource->inventory_id = $odoo->getInventoryID();
            $externalResource->type         = InventoryResourceType::Property;
            $externalResource->external_id  = $odooProperty->getKey();
            $externalResource->context      = [];
            $externalResource->created_by   = 1;
            $externalResource->save();

            // List the property odooProducts in odoo
            /** @var Traversable<IdentifiableProduct> $odooProducts */
            $odooProducts = $odoo->listPropertyProducts($externalResource->toInventoryResourceId());

            /** @var IdentifiableProduct $odooProduct */
            foreach ($odooProducts as $odooProduct) {
                $this->output->writeln("Import product: {$odooProduct->product->name[0]->value}");
                $job = new ImportProductJob($odoo->getInventoryID(), $property->getKey(), $odooProduct->resourceId);
                $job->handle();

                /**
                 * @var Product $product
                 */
                $product          = $job->getResult();
                $product->name_en = $line->id . " - " . $product->name_en;
                $product->name_fr = $line->id . " - " . $product->name_fr;
                $product->save();
            }

            $property->refresh();
            $address              = $property->address;
            $address->geolocation = new Point($line->lat, $line->lng);
            $address->save();
        }
    }
}
