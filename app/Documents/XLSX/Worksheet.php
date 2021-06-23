<?php


namespace Neo\Documents\XLSX;


use PhpOffice\PhpSpreadsheet\Cell\Cell;
use PhpOffice\PhpSpreadsheet\Cell\Coordinate;
use PhpOffice\PhpSpreadsheet\Exception;
use PhpOffice\PhpSpreadsheet\Style\NumberFormat;

/**
 * Class Worksheet
 *
 * This subclass of PHPSreadsheet Worksheet holds an internal pointer
 * that allow from writing consecutive data without specifying a precise location.
 *
 *  The internal pointer starts on the cell 'A1'
 */
class Worksheet extends \PhpOffice\PhpSpreadsheet\Worksheet\Worksheet {
    protected string $currentCol = "A";
    protected int $currentRow = 1;

    protected array $positionStack = [];

    public function pushPosition() {
        $this->positionStack[] = [$this->currentCol, $this->currentRow];
    }

    public function popPosition() {
        if(count($this->positionStack) === 0) {
            // Do nothing
            return;
        }

        [$this->currentCol, $this->currentRow] = array_pop($this->positionStack);
    }

    public function getCurrentCell(): Cell {
        return $this->getCell($this->getCursorPosition());
    }

    /**
     * @return string The cell coordinate of the pointer current position
     */
    public function getCursorPosition(): string {
        return $this->currentCol . $this->currentRow;
    }

    public function getCursorCol(): string {
        return $this->currentCol;
    }

    public function getRelativeRange($width = 1, $height = 1) {
        $start = $this->getCursorPosition();
        $this->pushPosition();

        $this->moveCursor($width - 1, $height - 1);
        $end = $this->getCursorPosition();

        $this->popPosition();
        return $start. ":" . $end;
    }

    /**
     * @throws Exception
     */
    public function getCursorColIndex(): int {
        return Coordinate::columnIndexFromString($this->currentCol);
    }

    public function getCursorRow(): int {
        return $this->currentRow;
    }

    /**
     * Change the cursor position to the given one.
     *
     * @param string $col New column Index as letter
     * @param int    $row New row index
     * @return $this
     */
    public function setCursorTo(string $col, int $row): Worksheet {
        $this->currentCol = $col;
        $this->currentRow = $row;

        return $this;
    }

    /**
     * Mov e the cursor by the specified offsets. Accepts negative values
     *
     * @param int $dcol How many columns to the right the cursor should be moved.
     * @param int $drow How many rows downward the cursor should be moved.
     * @return $this
     * @throws Exception
     */
    public function moveCursor(int $dcol, int $drow): Worksheet {
        $this->currentCol = Coordinate::stringFromColumnIndex(Coordinate::columnIndexFromString($this->currentCol) + $dcol);
        $this->currentRow += $drow;

        return $this;
    }

    /**
     * Print the given 2D array (row[column[]])at the current cursor position.
     * The cursor will be moved downward by the amount of row entered. The cursor column will remain unchanged.
     *
     * @param array $source
     * @param null  $nullValue
     * @param null  $startCell
     * @param bool  $strictNullComparison
     * @return $this
     * @throws Exception
     */
    public function fromArray(array $source, $nullValue = null, $startCell = null, $strictNullComparison = true): Worksheet {

        $start = $startCell ?: $this->getCursorPosition();

        parent::fromArray($source, $nullValue, $start, $strictNullComparison);

        // Move our cursor downward by the number of lines that were entered.
        return $this->moveCursor(0, count($source));
    }

    public function printRow(array $row) {
        if (!is_array($row)) {
            $row = func_get_args();
        }

        $this->fromArray([$row]);
    }

    /**
     * Merge multiple cells together starting from the cursor position
     *
     * @param int $width  How many cols the merged cell should span
     * @param int $height How many rows th merged cell should span
     * @return $this
     * @throws Exception
     */
    public function mergeCellsRelative(int $width = 1, int $height = 1) {
        $this->mergeCellsByColumnAndRow(
            $this->getCursorColIndex(),
            $this->getCursorRow(),
            $this->getCursorColIndex() + $width - 1,
            $this->getCursorRow() + $height - 1);

        // We do not move our cursor as we want to keep it on the merge cells so that the user can fill it

        return $this;
    }

    public function setRelativeCellFormat(string $formatCode, $dcol = 0, $drow = 0) {
        $this->getStyleByColumnAndRow($this->getCursorColIndex() + $dcol, $this->getCursorRow() + $drow)
           ->getNumberFormat()
           ->setFormatCode($formatCode);
    }
}
