<?php

namespace Neo\Documents\PlannerExport;

use Illuminate\Support\Collection;
use Illuminate\Support\Facades\Date;
use Illuminate\Support\Facades\Lang;
use JetBrains\PhpStorm\ArrayShape;
use Neo\Documents\XLSX\Worksheet;
use Neo\Documents\XLSX\XLSXDocument;
use Neo\Documents\XLSX\XLSXStyleFactory;
use Neo\Models\Network;
use PhpOffice\PhpSpreadsheet\Exception;
use PhpOffice\PhpSpreadsheet\Style\Alignment;
use PhpOffice\PhpSpreadsheet\Style\Fill;
use PhpOffice\PhpSpreadsheet\Style\NumberFormat;
use PhpOffice\PhpSpreadsheet\Worksheet\Drawing;

class PlannerExport extends XLSXDocument {
    protected string $contractReference;
    protected Collection $flights;
    protected array $columns;

    /**
     * @param array{properties: array<int>, year: int} $data
     */
    protected function ingest($data): bool {
        $this->contractReference = $data["odoo"]["contract"] ?? "";
        $this->flights           = collect($data["flights"])->map(fn($record) => new Flight($record));
        $this->columns           = $data["columns"];

        return true;
    }

    /**
     * @inheritDoc
     * @throws Exception
     */
    public function build(): bool {
        $firstSheetName  = Lang::get("contract.summary");
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

    /**
     * @throws Exception
     */
    protected function printSummary(): void {
        $this->ws->pushPosition();

        // Set the header style
        $this->ws->getStyle($this->ws->getRelativeRange(9, 5))->applyFromArray([
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

        $this->ws->getStyle($this->ws->getRelativeRange(9, 2))->applyFromArray(XLSXStyleFactory::totals());
        $this->ws->mergeCellsRelative(1, 2);

        // Print Totals headers
        $this->ws->printRow([
            'Total',
            Lang::get("contract.table-properties"),
            in_array("faces", $this->columns, true) ? Lang::get("contract.table-faces") : "",
            in_array("traffic", $this->columns, true) ? Lang::get("contract.table-traffic") : "",
            in_array("impressions", $this->columns, true) ? Lang::get("contract.table-impressions") : "",
            in_array("media-value", $this->columns, true) ? Lang::get("contract.table-media-value") : "",
            in_array("price", $this->columns, true) ? Lang::get("contract.table-net-investment") : "",
            in_array("cpm", $this->columns, true) ? Lang::get("contract.table-cpm") : "",
        ]);

        $this->ws->setRelativeCellFormat(NumberFormat::FORMAT_CURRENCY_USD, 5);
        $this->ws->setRelativeCellFormat(NumberFormat::FORMAT_CURRENCY_USD, 6);
        $this->ws->setRelativeCellFormat(NumberFormat::FORMAT_CURRENCY_USD_SIMPLE, 7);

        $impressions = $flightsValues->sum("impressions");
        $cpm         = $impressions > 0 ? $flightsValues->sum("cpmPrice") / $impressions * 1000 : 0;

        // Print Totals values
        $this->ws->printRow([
            '',
            $flightsValues->sum("propertiesCount"),
            in_array("faces", $this->columns, true) ? $flightsValues->sum("faces") : "",
            in_array("traffic", $this->columns, true) ? $flightsValues->sum("traffic") : "",
            in_array("impressions", $this->columns, true) ? $flightsValues->sum("impressions") : "",
            in_array("media-value", $this->columns, true) ? $flightsValues->sum("mediaValue") : "",
            in_array("price", $this->columns, true) ? $flightsValues->sum("price") : "",
            in_array("cpm", $this->columns, true) ? $cpm : "",
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

    /**
     * @throws Exception
     */
    #[ArrayShape(["propertiesCount" => "int", "faces" => "int|mixed", "traffic" => "int|mixed", "impressions" => "int|mixed", "mediaValue" => "int|mixed", "price" => "int|mixed", "cpmPrice" => "int|mixed"])]
    protected function printFlightSummary(Flight $flight, $flightIndex): array {
        $this->ws->getStyle($this->ws->getRelativeRange(9))->applyFromArray(XLSXStyleFactory::flightRow());

        $this->ws->pushPosition();
        $this->ws->moveCursor(5, 0)->mergeCellsRelative(2);
        $this->ws->popPosition();

        $this->ws->printRow([
            $flight->name ?? "Flight #" . $flightIndex + 1,
            $flight->start->toDateString(),
            '→',
            $flight->end->toDateString(),
            Lang::get("common.order-type-" . $flight->type)
        ]);

        $this->ws->getStyle($this->ws->getRelativeRange(9))->applyFromArray(XLSXStyleFactory::simpleTableHeader());
        $this->ws->getStyle($this->ws->getRelativeRange(9, 7))->applyFromArray([
            "fill" => [
                'fillType'   => Fill::FILL_SOLID,
                'startColor' => [
                    'argb' => "FFFFFFFF",
                ],
            ]
        ]);

        $this->ws->printRow([
            Lang::get("contract.table-networks"),
            Lang::get("contract.table-properties"),
            in_array("faces", $this->columns, true) ? Lang::get("contract.table-faces") : "",
            in_array("traffic", $this->columns, true) ? Lang::get("contract.table-traffic") : "",
            in_array("impressions", $this->columns, true) ? Lang::get("contract.table-impressions") : "",
            in_array("media-value", $this->columns, true) ? Lang::get("contract.table-media-value") : "",
            in_array("price", $this->columns, true) ? Lang::get("contract.table-net-investment") : "",
            in_array("cpm", $this->columns, true) ? Lang::get("contract.table-cpm") : "",
            in_array("weeks", $this->columns, true) ? Lang::get("contract.table-weeks") : "",
        ]);

        if ($flight->groups->count() === 1 && $flight->groups[0]->group === null) {
            $this->printFlightSummaryByNetwork($flight);
        } else {
            $this->printFlightSummaryByGroup($flight);
        }

        $this->ws->getStyle($this->ws->getRelativeRange(11))->applyFromArray(XLSXStyleFactory::simpleTableTotals());

        $flightValues = [
            "propertiesCount" => $flight->groups->sum("properties_count"),
            "faces"           => $flight->faces,
            "traffic"         => $flight->traffic,
            "impressions"     => $flight->impressions,
            "mediaValue"      => $flight->mediaValue,
            "price"           => $flight->price,
            "cpmPrice"        => $flight->cpmPrice,
        ];

        $this->ws->setRelativeCellFormat(NumberFormat::FORMAT_CURRENCY_USD, 5);
        $this->ws->setRelativeCellFormat(NumberFormat::FORMAT_CURRENCY_USD, 6);
        $this->ws->setRelativeCellFormat(NumberFormat::FORMAT_CURRENCY_USD_SIMPLE, 7);

        $this->ws->printRow([
            "Total",
            $flightValues["propertiesCount"],
            in_array("faces", $this->columns, true) ? $flightValues["faces"] : "",
            in_array("traffic", $this->columns, true) ? $flightValues["traffic"] : "",
            in_array("impressions", $this->columns, true) ? $flightValues["impressions"] : "",
            in_array("media-value", $this->columns, true) ? $flightValues["mediaValue"] : "",
            in_array("price", $this->columns, true) ? $flightValues["price"] : "",
            in_array("cpm", $this->columns, true) ? $flight->cpm : "",
            in_array("weeks", $this->columns, true) ? $flight->length : "",
        ]);

        $this->ws->moveCursor(0, 2);

        return $flightValues;
    }

    public function printFlightSummaryByNetwork(Flight $flight) {
        $networksIds = $flight->groups[0]->properties->pluck("property.network_id")->unique();
        $networks    = Network::query()->whereIn("id", $networksIds)->orderBy("id")->get();

        /** @var Network $network */
        foreach ($networks as $network) {
            $properties = $flight->groups[0]->properties->where("property.network_id", "=", $network->getKey());

            $this->ws->setRelativeCellFormat("#,##0_-", 1);
            $this->ws->getStyle($this->ws->getRelativeRange(1))->applyFromArray([
                "font" => [
                    "color" => [
                        "argb" => "FF" . $network->color,
                    ]
                ],
            ]);
            $this->ws->setRelativeCellFormat("#,##0_-", 2);
            $this->ws->setRelativeCellFormat("#,##0_-", 3);
            $this->ws->setRelativeCellFormat("#,##0_-", 4);
            $this->ws->setRelativeCellFormat(NumberFormat::FORMAT_CURRENCY_USD, 5);
            $this->ws->setRelativeCellFormat(NumberFormat::FORMAT_CURRENCY_USD, 6);
            $this->ws->setRelativeCellFormat(NumberFormat::FORMAT_CURRENCY_USD_SIMPLE, 7);

            $impressions = $properties->sum("impressions");
            $cpmPrice    = $properties->sum("cpmPrice");
            $cpm         = $impressions > 0 ? $cpmPrice / $impressions * 1000 : 0;

            $this->ws->printRow([
                $network?->name ?? "-",
                $properties->count(),
                in_array("faces", $this->columns, true) ? $properties->sum("faces") : "",
                in_array("traffic", $this->columns, true) ? $properties->sum("traffic") : "",
                in_array("impressions", $this->columns, true) ? $impressions : "",
                in_array("media-value", $this->columns, true) ? $properties->sum("mediaValue") : "",
                in_array("price", $this->columns, true) ? $properties->sum("price") : "",
                in_array("cpm", $this->columns, true) ? $cpm : "",
                in_array("weeks", $this->columns, true) ? $flight->length : "",
            ]);
        }
    }

    public function printFlightSummaryByGroup(Flight $flight) {
        /** @var Group $group */
        foreach ($flight->groups as $group) {
            $this->ws->setRelativeCellFormat("#,##0_-", 1);
            $this->ws->getStyle($this->ws->getRelativeRange(1))->applyFromArray([
                "font" => [
                    "color" => [
                        "argb" => "FF" . $group->group?->color ?? "000000",
                    ]
                ],
            ]);
            $this->ws->setRelativeCellFormat("#,##0_-", 2);
            $this->ws->setRelativeCellFormat("#,##0_-", 3);
            $this->ws->setRelativeCellFormat("#,##0_-", 4);
            $this->ws->setRelativeCellFormat(NumberFormat::FORMAT_CURRENCY_USD, 5);
            $this->ws->setRelativeCellFormat(NumberFormat::FORMAT_CURRENCY_USD, 6);
            $this->ws->setRelativeCellFormat(NumberFormat::FORMAT_CURRENCY_USD_SIMPLE, 7);

            $this->ws->printRow([
                $group->group?->name ?? Lang::get("contract.group-remaining-properties"),
                $group->properties_count,
                in_array("faces", $this->columns, true) ? $group->faces : "",
                in_array("traffic", $this->columns, true) ? $group->traffic : "",
                in_array("impressions", $this->columns, true) ? $group->impressions : "",
                in_array("media-value", $this->columns, true) ? $group->mediaValue : "",
                in_array("price", $this->columns, true) ? $group->price : "",
                in_array("cpm", $this->columns, true) ? $group->cpm : "",
                in_array("weeks", $this->columns, true) ? $flight->length : "",
            ]);
        }
    }

    /**
     * @throws Exception
     */
    public function printFlightHeader(Flight $flight, int $flightIndex, int $width = 8): void {
        $this->ws->getStyle($this->ws->getRelativeRange($width))->applyFromArray(XLSXStyleFactory::flightRow());

        $this->ws->pushPosition();
        $this->ws->moveCursor(5, 0)->mergeCellsRelative(2);
        $this->ws->popPosition();

        $this->ws->printRow([
            $flight->name ?? "Flight #" . $flightIndex + 1,
            $flight->start->toDateString(),
            '→',
            $flight->end->toDateString(),
            Lang::get("common.order-type-" . $flight->type)
        ]);
    }

    /**
     * @throws Exception
     */
    public function printFlight(Flight $flight, int $flightIndex): void {
        $flightLabel     = "Flight #" . $flightIndex + 1;
        $this->worksheet = new Worksheet(null, $flightLabel);
        $this->spreadsheet->addSheet($this->worksheet);
        $this->spreadsheet->setActiveSheetIndexByName($flightLabel);

        $this->printFlightHeader($flight, $flightIndex, width: 10);

        /** @var Group $group */
        foreach ($flight->groups as $group) {
            $gorupsCount = $flight->groups->count();

            if ($gorupsCount !== 1 || ($gorupsCount === 1 && $group->group !== null)) {
                // Group header
                $this->ws->getStyle($this->ws->getRelativeRange(10))->applyFromArray(XLSXStyleFactory::simpleTableHeader());
                $this->ws->getStyle($this->ws->getRelativeRange(10))->applyFromArray([
                    "font" => [
                        'size'  => "14",
                        "color" => [
                            "argb" => "FF" . $group->group?->color ?? "000000",
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
                    $group->group?->name ?? Lang::get("contract.group-remaining-properties"),
                ]);
            }

            $networks = Network::query()
                               ->whereIn("id", $group->properties->pluck("property.network_id")->unique())
                               ->orderBy("id")
                               ->get();

            /** @var Network $network */
            foreach ($networks as $network) {
                $properties = $group->properties->where("property.network_id", "=", $network->getKey());

                // Network header
                $this->ws->getStyle($this->ws->getRelativeRange(10))->applyFromArray(XLSXStyleFactory::simpleTableHeader());
                $this->ws->getStyle($this->ws->getRelativeRange(10))->applyFromArray([
                    "font" => [
                        'size'  => "14",
                        "color" => [
                            "argb" => "FF" . $network->color,
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
                    $network->name,
                    in_array("zipcode", $this->columns, true) ? Lang::get("contract.table-zipcode") : "",
                    in_array("location", $this->columns, true) ? Lang::get("contract.table-location") : "",
                    in_array("faces", $this->columns, true) ? Lang::get("contract.table-faces") : "",
                    in_array("spots", $this->columns, true) ? Lang::get("contract.table-spots") : "",
                    in_array("traffic", $this->columns, true) ? Lang::get("contract.table-traffic") : "",
                    in_array("impressions", $this->columns, true) ? Lang::get("contract.table-impressions") : "",
                    in_array("media-value", $this->columns, true) ? Lang::get("contract.table-media-value") : "",
                    in_array("price", $this->columns, true) ? Lang::get("contract.table-net-investment") : "",
                    in_array("cpm-lines", $this->columns, true) ? Lang::get("contract.table-cpm") : "",
                ]);

                /** @var Property $property */
                foreach ($properties as $property) {
                    $this->ws->getStyle($this->ws->getRelativeRange(10))->applyFromArray([
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

                    $this->ws->setRelativeCellFormat("#,##0_-", 3);
                    $this->ws->setRelativeCellFormat("#,##0_-", 4);
                    $this->ws->setRelativeCellFormat("#,##0_-", 5);
                    $this->ws->setRelativeCellFormat("#,##0_-", 6);
                    $this->ws->setRelativeCellFormat(NumberFormat::FORMAT_CURRENCY_USD, 7);
                    $this->ws->setRelativeCellFormat(NumberFormat::FORMAT_CURRENCY_USD, 8);
                    $this->ws->setRelativeCellFormat(NumberFormat::FORMAT_CURRENCY_USD_SIMPLE, 9);

                    $this->ws->printRow([
                        $property->property->actor->name,
                        in_array("zipcode", $this->columns, true) ? substr($property->property->address->zipcode, 0, 3) . " " . substr($property->property->address->zipcode, 3) : "",
                        in_array("location", $this->columns, true) ? $property->property->address->city->name : "",
                        in_array("faces", $this->columns, true) ? $property->faces : "",
                        "",
                        in_array("traffic", $this->columns, true) ? $property->traffic : "",
                        in_array("impressions", $this->columns, true) ? $property->impressions : "",
                        in_array("media-value", $this->columns, true) ? $property->mediaValue : "",
                        in_array("price", $this->columns, true) ? $property->price : "",
                        in_array("cpm-lines", $this->columns, true) ? $property->cpm : "",
                    ]);

                    $categories = collect($property->categories)->sortBy("category.name_" . Lang::locale());

                    /** @var Category $category */
                    foreach ($categories as $category) {
                        $products = collect($category->products)->sortBy("product.name_" . Lang::locale());

                        /** @var Product $product */
                        foreach ($products as $product) {
                            $this->ws->getStyle($this->ws->getRelativeRange(10))->applyFromArray([
                                "font" => [
                                    "size" => 10
                                ],
                                "fill" => [
                                    'fillType'   => Fill::FILL_SOLID,
                                    'startColor' => [
                                        'argb' => "FFFFFFFF",
                                    ],
                                ]
                            ]);

                            $this->ws->getStyle($this->ws->getRelativeRange(1))->applyFromArray([
                                'alignment' => [
                                    "indent" => 8
                                ]
                            ]);

                            $this->ws->setRelativeCellFormat("#,##0_-", 3);
                            $this->ws->setRelativeCellFormat("#,##0_-", 4);
                            $this->ws->setRelativeCellFormat("#,##0_-", 5);
                            $this->ws->setRelativeCellFormat("#,##0_-", 6);
                            $this->ws->setRelativeCellFormat(NumberFormat::FORMAT_CURRENCY_USD, 7);
                            $this->ws->setRelativeCellFormat(NumberFormat::FORMAT_CURRENCY_USD, 8);
                            $this->ws->setRelativeCellFormat(NumberFormat::FORMAT_CURRENCY_USD_SIMPLE, 9);

                            $this->ws->printRow([
                                $product->product["name_" . Lang::locale()],
                                "",
                                "",
                                in_array("faces", $this->columns, true) ? $product->faces : "",
                                in_array("spots", $this->columns, true) ? $product->spots : "",
                                "",
                                in_array("impressions", $this->columns, true) ? $product->impressions : "",
                                in_array("media-value", $this->columns, true) ? $product->mediaValue : "",
                                in_array("price", $this->columns, true) ? $product->price : "",
                                in_array("cpm-lines", $this->columns, true) ? $product->cpm : "",
                            ]);
                        }
                    }
                }

                $this->ws->getStyle($this->ws->getRelativeRange(10, 2))->applyFromArray([
                    "fill" => [
                        'fillType'   => Fill::FILL_SOLID,
                        'startColor' => [
                            'argb' => "FFFFFFFF",
                        ],
                    ]
                ]);

                $this->ws->moveCursor(0, 2);

            }

            $this->ws->getStyle($this->ws->getRelativeRange(10, 2))->applyFromArray([
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
