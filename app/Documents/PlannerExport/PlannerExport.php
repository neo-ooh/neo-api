<?php

namespace Neo\Documents\PlannerExport;

use Illuminate\Support\Collection;
use Illuminate\Support\Facades\Date;
use Neo\Documents\XLSX\XLSXDocument;
use Neo\Documents\XLSX\XLSXStyleFactory;
use PhpOffice\PhpSpreadsheet\Style\Alignment;
use PhpOffice\PhpSpreadsheet\Style\NumberFormat;
use PhpOffice\PhpSpreadsheet\Worksheet\Drawing;

class PlannerExport extends XLSXDocument {
    protected string $contractReference;
    protected Collection $flights;

    /**
     * @param array{properties: array<int>, year: int} $data
     */
    protected function ingest($data): bool {
        $this->contractReference = $data['contract'] ?? "";
        $this->flights           = collect($data['flights'])->map(fn($record) => new Flight($record));

        return true;
    }

    /**
     * @inheritDoc
     */
    public function build(): bool {
        // Print the summary page
        $this->printSummary();
        return true;
    }

    protected function printSummary() {
        $this->ws->pushPosition();

        // Set the header style
        $this->ws->getStyle($this->ws->getRelativeRange(7, 5))->applyFromArray([
            'font'      => [
                'bold'  => true,
                'color' => [
                    'argb' => "FF000000"
                ],
                'size'  => "13",
                "name"  => "Calibri"
            ],
            'alignment' => [
                'horizontal' => Alignment::HORIZONTAL_CENTER,
                'vertical'   => Alignment::VERTICAL_CENTER,
            ]
        ]);

        // Add the Neo logo
        $drawing = new Drawing();
        $drawing->setName('Neo-OOH');
        $drawing->setDescription('Neo Out of Home');
        $drawing->setPath(resource_path("logos/main.light.en@2x.png"));
        $drawing->setHeight(60);
        $drawing->setWorksheet($this->ws);
        $drawing->setCoordinates('D2');

        // Date
        $this->ws->printRow(["Date", Date::now()->toFormattedDateString()]);

        $this->ws->popPosition();
        $this->ws->moveCursor(0, 5);

        $flightsValues = collect();

        // Flights
        foreach ($this->flights as $flightIndex => $flight) {
            $flightsValues->push($this->printFlightSummary($flight, $flightIndex));
        }

        $this->ws->getStyle($this->ws->getRelativeRange(7, 2))->applyFromArray(XLSXStyleFactory::totals());
        $this->ws->mergeCellsRelative(1, 2);

        // Print Totals headers
        $this->ws->printRow([
            'Total',
            __("contract.table-properties"),
            __("contract.table-faces"),
            __("contract.table-traffic"),
            __("contract.table-media-value"),
            __("contract.table-net-investment"),
        ]);

        $this->ws->setRelativeCellFormat(NumberFormat::FORMAT_CURRENCY_USD, 4, 0);
        $this->ws->setRelativeCellFormat(NumberFormat::FORMAT_CURRENCY_USD, 5, 0);

        // Print Totals values
        $this->ws->printRow([
            '',
            $flightsValues->sum("propertiesCount"),
            $flightsValues->sum("faces"),
            $flightsValues->sum("traffic"),
            $flightsValues->sum("mediaValue"),
            $flightsValues->sum("price"),
        ]);

        // Autosize columns
        $this->ws->getColumnDimension("A")->setAutoSize(true);
        $this->ws->getColumnDimension("B")->setAutoSize(true);
        $this->ws->getColumnDimension("C")->setAutoSize(true);
        $this->ws->getColumnDimension("D")->setAutoSize(true);
        $this->ws->getColumnDimension("E")->setAutoSize(true);
        $this->ws->getColumnDimension("F")->setAutoSize(true);
        $this->ws->getColumnDimension("G")->setAutoSize(true);
    }

    protected function printFlightSummary(Flight $flight, $flightIndex) {
        $this->ws->getStyle($this->ws->getRelativeRange(6, 1))->applyFromArray(XLSXStyleFactory::flightRow());

        $this->ws->pushPosition();
        $this->ws->moveCursor(5, 0)->mergeCellsRelative(3, 1);
        $this->ws->popPosition();

        $this->ws->printRow([
            "Flight #" . $flightIndex + 1,
            $flight->startDate->toDateString(),
            '→',
            $flight->endDate->toDateString(),
            __("common.order-type-".$flight->type)
        ]);

        $this->ws->getStyle($this->ws->getRelativeRange(7, 1))->applyFromArray(XLSXStyleFactory::simpleTableHeader());

        $this->ws->printRow([
            __("contract.table-networks"),
            __("contract.table-properties"),
            __("contract.table-faces"),
            __("contract.table-traffic"),
            __("contract.table-media-value"),
            __("contract.table-net-investment"),
            __("contract.table-weeks"),
        ]);

        $networks = $flight->selection->groupBy("property.network.id");

        /** @var Collection $properties */
        foreach($networks as $properties) {
            $this->ws->setRelativeCellFormat("#,##0_-", 1, 0);
            $this->ws->setRelativeCellFormat("#,##0_-", 2, 0);
            $this->ws->setRelativeCellFormat("#,##0_-", 3, 0);
            $this->ws->setRelativeCellFormat(NumberFormat::FORMAT_CURRENCY_USD, 4, 0);
            $this->ws->setRelativeCellFormat(NumberFormat::FORMAT_CURRENCY_USD, 5, 0);

            $this->ws->printRow([
                $properties[0]['property']['network']['name'],
                count($properties),
                $properties->sum("facesCount"),
                $properties->sum("traffic"),
                $properties->sum("mediaValue"),
                $properties->sum("price"),
                $flight->length,
            ]);
        }

        $this->ws->getStyle($this->ws->getRelativeRange(10, 1))->applyFromArray(XLSXStyleFactory::simpleTableTotals());

        $flightValues = [
            "propertiesCount" => count($flight->selection),
            "faces" => $flight->selection->sum("facesCount"),
            "traffic" => $flight->selection->sum("traffic"),
            "mediaValue" => $flight->selection->sum("mediaValue"),
            "price" => $flight->selection->sum("price"),
        ];

        $this->ws->setRelativeCellFormat(NumberFormat::FORMAT_CURRENCY_USD, 4, 0);
        $this->ws->setRelativeCellFormat(NumberFormat::FORMAT_CURRENCY_USD, 5, 0);

        $this->ws->printRow([
            "Total",
            $flightValues["propertiesCount"],
            $flightValues["faces"],
            $flightValues["traffic"],
            $flightValues["mediaValue"],
            $flightValues["price"],
            $flight->length,
        ]);

        $this->ws->moveCursor(0, 2);

        return $flightValues;
    }

    /**
     * @inheritDoc
     */
    public function getName(): string {
        return $this->contractReference ?? 'planner-export';
    }
}
