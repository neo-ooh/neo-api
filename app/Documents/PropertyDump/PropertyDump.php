<?php
/*
 * Copyright 2020 (c) Neo-OOH - All Rights Reserved
 * Unauthorized copying of this file, via any medium is strictly prohibited
 * Proprietary and confidential
 * Written by Valentin Dufois <vdufois@neo-ooh.com>
 *
 * @neo/api - PropertyDump.php
 */

namespace Neo\Documents\PropertyDump;


use Illuminate\Support\Collection;
use Neo\Documents\XLSX\Worksheet;
use Neo\Documents\XLSX\XLSXDocument;
use Neo\Documents\XLSX\XLSXStyleFactory;
use Neo\Models\Property;

class PropertyDump extends XLSXDocument {
    protected array $columns = [
        "Venue Name",
        "Display Unit ID",
        "Player ID",
        "Name",
        "Screens",
        "Width",
        "Height",
        "Resolution",
        "Address",
        "City",
        "Province",
        "Country",
        "Postal Code",
        "Full Address",
        "Longitude",
        "Latitude",
        "Monday Open",
        "Monday Close",
        "Tuesday Open",
        "Tuesday Close",
        "Wednesday Open",
        "Wednesday Close",
        "Thursday Open",
        "Thursday Close",
        "Friday Open",
        "Friday Close",
        "Saturday Open",
        "Saturday Close",
        "Sunday Open",
        "Sunday Close",
        "Total Hours",
        "Weekly Traffic",
        "Weekly Impressions",
        "Weekly Impressions per screen",
    ];

    protected \Illuminate\Database\Eloquent\Collection $properties;

    protected Collection $displayUnitsRows;
    protected Collection $playersRows;

    public function __construct(protected array $propertiesId) {
        parent::__construct();
        $this->ingest(null);
    }

    /**
     * @inheritDoc
     */
    protected function ingest($data): bool {
        $this->properties = Property::query()
                                    ->with([
                                        "actor",
                                        "actor.own_locations",
                                        "actor.own_locations.players",
                                        "actor.own_locations.display_type",
                                        "traffic",
                                        "traffic.weekly_data",
                                        "address",
                                        "opening_hours",
                                        "products",
                                        "products.impressions_models",
                                        "products_categories.impressions_models"
                                    ])
                                    ->whereIn("actor_id", $this->propertiesId)
                                    ->get();

        $this->displayUnitsRows = collect();
        $this->playersRows      = collect();

        foreach ($this->properties as $property) {
            if (!$property) {
                return false;
            }

            [$displayUnitsRows, $playersRows] = buildPropertyRows($property);

            $this->displayUnitsRows->push(...$displayUnitsRows);
            $this->playersRows->push(...$playersRows);
        }

        return true;
    }

    /**
     * @inheritDoc
     */
    public function build(): bool {
        $this->ws->setTitle("Display Units");
        $this->ws->getStyle($this->ws->getRelativeRange(33, 1))->applyFromArray(XLSXStyleFactory::tableHeader());

        // Print our headers
        $this->ws->printRow([
            "Venue Name",
            "Display Unit ID",
            "Name",
            "Screens",
            "Width",
            "Height",
            "Resolution",
            "Address",
            "City",
            "Province",
            "Country",
            "Postal Code",
            "Full Address",
            "Longitude",
            "Latitude",
            "Monday Open",
            "Monday Close",
            "Tuesday Open",
            "Tuesday Close",
            "Wednesday Open",
            "Wednesday Close",
            "Thursday Open",
            "Thursday Close",
            "Friday Open",
            "Friday Close",
            "Saturday Open",
            "Saturday Close",
            "Sunday Open",
            "Sunday Close",
            "Total Hours",
            "Weekly Traffic",
            "Weekly Impressions",
        ]);

        // Print each display unit
        foreach ($this->displayUnitsRows as $displayUnitsDatum) {
            $this->ws->printRow($displayUnitsDatum);
        }

        // Resize columns
        foreach ($this->columns as $i => $ignored) {
            $this->ws->getColumnDimensionByColumn($i)->setAutoSize(true);
        }

        $this->worksheet = new Worksheet(null, "Players");
        $this->spreadsheet->addSheet($this->worksheet);
        $this->spreadsheet->setActiveSheetIndexByName("Players");

        $this->ws->getStyle($this->ws->getRelativeRange(34, 1))->applyFromArray(XLSXStyleFactory::tableHeader());

        // Print our headers
        $this->ws->printRow([
            "Venue Name",
            "Display Unit ID",
            "Player ID",
            "Name",
            "Screens",
            "Width",
            "Height",
            "Resolution",
            "Address",
            "City",
            "Province",
            "Country",
            "Postal Code",
            "Full Address",
            "Longitude",
            "Latitude",
            "Monday Open",
            "Monday Close",
            "Tuesday Open",
            "Tuesday Close",
            "Wednesday Open",
            "Wednesday Close",
            "Thursday Open",
            "Thursday Close",
            "Friday Open",
            "Friday Close",
            "Saturday Open",
            "Saturday Close",
            "Sunday Open",
            "Sunday Close",
            "Total Hours",
            "Weekly Traffic",
            "Weekly Impressions",
            "Weekly Impressions per screen",
        ]);

        // Print each display unit
        foreach ($this->playersRows as $playersDatum) {
            $this->ws->printRow($playersDatum);
        }

        // Resize columns
        foreach ($this->columns as $i => $ignored) {
            $this->ws->getColumnDimensionByColumn($i)->setAutoSize(true);
        }

        return true;
    }

    /**
     * @inheritDoc
     */
    public function getName(): string {
        return "Property Dump";
    }
}
