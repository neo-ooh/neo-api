<?php


namespace Neo\Documents\XLSX;


use Neo\Documents\Document;
use PhpOffice\PhpSpreadsheet\Cell\AdvancedValueBinder;
use PhpOffice\PhpSpreadsheet\Cell\Cell;
use PhpOffice\PhpSpreadsheet\Exception;
use PhpOffice\PhpSpreadsheet\Spreadsheet;
use PhpOffice\PhpSpreadsheet\Writer\Xlsx;
use Spatie\TemporaryDirectory\TemporaryDirectory;

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
    public function __construct() {
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
    public function format(): string {
        return "application/vnd.openxmlformats-officedocument.spreadsheetml.sheet";
    }

    /**
     * @inheritDoc
     * @throws \PhpOffice\PhpSpreadsheet\Writer\Exception|Exception
     */
    public function output() {
        $writer = new Xlsx($this->spreadsheet);
        $writer->setPreCalculateFormulas(false);

        $tempDir = (new TemporaryDirectory())->create();
        $tempFile = $tempDir->path('temp.xlsx');
        $writer->save($tempFile);
        return file_get_contents($tempFile);
    }
}
