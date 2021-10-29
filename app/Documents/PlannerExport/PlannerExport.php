<?php

namespace Neo\Documents\PlannerExport;

use App;
use Illuminate\Database\Eloquent\Collection;
use Illuminate\Support\Facades\Date;
use Neo\Documents\XLSX\Worksheet;
use Neo\Documents\XLSX\XLSXDocument;
use Neo\Documents\XLSX\XLSXStyleFactory;
use Neo\Models\Property;
use PhpOffice\PhpSpreadsheet\Worksheet\Drawing;

class PlannerExport extends XLSXDocument {
    protected string $contractReference;

    /**
     * @param array{properties: array<int>, year: int} $data
     */
    protected function ingest($data): bool {
        $this->contractReference = $data['contract'] ?? "";
        $this->flights           = collect($data['selection']);

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
        $this->ws->moveCursor(0, 2);
    }

    /**
     * @inheritDoc
     */
    public function getName(): string {
        return $this->contractReference ? 'planner-export';
    }
}
