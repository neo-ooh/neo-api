<?php

namespace Neo\Documents\PlannerExport;

use Illuminate\Support\Collection;
use Illuminate\Support\Facades\Date;
use Neo\Documents\XLSX\XLSXDocument;
use Neo\Documents\XLSX\XLSXStyleFactory;
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

        // Add the Neo logo
        $drawing = new Drawing();
        $drawing->setName('Neo-OOH');
        $drawing->setDescription('Neo Out of Home');
        $drawing->setPath(resource_path("logos/main.dark.en@2x.png"));
        $drawing->setHeight(60);
        $drawing->setWorksheet($this->ws);
        $drawing->setCoordinates('F2');

        // Date
        $this->ws->printRow(["Date", Date::now()->toFormattedDateString()]);

        $this->ws->popPosition();
        $this->ws->moveCursor(0, 5);

        foreach ($this->flights as $flightIndex => $flight) {
            $this->printFlightRow($flight, $flightIndex);
        }
    }

    protected function printFlightRow(Flight $flight, $flightIndex) {
        $this->ws->getStyle($this->ws->getRelativeRange(16, 1))->applyFromArray(XLSXStyleFactory::tableHeader());
        $this->ws->printRow(["Flight #$flightIndex", $flight->startDate->toDateString(), $flight->endDate->toDateString(), __("common.order-type-".$flight->type)]);
    }

    /**
     * @inheritDoc
     */
    public function getName(): string {
        return $this->contractReference ?? 'planner-export';
    }
}
