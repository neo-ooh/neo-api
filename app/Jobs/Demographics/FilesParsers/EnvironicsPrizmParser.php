<?php

namespace Neo\Jobs\Demographics\FilesParsers;

use PhpOffice\PhpSpreadsheet\IOFactory;

class EnvironicsPrizmParser {
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

        // for the prizm file, we only have a variable number, on the third column

        $entries = [];
        /** @var array $row */
        foreach ($data as $row) {
            // Is this a value row ?
            if (!is_numeric(trim($row[2]))) {
                continue;
            }

            $entries[] = [
                "id"              => "PZML" . trim($row[2]),
                "label"           => trim($row[3]),
                "value"           => (float)trim($row[5]),
                "reference_value" => (float)trim($row[7]),
            ];
        }

        return $entries;
    }
}
