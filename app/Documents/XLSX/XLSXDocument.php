<?php
/*
 * Copyright 2023 (c) Neo-OOH - All Rights Reserved
 * Unauthorized copying of this file, via any medium is strictly prohibited
 * Proprietary and confidential
 * Written by Valentin Dufois <vdufois@neo-ooh.com>
 *
 * @neo/api - XLSXDocument.php
 */

namespace Neo\Documents\XLSX;


use JetBrains\PhpStorm\NoReturn;
use Neo\Documents\Document;
use Neo\Documents\DocumentFormat;
use Neo\Exceptions\Documents\UnsupportedDocumentFormatException;
use PhpOffice\PhpSpreadsheet\Cell\AdvancedValueBinder;
use PhpOffice\PhpSpreadsheet\Cell\Cell;
use PhpOffice\PhpSpreadsheet\Exception;
use PhpOffice\PhpSpreadsheet\Spreadsheet;
use PhpOffice\PhpSpreadsheet\Writer\BaseWriter;
use PhpOffice\PhpSpreadsheet\Writer\Csv;
use PhpOffice\PhpSpreadsheet\Writer\Xlsx;

/**
 * Class XLSXDocument
 *
 * @package Neo\Documents\XLSX
 *
 * @property Worksheet $ws Convenient access to the current worksheet
 */
abstract class XLSXDocument extends Document {

    protected Spreadsheet $spreadsheet;
    protected Worksheet $worksheet;

    /**
     * @throws Exception
     */
    protected function __construct() {
        $this->spreadsheet = new Spreadsheet();
        $this->worksheet   = new Worksheet(null, 'Worksheet 1');
        $this->spreadsheet->addSheet($this->worksheet);
        $this->spreadsheet->removeSheetByIndex(0);

        Cell::setValueBinder(new AdvancedValueBinder());
    }

    public function __get($name) {
        if ($name === 'ws') {
            return $this->spreadsheet->getActiveSheet();
        }

        return false;
    }

    public function __set($name, $value) {
        return false;
    }

    public function __isset($name) {
        return $name === 'ws';
    }

    /**
     * @inheritDoc
     */
    public function format(): DocumentFormat {
        return DocumentFormat::XLSX;
    }

    public function customizeOutput(BaseWriter $writer) {
        if ($this->format() === DocumentFormat::XLSX) {
            $writer->setPreCalculateFormulas(false);
        }
    }

    /**
     * @inheritDoc
     * @throws \PhpOffice\PhpSpreadsheet\Writer\Exception|Exception
     * @throws UnsupportedDocumentFormatException
     */
    #[NoReturn] public function output(string|null $path = null): void {
        $writer = match ($this->format()) {
            DocumentFormat::XLSX => new Xlsx($this->spreadsheet),
            DocumentFormat::CSV  => new Csv($this->spreadsheet),
            default              => throw new UnsupportedDocumentFormatException($this->format(), [DocumentFormat::XLSX, DocumentFormat::CSV])
        };
        
        $this->customizeOutput($writer);

        header("access-control-allow-origin: *");
        header("content-type: " . $this->format()->value);

        if ($path === null) {
            $writer->save("php://output");
            exit;
        } else {
            $writer->save($path);
        }
    }
}
