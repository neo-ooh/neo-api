<?php


namespace Neo\Documents\Contract\XLSXComponents;


use Neo\Documents\Contract\Order;
use Neo\Documents\XLSX\Component;
use Neo\Documents\XLSX\Worksheet;
use Neo\Documents\XLSX\XLSXStyleFactory;

class TechnicalSpecs extends Component {
    protected Order $order;

    public function __construct(Order $order) {
        $this->order = $order;
    }

    public function render(Worksheet $ws) {
        $ws->moveCursor(0, 1);
        $ws->pushPosition();

        // Start by printing the disclaimers
        $ws->mergeCellsRelative(5);
        $ws->getCurrentCell()->setValue(strip_tags(__("contract.summary-notice-1")));
        $ws->moveCursor(0, 1);
        $ws->mergeCellsRelative(5);
        $ws->getCurrentCell()->setValue(strip_tags(__("contract.summary-notice-2")));
        $ws->moveCursor(0, 1);
        $ws->mergeCellsRelative(5);
        $ws->getCurrentCell()->setValue(strip_tags(__("contract.summary-notice-3")));
        $ws->moveCursor(0, 1);
        $ws->mergeCellsRelative(5);
        $ws->getCurrentCell()->setValue(strip_tags(__("contract.summary-notice-4")));
        $ws->moveCursor(0, 1);
         $ws->mergeCellsRelative(5);
        $ws->getCurrentCell()->setValue(strip_tags(__("contract.summary-notice-5")));

        // Print the technical specs table
        if($this->order->getShoppingOrders()->count() > 0) {
            // Shopping
            $ws->moveCursor(0, 2);
            $ws->mergeCellsRelative(2);
            $ws->getStyle($ws->getCursorPosition())->applyFromArray(XLSXStyleFactory::specsHeader());
            $ws->getCurrentCell()->setValue(__("contract.technical-specs"));
            $ws->moveCursor(2, 0);
            $ws->getStyle($ws->getCursorPosition())->applyFromArray(XLSXStyleFactory::specsHeader("shopping"));
            $ws->getCurrentCell()->setValue(__("common.network-shopping"));
            $ws->mergeCellsRelative(2);

            $ws->moveCursor(-2, 1);
            // header
            $ws->getStyle($ws->getRelativeRange(5))->applyFromArray(XLSXStyleFactory::tableHeader());
            $ws->mergeCellsRelative(2);
            $ws->printRow([__("contract.table-product"),
                            null,
                            __("contract.table-spot-duration"),
                            __("contract.table-loop-duration"),
                            __("contract.table-loop-holiday")]);
            $ws->mergeCellsRelative(2);
            $ws->printRow(["Digital - Horizontal", null,  "15 sec.", "5 min.", "7.5 min."]);
            $ws->mergeCellsRelative(2);
            $ws->printRow(["Digital - Vertical", null,  "10 sec.", "70 sec.", "100 sec."]);
            $ws->mergeCellsRelative(2);
            $ws->printRow (["Digital - Spectacular", null,  "15 sec.", "5 min.", "7.5 min."]);
        }


        if($this->order->getOTGOrders()->count() > 0) {
            // On-The-Go
            $ws->moveCursor(0, 1);
            $ws->mergeCellsRelative(2);
            $ws->getStyle($ws->getCursorPosition())->applyFromArray(XLSXStyleFactory::specsHeader());
            $ws->getCurrentCell()->setValue(__("contract.technical-specs"));
            $ws->moveCursor(2, 0);
            $ws->getStyle($ws->getCursorPosition())->applyFromArray(XLSXStyleFactory::specsHeader("otg"));
            $ws->getCurrentCell()->setValue(__("common.network-otg"));
            $ws->mergeCellsRelative(2);

            $ws->moveCursor(-2, 1);
            // header
            $ws->getStyle($ws->getRelativeRange(5))->applyFromArray(XLSXStyleFactory::tableHeader());
            $ws->mergeCellsRelative(2);
            $ws->printRow([__("contract.table-product"),
                           null,
                           __("contract.table-spot-duration"),
                           __("contract.table-loop-duration"),
                           __("contract.table-loop-holiday")]);
            $ws->mergeCellsRelative(2);
            $ws->printRow(["Indoor - Digital Horizontal", null, "15 sec.", "4 min.", "4 min."]);
            $ws->mergeCellsRelative(2);
            $ws->printRow(["Outdoor - Digital In Screen", null, "15 sec.", "4 min.", "4 min."]);
            $ws->mergeCellsRelative(2);
            $ws->printRow(["Outdoor - Digital Full Horizontal", null, "15 sec.", "4 min.", "4 min."]);
        }


        if($this->order->getFitnessOrders()->count() > 0) {
            // Fitness
            $ws->moveCursor(0, 1);
            $ws->mergeCellsRelative(2);
            $ws->getStyle($ws->getCursorPosition())->applyFromArray(XLSXStyleFactory::specsHeader());
            $ws->getCurrentCell()->setValue(__("contract.technical-specs"));
            $ws->moveCursor(2, 0);
            $ws->getStyle($ws->getCursorPosition())->applyFromArray(XLSXStyleFactory::specsHeader("otg"));
            $ws->getCurrentCell()->setValue(__("common.network-fitness"));
            $ws->mergeCellsRelative(2);

            $ws->moveCursor(-2, 1);
            // header
            $ws->getStyle($ws->getRelativeRange(5))->applyFromArray(XLSXStyleFactory::tableHeader());
            $ws->mergeCellsRelative(2);
            $ws->printRow([__("contract.table-product"),
                           null,
                           __("contract.table-spot-duration"),
                           __("contract.table-loop-duration"),
                           __("contract.table-loop-holiday")]);
            $ws->mergeCellsRelative(2);
            $ws->printRow(["Digital - Horizontal", null, "15 sec.", "10 min.", "10 min."]);
            $ws->mergeCellsRelative(2);
            $ws->printRow(["Digital Vertical", null, "10 sec.", "70 sec.", "70 sec."]);
        }

        $ws->popPosition();
        $ws->moveCursor(9, 0);
    }
}
