<?php

namespace Neo\Documents\PlannerExport;

use Illuminate\Support\Collection;
use Illuminate\Support\Facades\App;
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
    protected array $columns;

    /**
     * @param array{properties: array<int>, year: int} $data
     */
    protected function ingest($data): bool {
        $this->contractReference = $data['contract'] ?? "";
        $this->flights           = collect($data['flights'])->map(fn($record) => new Flight($record));
        $this->propertiesCount   = $data['stats']['propertiesCount'];
        $this->facesCount        = $data['stats']['facesCount'];
        $this->columns           = $data['columns'];

        return true;
    }

    /**
     * @inheritDoc
     */
    public function build(): bool {
        $firstSheetName  = __("contract.summary");
        $this->worksheet = new Worksheet(null, $firstSheetName);
        $this->spreadsheet->addSheet($this->worksheet);
        $this->spreadsheet->setActiveSheetIndex(1);

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
        $this->ws->getStyle($this->ws->getRelativeRange(8, 5))->applyFromArray([
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

        $this->ws->getStyle($this->ws->getRelativeRange(8, 2))->applyFromArray(XLSXStyleFactory::totals());
        $this->ws->mergeCellsRelative(1, 2);

        // Print Totals headers
        $this->ws->printRow([
            'Total',
            __("contract.table-properties"),
            in_array("faces", $this->columns, true) ? __("contract.table-faces") : "",
            in_array("traffic", $this->columns, true) ? __("contract.table-traffic") : "",
            in_array("impressions", $this->columns, true) ? __("contract.table-impressions") : "",
            in_array("media-value", $this->columns, true) ? __("contract.table-media-value") : "",
            in_array("price", $this->columns, true) ? __("contract.table-net-investment") : "",
        ]);

        $this->ws->setRelativeCellFormat(NumberFormat::FORMAT_CURRENCY_USD, 4, 0);
        $this->ws->setRelativeCellFormat(NumberFormat::FORMAT_CURRENCY_USD, 5, 0);

        // Print Totals values
        $this->ws->printRow([
            '',
            $this->propertiesCount,
            in_array("faces", $this->columns, true) ? $this->facesCount : "",
            in_array("traffic", $this->columns, true) ? $flightsValues->sum("traffic") : "",
            in_array("impressions", $this->columns, true) ? $flightsValues->sum("impressions") : "",
            in_array("media-value", $this->columns, true) ? $flightsValues->sum("mediaValue") : "",
            in_array("price", $this->columns, true) ? $flightsValues->sum("price") : "",
        ]);

        // Autosize columns
        $this->ws->getColumnDimension("A")->setAutoSize(true);
        $this->ws->getColumnDimension("B")->setAutoSize(true);
        $this->ws->getColumnDimension("C")->setAutoSize(true);
        $this->ws->getColumnDimension("D")->setAutoSize(true);
        $this->ws->getColumnDimension("E")->setAutoSize(true);
        $this->ws->getColumnDimension("F")->setAutoSize(true);
        $this->ws->getColumnDimension("G")->setAutoSize(true);
        $this->ws->getColumnDimension("H")->setAutoSize(true);
    }

    protected function printFlightSummary(Flight $flight, $flightIndex) {
        $this->ws->getStyle($this->ws->getRelativeRange(8, 1))->applyFromArray(XLSXStyleFactory::flightRow());

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

        $this->ws->getStyle($this->ws->getRelativeRange(8, 1))->applyFromArray(XLSXStyleFactory::simpleTableHeader());
        $this->ws->getStyle($this->ws->getRelativeRange(8, 7))->applyFromArray([
            "fill" => [
                'fillType'   => Fill::FILL_SOLID,
                'startColor' => [
                    'argb' => "FFFFFFFF",
                ],
            ]
        ]);

        $this->ws->printRow([
            __("contract.table-networks"),
            __("contract.table-properties"),
            in_array("faces", $this->columns, true) ? __("contract.table-faces") : "",
            in_array("traffic", $this->columns, true) ? __("contract.table-traffic") : "",
            in_array("impressions", $this->columns, true) ? __("contract.table-impressions") : "",
            in_array("media-value", $this->columns, true) ? __("contract.table-media-value") : "",
            in_array("price", $this->columns, true) ? __("contract.table-net-investment") : "",
            in_array("weeks", $this->columns, true) ? __("contract.table-weeks") : "",
        ]);

        $networks = $flight->selection->groupBy("property.network.id");

        /** @var Collection $properties */
        foreach ($networks as $properties) {
            $this->ws->setRelativeCellFormat("#,##0_-", 1, 0);
            $this->ws->setRelativeCellFormat("#,##0_-", 2, 0);
            $this->ws->setRelativeCellFormat("#,##0_-", 3, 0);
            $this->ws->setRelativeCellFormat("#,##0_-", 4, 0);
            $this->ws->setRelativeCellFormat(NumberFormat::FORMAT_CURRENCY_USD, 5, 0);
            $this->ws->setRelativeCellFormat(NumberFormat::FORMAT_CURRENCY_USD, 6, 0);

            $this->ws->printRow([
                $properties[0]['property']['network']['name'],
                count($properties),
                in_array("faces", $this->columns, true) ? $properties->sum("facesCount") : "",
                in_array("traffic", $this->columns, true) ? $properties->sum("traffic") : "",
                in_array("impressions", $this->columns, true) ? $properties->sum("impressions") : "",
                in_array("media-value", $this->columns, true) ? $properties->sum("mediaValue") : "",
                in_array("price", $this->columns, true) ? $properties->sum("price") : "",
                in_array("weeks", $this->columns, true) ? $flight->length : "",
            ]);
        }

        $this->ws->getStyle($this->ws->getRelativeRange(10, 1))->applyFromArray(XLSXStyleFactory::simpleTableTotals());

        $flightValues = [
            "propertiesCount" => count($flight->selection),
            "faces"           => $flight->selection->sum("facesCount"),
            "traffic"         => $flight->selection->sum("traffic"),
            "impressions"     => $flight->selection->sum("impressions"),
            "mediaValue"      => $flight->selection->sum("mediaValue"),
            "price"           => $flight->selection->sum("price"),
        ];

        $this->ws->setRelativeCellFormat(NumberFormat::FORMAT_CURRENCY_USD, 4, 0);
        $this->ws->setRelativeCellFormat(NumberFormat::FORMAT_CURRENCY_USD, 5, 0);

        $this->ws->printRow([
            "Total",
            $flightValues["propertiesCount"],
            in_array("faces", $this->columns, true) ? $flightValues["faces"] : "",
            in_array("traffic", $this->columns, true) ? $flightValues["traffic"] : "",
            in_array("impressions", $this->columns, true) ? $flightValues["impressions"] : "",
            in_array("media-value", $this->columns, true) ? $flightValues["mediaValue"] : "",
            in_array("price", $this->columns, true) ? $flightValues["price"] : "",
            in_array("weeks", $this->columns, true) ? $flight->length : "",
        ]);

        $this->ws->moveCursor(0, 2);

        return $flightValues;
    }

    public function printFlightHeader(Flight $flight, int $flightIndex, int $width = 8) {
        $this->ws->getStyle($this->ws->getRelativeRange($width, 1))->applyFromArray(XLSXStyleFactory::flightRow());

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

        $this->printFlightHeader($flight, $flightIndex, width: 9);

        $networks = $flight->selection->groupBy("property.network.id");

        foreach ($networks as $networkProperties) {
            // Property / Products table header
            $this->ws->getStyle($this->ws->getRelativeRange(9, 1))->applyFromArray(XLSXStyleFactory::simpleTableHeader());
            $this->ws->getStyle($this->ws->getRelativeRange(9, 1))->applyFromArray([
                "font" => [
                    'size'  => "14",
                    "color" => [
                        "argb" => "FF" . $networkProperties->first()["property"]["network"]["color"]
                    ]
                ],
                "fill" => [
                    'fillType'   => Fill::FILL_SOLID,
                    'startColor' => [
                        'argb' => "FFFFFFFF",
                    ],
                ]
            ]);

            $this->ws->printRow([
                $networkProperties->first()["property"]["network"]["name"],
                in_array("zipcode", $this->columns, true) ? __("contract.table-zipcode") : "",
                in_array("location", $this->columns, true) ? __("contract.table-location") : "",
                in_array("faces", $this->columns, true) ? __("contract.table-faces") : "",
                in_array("spots", $this->columns, true) ? __("contract.table-spots") : "",
                in_array("traffic", $this->columns, true) ? __("contract.table-traffic") : "",
                in_array("impressions", $this->columns, true) ? __("contract.table-impressions") : "",
                in_array("media-value", $this->columns, true) ? __("contract.table-media-value") : "",
                in_array("price", $this->columns, true) ? __("contract.table-net-investment") : "",
            ]);

            $networkProperties = collect($networkProperties)->sortBy("property.name");

            foreach ($networkProperties as $property) {
                $this->ws->getStyle($this->ws->getRelativeRange(9, 1))->applyFromArray([
                    "font" => [
                        "size" => 12,
                        'bold' => true,
                    ],
                    "fill" => [
                        'fillType'   => Fill::FILL_SOLID,
                        'startColor' => [
                            'argb' => "FFFFFFFF",
                        ],
                    ]
                ]);

                $this->ws->setRelativeCellFormat("#,##0_-", 3, 0);
                $this->ws->setRelativeCellFormat("#,##0_-", 4, 0);
                $this->ws->setRelativeCellFormat("#,##0_-", 5, 0);
                $this->ws->setRelativeCellFormat("#,##0_-", 6, 0);
                $this->ws->setRelativeCellFormat(NumberFormat::FORMAT_CURRENCY_USD, 7, 0);
                $this->ws->setRelativeCellFormat(NumberFormat::FORMAT_CURRENCY_USD, 8, 0);

                $this->ws->printRow([
                    $property["property"]["name"],
                    in_array("zipcode", $this->columns, true) ? substr($property["property"]["address"]["zipcode"], 0, 3) . " " . substr($property["property"]["address"]["zipcode"], 3) : "",
                    in_array("location", $this->columns, true) ? $property["property"]["address"]["city"]["name"] : "",
                    in_array("faces", $this->columns, true) ? $property["facesCount"] : "",
                    "",
                    in_array("traffic", $this->columns, true) ? $property["traffic"] : "",
                    in_array("impressions", $this->columns, true) ? $property["impressions"] : "",
                    in_array("media-value", $this->columns, true) ? $property["mediaValue"] : "",
                    in_array("price", $this->columns, true) ? $property["price"] : "",
                ]);

                $productsCount = count($property["products"]);

                $this->ws->getStyle($this->ws->getRelativeRange(9, $productsCount))->applyFromArray([
                    "font" => [
                        "size" => 11
                    ],
                    "fill" => [
                        'fillType'   => Fill::FILL_SOLID,
                        'startColor' => [
                            'argb' => "FFFFFFFF",
                        ],
                    ]
                ]);

                $this->ws->getStyle($this->ws->getRelativeRange(1, $productsCount))->applyFromArray([
                    'alignment' => [
                        "indent" => 4
                    ]
                ]);

                $products = collect($property["products"])->sortBy("name");

                foreach ($products as $product) {
                    $this->ws->setRelativeCellFormat("#,##0_-", 3, 0);
                    $this->ws->setRelativeCellFormat("#,##0_-", 4, 0);
                    $this->ws->setRelativeCellFormat("#,##0_-", 5, 0);
                    $this->ws->setRelativeCellFormat("#,##0_-", 6, 0);
                    $this->ws->setRelativeCellFormat(NumberFormat::FORMAT_CURRENCY_USD, 7, 0);
                    $this->ws->setRelativeCellFormat(NumberFormat::FORMAT_CURRENCY_USD, 8, 0);

                    $this->ws->printRow([
                        $product["name_" . App::getLocale()],
                        "",
                        "",
                        in_array("faces", $this->columns, true) ? $product["quantity"] : "",
                        in_array("spots", $this->columns, true) ? $product["spotsCount"] : "",
                        "",
                        in_array("impressions", $this->columns, true) ? $product["impressions"] : "",
                        in_array("media-value", $this->columns, true) ? $product["mediaValue"] : "",
                        in_array("price", $this->columns, true) ? $product["price"] : "",
                    ]);
                }
            }
            $this->ws->getStyle($this->ws->getRelativeRange(9, 2))->applyFromArray([
                "fill" => [
                    'fillType'   => Fill::FILL_SOLID,
                    'startColor' => [
                        'argb' => "FFFFFFFF",
                    ],
                ]
            ]);

            $this->ws->moveCursor(0, 2);
        }

        $this->ws->getColumnDimension("A")->setAutoSize(true);
        $this->ws->getColumnDimension("B")->setAutoSize(true);
        $this->ws->getColumnDimension("C")->setAutoSize(true);
        $this->ws->getColumnDimension("D")->setAutoSize(true);
        $this->ws->getColumnDimension("E")->setAutoSize(true);
        $this->ws->getColumnDimension("F")->setAutoSize(true);
        $this->ws->getColumnDimension("G")->setAutoSize(true);
        $this->ws->getColumnDimension("H")->setAutoSize(true);
        $this->ws->getColumnDimension("I")->setAutoSize(true);
    }

    /**
     * @inheritDoc
     */
    public function getName(): string {
        return $this->contractReference ?? 'planner-export';
    }
}
