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


use Carbon\Carbon;
use Illuminate\Database\Eloquent\Collection;
use Neo\Documents\XLSX\Worksheet;
use Neo\Documents\XLSX\XLSXDocument;
use Neo\Documents\XLSX\XLSXStyleFactory;
use Neo\Models\DisplayType;
use Neo\Models\DisplayTypePrintsFactors;
use Neo\Models\Network;
use Neo\Models\Property;
use PhpOffice\PhpSpreadsheet\Style\Border;

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
        for ($i = 0; $i < 27; ++$i) {
            $this->ws->getColumnDimensionByColumn($i)->setWidth(15);
        }

        $this->ws->getColumnDimension('A')->setAutoSize(true);
        $this->ws->getColumnDimension('B')->setAutoSize(true);

        return true;
    }

    public function printNetwork(Network $network) {
        // Start by printing our header
        // Set row style
        $this->ws->getStyle($this->ws->getRelativeRange(26, 2))->applyFromArray(XLSXStyleFactory::tableHeader());
        $this->ws->getRowDimension($this->ws->getCursorRow())->setRowHeight(30);

        $this->ws->pushPosition();

        // Print header
        $this->ws->printRow([
            "Properties",
            "Products",
        ]);

        $this->ws->popPosition();
        $this->ws->pushPosition();
        $this->ws->moveCursor(2, 0);

        for ($i = 0; $i < 12; $i++) {
            $this->ws->pushPosition();
            $this->ws->mergeCellsRelative(2, 1);

            $month = Carbon::create($this->year, $i + 1)->monthName;
            $this->ws->printRow([$month]);
            $this->ws->printRow(["Monthly", "Weekly"]);

            $this->ws->popPosition();
            $this->ws->moveCursor(2, 0);
        }

        $this->ws->popPosition();
        $this->ws->moveCursor(0, 2);

        // Now print each property
        /**
         * @var Property $property
         */
        foreach ($network->properties as $property) {
            // Get the traffic information for the year
            $trafficData = collect();
            for ($i = 0; $i < 12; $i++) {
                $trafficData[] = $property->getTraffic($this->year, $i);
            }

            // We print one row per property product
            $products = $property->actor->own_locations->pluck("display_type")->unique("id");

            /**
             * @var DisplayType $product
             */
            foreach ($products as $product) {
                $values = collect();

                $this->ws->getStyle($this->ws->getRelativeRange(2, 1))
                         ->getBorders()
                         ->getRight()
                         ->setBorderStyle(Border::BORDER_THICK);

                $this->ws->pushPosition();

                foreach ($trafficData as $month => $traffic) {
                    $this->ws->moveCursor(2, 0);
                    // Take advantage of the loop for each month value to setup proper formatting of values for the row
                    $this->ws->setRelativeCellFormat('#,##0.00', 0, 0);
                    $this->ws->setRelativeCellFormat('#,##0.00', 1, 0);

                    $this->ws->getStyle($this->ws->getRelativeRange(2, 1))
                            ->getBorders()
                            ->getRight()
                            ->setBorderStyle(Border::BORDER_THICK);

                    $period = $this->getPeriod($network->id, $product->id, $month);

                    // If no period calculator are available, skip product
                    if (!$period) {
                        $values[] = null;
                        $values[] = null;
                        continue;
                    }

                    $prints   = $period->getPrintsForTraffic($traffic);
                    $values[] = $prints;
                    $values[] = ($prints / Carbon::create($this->year, $month)->daysInMonth) * 7;
                }

                $this->ws->popPosition();

                $this->ws->printRow([
                    $property->actor->name,
                    $product->name,
                    ...$values
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
         */ fn($p) => $p->displayTypes->contains($displayTypeId)
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
