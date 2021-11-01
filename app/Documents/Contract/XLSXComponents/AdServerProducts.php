<?php

namespace Neo\Documents\Contract\XLSXComponents;

use Illuminate\Support\Collection;
use Neo\Documents\Contract\Order;
use Neo\Documents\Contract\OrderLine;
use Neo\Documents\XLSX\Worksheet;
use Neo\Documents\XLSX\XLSXStyleFactory;
use PhpOffice\PhpSpreadsheet\Style\NumberFormat;

class AdServerProducts {
    public function __construct(protected Collection $lines) {}

    public function render(Worksheet $ws) {
        $ws->moveCursor(0, 2);
        $ws->mergeCellsRelative(16);
        $ws->getCurrentCell()->setValue(__("contract.adserver-products"));

        // Stylize the cell
        $ws->getStyle($ws->getCursorPosition())->applyFromArray(XLSXStyleFactory::networkSectionHeader("dark-blue"));
        $ws->getRowDimension($ws->getCursorRow())->setRowHeight(50);

        // Print the table headers
        $ws->moveCursor(0, 2);

        $ws->getStyle($ws->getRelativeRange(16, 1))->applyFromArray(XLSXStyleFactory::tableHeader());
        $ws->getRowDimension($ws->getCursorRow())->setRowHeight(30);

        $ws->printRow([
            __("contract.table-markets"),
            __("contract.table-properties"),
            __("contract.table-networks"),
            "",
            __("contract.table-product"),
            "",
            "",
            "",
            "",
            "",
            "",
            __("contract.table-media-value"),
            __("contract.table-net-investment"),
            __("contract.table-impressions"),
            "",
            __("contract.table-cpm")
        ]);

        /** @var OrderLine $line */
        foreach($this->lines as $line) {
            $ws->setRelativeCellFormat(NumberFormat::FORMAT_CURRENCY_USD, 7, 0);
            $ws->setRelativeCellFormat(NumberFormat::FORMAT_NUMBER_00, 9, 0);
            $ws->setRelativeCellFormat(NumberFormat::FORMAT_CURRENCY_USD, 11, 0);
            $ws->setRelativeCellFormat(XLSXStyleFactory::FORMAT_CURRENCY_TWO_PLACES, 12, 0);
            $ws->setRelativeCellFormat(XLSXStyleFactory::FORMAT_CURRENCY_TWO_PLACES, 15, 0);

            $ws->printRow([
                $line->market_name,
                $line->property_name,
                $line->network,
                "",
                "",
                "",
                "",
                "",
                "",
                "",
                "",
                $line->unit_price * $line->nb_weeks,
                $line->subtotal,
                $line->impressions,
                "",
                $line->cpm,
            ]);
        }
    }
}
