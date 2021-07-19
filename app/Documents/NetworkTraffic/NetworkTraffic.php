<?php
/*
 * Copyright 2020 (c) Neo-OOH - All Rights Reserved
 * Unauthorized copying of this file, via any medium is strictly prohibited
 * Proprietary and confidential
 * Written by Valentin Dufois <vdufois@neo-ooh.com>
 *
 * @neo/api - NetworkTraffic.php
 */

namespace Neo\Documents\NetworkTraffic;


use Illuminate\Database\Eloquent\Collection;
use Neo\Documents\XLSX\Worksheet;
use Neo\Documents\XLSX\XLSXDocument;
use Neo\Documents\XLSX\XLSXStyleFactory;
use Neo\Models\DisplayType;
use Neo\Models\DisplayTypePrintsFactors;
use Neo\Models\Network;
use Neo\Models\Property;

class NetworkTraffic extends XLSXDocument {

    protected int $year;

    /**
     * @var Collection<Network>
     */
    protected Collection $networks;

    /**
     * @var Collection<DisplayTypePrintsFactors>
     */
    protected Collection $periods;

    public function __construct(int $year, Collection $networks, Collection $periods) {
        parent::__construct();
        $this->ingest([$year, $networks, $periods]);
    }

    /**
     * @param [Collection<Network>, Collection<DisplayTypePrintsFactors>] $data
     * @inheritDoc
     */
    protected function ingest($data): bool {
        [$this->year, $this->networks, $this->periods] = $data;

        return true;
    }

    /**
     * @inheritDoc
     */
    public function build(): bool {
        foreach ($this->networks as $network) {
            $this->worksheet = new Worksheet(null, $network->name);
            $this->spreadsheet->addSheet($this->worksheet);
            $this->spreadsheet->setActiveSheetIndexByName($network->name);

            $this->printNetwork($network);
        }

        // Remove the first sheet as it is not being used
        $this->spreadsheet->removeSheetByIndex(0);

        // Autosize columns
        $this->ws->getColumnDimension("A")->setAutoSize(true);
        $this->ws->getColumnDimension("B")->setAutoSize(true);
        $this->ws->getColumnDimension("C")->setAutoSize(true);
        $this->ws->getColumnDimension("D")->setAutoSize(true);
        $this->ws->getColumnDimension("E")->setAutoSize(true);
        $this->ws->getColumnDimension("F")->setAutoSize(true);
        $this->ws->getColumnDimension("G")->setAutoSize(true);
        $this->ws->getColumnDimension("H")->setAutoSize(true);
        $this->ws->getColumnDimension("I")->setAutoSize(true);
        $this->ws->getColumnDimension("J")->setAutoSize(true);
        $this->ws->getColumnDimension("K")->setAutoSize(true);
        $this->ws->getColumnDimension("L")->setAutoSize(true);
        $this->ws->getColumnDimension("M")->setAutoSize(true);

        return true;
    }

    public function printNetwork(Network $network) {
        // Start by printing our header
        // Set row style
        $this->ws->getStyle($this->ws->getRelativeRange(14, 1))->applyFromArray(XLSXStyleFactory::tableHeader());
        $this->ws->getRowDimension($this->ws->getCursorRow())->setRowHeight(30);

        // Print header
        $this->ws->printRow([
            "Property",
            "Product",
            "January",
            "February",
            "March",
            "April",
            "May",
            "June",
            "July",
            "August",
            "September",
            "October",
            "November",
            "December",
        ]);

        // Now print each property
        /**
         * @var Property $property
         */
        foreach ($network->properties as $property) {
            // Get the traffic information for the year
            $trafficData = collect();
            for ($i = 0; $i < 12; $i++) {
                $trafficData[] = $property->getTraffic($this->year, 0);
            }

            // We print one row per property product
            $products = $property->actor->own_locations->pluck("display_type")->unique("id");

            /**
             * @var DisplayType $product
             */
            foreach ($products as $product) {
                $prints = collect();

                foreach ($trafficData as $month => $traffic) {
                    $period = $this->getPeriod($network->id, $product->id, $month);

                    // If no period calculator are available, skip product
                    if(!$period) {
                        $prints[] = null;
                        continue;
                    }

                    $prints[] = $period->getPrintsForTraffic($traffic);
                }

                $this->ws->printRow([
                    $property->actor->name,
                    $product->name,
                    ...$prints
                ]);

            }


        }
    }

    /**
     * @param int $networkId
     * @param int $displayTypeId
     * @param int $month 0-indexed month
     *
     * @return DisplayTypePrintsFactors | null
     */
    public function getPeriod(int $networkId, int $displayTypeId, int $month) {
        return $this->periods->first(/**
         * @param DisplayTypePrintsFactors $p
         */ fn($p) => $p->displayTypes()->where('id', "=", $displayTypeId)->exists()
            && $p->network_id === $networkId
            && $p->start_month <= $month + 1
            && $p->end_month >= $month + 1
        );
    }

    /**
     * @inheritDoc
     */
    public function getName(): string {
        return "Network Traffic Report ";
    }
}
