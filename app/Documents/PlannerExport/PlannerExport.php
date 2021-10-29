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
            $this->printFlightSummary($flight, $flightIndex);
        }
    }

    protected function printFlightSummary(Flight $flight, $flightIndex) {
        $this->ws->getStyle($this->ws->getRelativeRange(16, 1))->applyFromArray(XLSXStyleFactory::flightRow());
        $this->ws->printRow([
            "Flight #" . $flightIndex + 1,
            $flight->startDate->toDateString(),
            '→',
            $flight->endDate->toDateString(),
            '',
            __("common.order-type-".$flight->type)
        ]);

        $this->ws->printRow([
            __("contract.table-networks"),
            __("contract.table-properties"),
            __("contract.table-faces"),
            __("contract.table-traffic"),
            __("contract.table-media-value"),
            __("contract.table-net-investment"),
            __("contract.table-net-weeks"),
        ]);

        $networks = $flight->selection->groupBy("property.network.id")->sortBy("0.property.network.name");
        /** @var Collection $properties */
        foreach($networks as $properties) {
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

        $this->ws->moveCursor(0, 2);
    }

    /**
     * @inheritDoc
     */
    public function getName(): string {
        return $this->contractReference ?? 'planner-export';
    }
}
