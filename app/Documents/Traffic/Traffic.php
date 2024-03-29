<?php
/*
 * Copyright 2023 (c) Neo-OOH - All Rights Reserved
 * Unauthorized copying of this file, via any medium is strictly prohibited
 * Proprietary and confidential
 * Written by Valentin Dufois <vdufois@neo-ooh.com>
 *
 * @neo/api - Traffic.php
 */

namespace Neo\Documents\Traffic;

use Illuminate\Database\Eloquent\Collection;
use Illuminate\Support\Facades\App;
use Neo\Documents\XLSX\XLSXDocument;
use Neo\Documents\XLSX\XLSXStyleFactory;
use Neo\Modules\Properties\Models\Property;

class Traffic extends XLSXDocument {

	protected int $year;

	protected Collection $properties;

	/**
	 * @param array{properties: array<int>, year: int} $data
	 */
	protected function ingest($data): bool {
		$this->year       = $data["year"];
		$this->properties = Property::query()->whereIn("actor_id", $data["properties"])
		                            ->with(["traffic", "network", "address.city", "actor"])
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
			                    __("common.property"),
			                    __("common.city"),
			                    __("common.market"),
			                    __("common.province"),
			                    __("common.month-january"),
			                    __("common.month-february"),
			                    __("common.month-march"),
			                    __("common.month-april"),
			                    __("common.month-may"),
			                    __("common.month-june"),
			                    __("common.month-july"),
			                    __("common.month-august"),
			                    __("common.month-september"),
			                    __("common.month-october"),
			                    __("common.month-november"),
			                    __("common.month-december"),
		                    ]);

		/** @var Property $property */
		foreach ($this->properties as $property) {
			$this->ws->setRelativeCellFormat('#,##0', 4, 0);
			$this->ws->setRelativeCellFormat('#,##0', 5, 0);
			$this->ws->setRelativeCellFormat('#,##0', 6, 0);
			$this->ws->setRelativeCellFormat('#,##0', 7, 0);
			$this->ws->setRelativeCellFormat('#,##0', 8, 0);
			$this->ws->setRelativeCellFormat('#,##0', 9, 0);
			$this->ws->setRelativeCellFormat('#,##0', 10, 0);
			$this->ws->setRelativeCellFormat('#,##0', 11, 0);
			$this->ws->setRelativeCellFormat('#,##0', 12, 0);
			$this->ws->setRelativeCellFormat('#,##0', 13, 0);
			$this->ws->setRelativeCellFormat('#,##0.', 14, 0);
			$this->ws->setRelativeCellFormat('#,##0', 15, 0);

			$this->ws->printRow([$property->actor->name,
			                     $property->address?->city?->name,
			                     $property->address?->city?->market?->{"name_" . App::getLocale()},
			                     $property->address?->city?->province->slug,
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


		// Autosize columns
		for ($i = 0; $i <= 16; ++$i) {
			$this->ws->getColumnDimensionByColumn($i)->setAutoSize(true);
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
