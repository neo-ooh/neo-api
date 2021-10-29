<?php

namespace Neo\Documents\PlannerExport;

use Illuminate\Support\Collection;
use Illuminate\Support\Facades\Date;
use Neo\Documents\XLSX\Worksheet;
use Neo\Documents\XLSX\XLSXDocument;
use Neo\Documents\XLSX\XLSXStyleFactory;
use PhpOffice\PhpSpreadsheet\Style\Alignment;
use PhpOffice\PhpSpreadsheet\Style\Fill;
use PhpOffice\PhpSpreadsheet\Style\NumberFormat;
use PhpOffice\PhpSpreadsheet\Worksheet\Drawing;

class PlannerExport extends XLSXDocument {
    protected string $contractReference;
    protected Collection $flights;
    protected int $propertiesCount;
    protected int $facesCount;

    /**
     * @param array{properties: array<int>, year: int} $data
     */
    protected function ingest($data): bool {
        $this->contractReference = $data['contract'] ?? "";
        $this->flights           = collect($data['flights'])->map(fn($record) => new Flight($record));
        $this->propertiesCount   = $data['stats']['propertiesCount'];
        $this->facesCount        = $data['stats']['facesCount'];

        return true;
    }

    /**
     * @inheritDoc
     */
    public function build(): bool {
        $firstSheetName  = $this->contractReference ?? __("contract.summary");
        $this->worksheet = new Worksheet(null, $firstSheetName);
        $this->spreadsheet->addSheet($this->worksheet);
        $this->spreadsheet->setActiveSheetIndexByName($firstSheetName);

        // Remove the first sheet as it is not being used
        $this->spreadsheet->removeSheetByIndex(0);

        // Print the summary page
        $this->printSummary();

        // Print each flight's details page
        foreach ($this->flights as $flightIndex => $flight) {
            $this->printFlight($flight, $flightIndex);
        }

        $this->spreadsheet->setActiveSheetIndexByName($firstSheetName);
        return true;
    }

    protected function printSummary() {

        $this->ws->pushPosition();

        // Set the header style
        $this->ws->getStyle($this->ws->getRelativeRange(7, 5))->applyFromArray([
            'font'      => [
                'bold'  => true,
                'color' => [
                    'argb' => "FFFFFFFF"
                ],
                'size'  => "13",
                "name"  => "Calibri"
            ],
            'alignment' => [
                'horizontal' => Alignment::HORIZONTAL_CENTER,
                'vertical'   => Alignment::VERTICAL_CENTER,
            ],
            'fill'      => [
                'fillType'   => Fill::FILL_SOLID,
                'startColor' => [
                    'argb' => XLSXStyleFactory::COLORS["dark-blue"],
                ],
            ],
        ]);

        // Add the Neo logo
        $drawing = new Drawing();
        $drawing->setName('Neo-OOH');
        $drawing->setDescription('Neo Out of Home');
        $drawing->setPath(resource_path("logos/main.light.en.png"));
        $drawing->setHeight(65);
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
            $this->propertiesCount,
            $this->facesCount,
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
        $this->ws->moveCursor(5, 0)->mergeCellsRelative(2, 1);
        $this->ws->popPosition();

        $this->ws->printRow([
            "Flight #" . $flightIndex + 1,
            $flight->startDate->toDateString(),
            '→',
            $flight->endDate->toDateString(),
            __("common.order-type-" . $flight->type)
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
        foreach ($networks as $properties) {
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
            "faces"           => $flight->selection->sum("facesCount"),
            "traffic"         => $flight->selection->sum("traffic"),
            "mediaValue"      => $flight->selection->sum("mediaValue"),
            "price"           => $flight->selection->sum("price"),
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

    public function printFlightHeader(Flight $flight, int $flightIndex) {
        $this->ws->getStyle($this->ws->getRelativeRange(6, 1))->applyFromArray(XLSXStyleFactory::flightRow());

        $this->ws->pushPosition();
        $this->ws->moveCursor(5, 0)->mergeCellsRelative(2, 1);
        $this->ws->popPosition();

        $this->ws->printRow([
            "Flight #" . $flightIndex + 1,
            $flight->startDate->toDateString(),
            '→',
            $flight->endDate->toDateString(),
            __("common.order-type-" . $flight->type)
        ]);
    }

    public function printFlight(Flight $flight, int $flightIndex) {
        $flightLabel     = "Flight #" . $flightIndex + 1;
        $this->worksheet = new Worksheet(null, $flightLabel);
        $this->spreadsheet->addSheet($this->worksheet);
        $this->spreadsheet->setActiveSheetIndexByName($flightLabel);

        $this->printFlightHeader($flight, $flightIndex);

        // Property / Products table header
        $this->ws->getStyle($this->ws->getRelativeRange(7, 1))->applyFromArray(XLSXStyleFactory::simpleTableHeader());

        $this->ws->printRow([
            __("contract.table-properties"),
            __("contract.table-zipcode"),
            __("contract.table-location"),
            __("contract.table-faces"),
            __("contract.table-traffic"),
            __("contract.table-media-value"),
            __("contract.table-net-investment"),
        ]);

        foreach ($flight->selection as $property) {
            $this->ws->setRelativeCellFormat("#,##0_-", 3, 0);
            $this->ws->setRelativeCellFormat("#,##0_-", 4, 0);
            $this->ws->setRelativeCellFormat(NumberFormat::FORMAT_CURRENCY_USD, 4, 0);
            $this->ws->setRelativeCellFormat(NumberFormat::FORMAT_CURRENCY_USD, 5, 0);

            $this->ws->printRow([
                $property["property"]["name"],
                $property["property"]["address"]["zipcode"],
                $property["property"]["address"]["city"]["name"],
                $property["facesCount"],
                $property["traffic"],
                $property["mediaValue"],
                $property["price"],
            ]);
        }
    }

    /**
     * @inheritDoc
     */
    public function getName(): string {
        return $this->contractReference ?? 'planner-export';
    }
}
