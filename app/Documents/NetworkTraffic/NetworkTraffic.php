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
            $this->ws->printRow([$property->actor->name]);
        }
    }

    /**
     * @inheritDoc
     */
    public function getName(): string {
        return "Network Traffic Report ";
    }
}
