<?php

namespace Neo\Documents\Traffic;

use Illuminate\Database\Eloquent\Collection;
use Neo\Documents\XLSX\Worksheet;
use Neo\Documents\XLSX\XLSXDocument;
use Neo\Documents\XLSX\XLSXStyleFactory;
use Neo\Models\Property;

class Traffic extends XLSXDocument {

    protected int $year;

    protected Collection $properties;

    /**
     * @param array{properties: array<int>, year: int} $data
     */
    protected function ingest($data): bool {
        $this->year = $data["year"];
        $this->properties = Property::query()->whereIn("actor_id", $data["properties"])
                                             ->with(["traffic", "network", "address", "actor"])
                                             ->get()
                                             ->sortBy("actor.name");

        return true;
    }

    /**
     * @inheritDoc
     */
    public function build(): bool {
//        $this->ws->setTitle($this->year);

        // Print header
        $this->ws->getStyle($this->ws->getRelativeRange(16, 1))->applyFromArray(XLSXStyleFactory::tableHeader());
        $this->ws->getRowDimension($this->ws->getCursorRow())->setRowHeight(30);

        $this->ws->printRow([
            __("property"),
            __("city"),
            __("market"),
            __("province"),
            __("month.january"),
            __("month.february"),
            __("month.march"),
            __("month.april"),
            __("month.may"),
            __("month.june"),
            __("month.july"),
            __("month.august"),
            __("month.september"),
            __("month.october"),
            __("month.november"),
            __("month.december"),
        ]);

        /** @var Property $property */
        foreach ($this->properties as $property) {
            $this->ws->printRow([$property->actor->name,
                                 $property->address->city?->name,
                                 $property->address->city?->market?->name,
                                 $property->address->city?->province->slug,
                                 $property->getTraffic($this->year, 0),
                                 $property->getTraffic($this->year, 1),
                                 $property->getTraffic($this->year, 2),
                                 $property->getTraffic($this->year, 3),
                                 $property->getTraffic($this->year, 4),
                                 $property->getTraffic($this->year, 5),
                                 $property->getTraffic($this->year, 6),
                                 $property->getTraffic($this->year, 7),
                                 $property->getTraffic($this->year, 8),
                                 $property->getTraffic($this->year, 9),
                                 $property->getTraffic($this->year, 10),
                                 $property->getTraffic($this->year, 11),
                ]);
        }

        return true;
    }

    /**
     * @inheritDoc
     */
    public function getName(): string {
        return $this->year;
    }
}
