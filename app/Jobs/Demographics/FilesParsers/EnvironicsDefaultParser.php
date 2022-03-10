<?php

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

        // for this kind of file, value rows are identifyiable by the variable id on the first column, that always start with ECY

        $entries = [];
        /** @var array $row */
        foreach ($data as $row) {
            // Is this a value row ?
            if (!str_starts_with(trim($row[0]), "ECY")) {
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
