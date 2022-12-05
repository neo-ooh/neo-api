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
use Neo\Models\ContractFlight;
use Neo\Models\ContractLine;
use Neo\Modules\Broadcast\Models\Location;
use Neo\Modules\Broadcast\Models\Network;
use PhpOffice\PhpSpreadsheet\Spreadsheet;
use PhpOffice\PhpSpreadsheet\Writer\Csv;
use Symfony\Component\HttpFoundation\StreamedResponse;

class ContractsFlightsBroadSignExportController {
    public function index(ListBroadSignExportsRequest $request, ContractFlight $flight) {
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

    public function show(ShowBroadSignExportRequest $request, ContractFlight $flight, Network $network) {
        // A flight exports are broken down by network & formats
        $lines = $flight->lines;
        $lines->load(["product.property", "product.locations"]);

        $networkId  = $network->getKey();
        $categoryId = (int)$request->input("category_id");

        $filteredLines = $lines->filter(fn(ContractLine $line) => $line->product->property->network_id === $networkId && $line->product->category_id === $categoryId);

        $doc   = new Spreadsheet();
        $sheet = new Worksheet(null, $flight->contract->contract_id);
        $doc->addSheet($sheet);
        $doc->removeSheetByIndex(0);

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

        return new StreamedResponse(
            fn() => $writer->save("php://output"),
            200
        );
    }


}
