<?php
/*
 * Copyright 2020 (c) Neo-OOH - All Rights Reserved
 * Unauthorized copying of this file, via any medium is strictly prohibited
 * Proprietary and confidential
 * Written by Valentin Dufois <vdufois@neo-ooh.com>
 *
 * @neo/api - ProductionFees.php
 */

namespace Neo\Documents\Contract\XLSXComponents;


use Neo\Documents\Contract\Order;
use Neo\Documents\XLSX\Component;
use Neo\Documents\XLSX\Worksheet;
use Neo\Documents\XLSX\XLSXStyleFactory;
use PhpOffice\PhpSpreadsheet\Style\NumberFormat;

class ProductionFees extends Component {
    protected Order $order;

    public function __construct(Order $order) {
        $this->order = $order;
    }

    public function render(Worksheet $ws) {
        if(count($this->order->productionLines) === 0) {
            // Nothing to show
            return;
        }

        $ws->moveCursor(0, 1);

        // Print the table header
        $ws->getStyle($ws->getRelativeRange(4))->applyFromArray(XLSXStyleFactory::tableHeader());
        $ws->mergeCellsRelative(2);
        $ws->printRow([__("contract.totals-production-cost"), null, __("contract.table-quantity"), __("contract.totals-production-cost")]);

        foreach ($this->order->productionLines as $p) {
            $ws->setRelativeCellFormat(NumberFormat::FORMAT_CURRENCY_USD, 3, 0);
            $ws->mergeCellsRelative(2);
            $ws->printRow([substr($p->description, strlen("[production]")), null, $p->quantity, $p->subtotal]);
        }
    }
}
