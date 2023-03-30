<?php
/*
 * Copyright 2023 (c) Neo-OOH - All Rights Reserved
 * Unauthorized copying of this file, via any medium is strictly prohibited
 * Proprietary and confidential
 * Written by Valentin Dufois <vdufois@neo-ooh.com>
 *
 * @neo/api - ContractsFlightsExportController.php
 */

namespace Neo\Http\Controllers;

use Illuminate\Http\Response;
use Illuminate\Support\Collection;
use Neo\Documents\XLSX\Worksheet;
use Neo\Http\Requests\ContractsFlights\GetExportRequest;
use Neo\Http\Requests\ContractsFlights\ListExportsRequest;
use Neo\Models\ContractFlight;
use Neo\Models\ContractLine;
use Neo\Modules\Broadcast\Models\Location;
use Neo\Modules\Broadcast\Models\Network;
use PhpOffice\PhpSpreadsheet\Spreadsheet;
use PhpOffice\PhpSpreadsheet\Writer\Csv;
use Symfony\Component\HttpFoundation\StreamedResponse;

class ContractsFlightsExportController {
    public function index(ListExportsRequest $request, ContractFlight $flight) {
        // A flight exports are broken down by network & formats
        $lines = $flight->lines;
        $lines->load(["product.property"]);

        $breakdown = collect();

        foreach ($lines as $line) {
            $networkId  = $line->product->property->network_id;
            $categoryid = $line->product->category_id;

            if (!isset($breakdown[$networkId])) {
                $breakdown[$networkId] = collect();
            }

            $breakdown[$networkId][] = $categoryid;
        }

        $breakdown = $breakdown->map(fn(Collection $categories) => $categories->unique()->values());

        return new Response($breakdown);
    }

    public function show(GetExportRequest $request, ContractFlight $flight, Network $network) {
        $networkId   = $network->getKey();
        $categoryId  = (int)$request->input("category_id");
        $serviceType = $request->input("service_type");
        $serviceId   = $request->input("service_id");

        // A flight exports are broken down by network & formats
        $lines = $flight->lines()
                        ->with(["product.property.actor", "product.locations", "product.external_representations"])
                        ->get()
                        ->filter(fn(ContractLine $line) => $line->product->property->network_id === $networkId && $line->product->category_id === $categoryId);

        $doc   = new Spreadsheet();
        $sheet = new Worksheet(null, $flight->contract->contract_id);
        $doc->addSheet($sheet);
        $doc->removeSheetByIndex(0);

        // Print header
        $sheet->printRow([
                             $serviceId === "broadcaster" ? "Location Id" : "Inventory Id",
                             $serviceId === "broadcaster" ? "Location" : "Product",
                             "spots",
                         ]);

        $builtLines = $lines->flatMap(function (ContractLine $line) use ($serviceType, $serviceId) {
            return match ($serviceType) {
                "broadcaster" => $line->product->locations->map(fn(Location $location) => [$location->external_id, $location->name, $line->spots]),
                "inventory"   => [[$line->product->external_representations->firstWhere("inventory_id", "=", $serviceId)?->external_id ?? "-", $line->product->name_en, $line->spots]],
            };
        });

        $sheet->fromArray($builtLines->toArray());

        $writer = new Csv($doc);
        $writer->setEnclosure('"');

        return new StreamedResponse(
            fn() => $writer->save("php://output"),
            200
        );
    }


}
