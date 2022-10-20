<?php
/*
 * Copyright 2020 (c) Neo-OOH - All Rights Reserved
 * Unauthorized copying of this file, via any medium is strictly prohibited
 * Proprietary and confidential
 * Written by Valentin Dufois <vdufois@neo-ooh.com>
 *
 * @neo/api - ContractsFlightsBroadSignExportController.php
 */

namespace Neo\Http\Controllers;

use Illuminate\Http\Response;
use Illuminate\Support\Collection;
use Neo\Documents\XLSX\Worksheet;
use Neo\Http\Requests\ContractsFlights\ListBroadSignExportsRequest;
use Neo\Http\Requests\ContractsFlights\ShowBroadSignExportRequest;
use Neo\Models\Contract;
use Neo\Models\ContractFlight;
use Neo\Models\ContractLine;
use Neo\Models\Location;
use Neo\Models\Network;
use PhpOffice\PhpSpreadsheet\Spreadsheet;
use PhpOffice\PhpSpreadsheet\Writer\Csv;

class ContractsFlightsBroadSignExportController {
    public function index(ListBroadSignExportsRequest $request, Contract $contract, ContractFlight $flight) {
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

    public function show(ShowBroadSignExportRequest $request, Contract $contract, ContractFlight $flight, Network $network) {
        // A flight exports are broken down by network & formats
        $lines = $flight->lines;
        $lines->load(["product.property", "product.locations"]);

        $networkId  = $network->getKey();
        $categoryId = (int)$request->input("category_id");

        $filteredLines = $lines->filter(fn(ContractLine $line) => $line->product->property->network_id === $networkId && $line->product->category_id === $categoryId);

        $doc   = new Spreadsheet();
        $sheet = new Worksheet(null, $contract->contract_id);
        $doc->addSheet($sheet);
        $doc->removeSheetByIndex(0);


        $sheet->printRow([
            "display_unit_id",
            "display_unit_name",
        ]);

        /** @var ContractLine $line */
        foreach ($filteredLines as $line) {
            /** @var Location $location */
            foreach ($line->product->locations as $location) {
                $sheet->printRow([
                    $location->external_id,
                    $location->name,
                ]);
            }
        }

        $writer = new Csv($doc);
        $writer->setEnclosure('"');

        header("access-control-allow-origin: *");
        header("content-type: text/csv");

        $writer->save("php://output");
    }


}
