<?php
/*
 * Copyright 2023 (c) Neo-OOH - All Rights Reserved
 * Unauthorized copying of this file, via any medium is strictly prohibited
 * Proprietary and confidential
 * Written by Valentin Dufois <vdufois@neo-ooh.com>
 *
 * @neo/api - EnvironicsDefaultParser.php
 */

namespace Neo\Jobs\Demographics\FilesParsers;

use PhpOffice\PhpSpreadsheet\IOFactory;

class EnvironicsDefaultParser {
    public function __construct(protected string $filePath) {

    }

    public function getEntries() {
        $reader = IOFactory::createReaderForFile($this->filePath);
        $reader->setReadDataOnly(true);
        $workbook = $reader->load($this->filePath);

        // We want to make sure we are on the first page
        $workbook->setActiveSheetIndex(0);
        $spreadsheet = $workbook->getActiveSheet();
        $data        = $spreadsheet->toArray();

        // Environics data files first datum is on line 6. Data  lines can be differentiated from section lines as they have an integer column on the F column.

        $entries = [];
        /** @var array $row */
        foreach ($data as $i => $row) {
            if ($i < 5) {
                continue;
            }

            // Is this a value row ?
            if (!is_numeric($row[5])) {
                continue;
            }

            $entries[] = [
                "id"              => trim($row[0]),
                "label"           => trim($row[1]),
                "value"           => (float)trim($row[2]),
                "reference_value" => (float)trim($row[4]),
            ];
        }

        return $entries;
    }
}
