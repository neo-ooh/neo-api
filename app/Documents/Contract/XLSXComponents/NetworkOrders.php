<?php

namespace Neo\Documents\Contract\XLSXComponents;

use Illuminate\Support\Collection;
use Neo\Documents\Contract\Order;
use Neo\Documents\Contract\OrderLine;
use Neo\Documents\XLSX\Component;
use Neo\Documents\XLSX\Worksheet;
use Neo\Documents\XLSX\XLSXStyleFactory;
use PhpOffice\PhpSpreadsheet\Style\Border;
use PhpOffice\PhpSpreadsheet\Style\NumberFormat;

class NetworkOrders extends Component {

    protected string $network;
    protected Order $order;

    public function __construct(string $network, Order $order) {
        $this->network = $network;
        $this->order   = $order;
    }

    public function render(Worksheet $ws) {
        $orderLines = $this->order->orderLines->filter(fn(OrderLine $line) => $line->isNetwork($this->network));

        if (count($orderLines) === 0) {
            // If there is no ordere line for this network, we don't print anything
            return;
        }

        // Start by printing our header
        $ws->moveCursor(0, 2);
        $ws->mergeCellsRelative(16);
        $ws->getCurrentCell()->setValue(strtoupper(__("common.network-" . $this->network)));

        // Stylize the cell
        $ws->getStyle($ws->getCursorPosition())->applyFromArray(XLSXStyleFactory::networkSectionHeader($this->network));
        $ws->getRowDimension($ws->getCursorRow())->setRowHeight(50);

        // Then print the guaranteed orders
        $this->printGuaranteedOrderLines($this->order->getGuaranteedOrders()
                                                     ->filter(fn(OrderLine $line) => $line->isNetwork($this->network)), $ws);

        // Then print the bua orders
        $this->printBuaOrderLinee($this->order->getBuaOrders()
                                              ->filter(fn(OrderLine $line) => $line->isNetwork($this->network)), $ws);
    }

    public function printGuaranteedOrderLines(Collection $orderLines, Worksheet $ws) {
        if (count($orderLines) === 0) {
            return null;
        }

        // Group orders by state and by market
        $states = $orderLines->groupBy(["property_state", "market_name"]);

        foreach ($states as $state => $markets) {
            // Print the table headers
            $ws->moveCursor(0, 2);

            $ws->getStyle($ws->getRelativeRange(16, 1))->applyFromArray(XLSXStyleFactory::tableHeader());
            $ws->getRowDimension($ws->getCursorRow())->setRowHeight(30);

            $ws->printRow([
                __("contract.table-markets"),
                __("contract.table-properties"),
                __("contract.table-annual-traffic"),
                __("contract.table-campaign-traffic"),
                __("contract.table-product"),
                __("contract.table-start-date"),
                __("contract.table-end-date"),
                __("contract.table-rate-per-week"),
                __("contract.table-count-of-screens-posters"),
                __("contract.table-count-of-weeks"),
                __("contract.table-spots-per-loop"),
                __("contract.table-media-value"),
                __("contract.table-net-investment"),
                __("contract.table-impressions"),
                __("contract.table-impressions-covid"),
                __("contract.table-cpm")
            ]);

            $ws->mergeCellsRelative(2, 1);
            $ws->moveCursor(0, 1);
            $ws->mergeCellsRelative(2, 1);

            // Stylize the cell
            $ws->getStyle($ws->getCursorPosition())->applyFromArray(XLSXStyleFactory::locationHeader());

            $ws->getCurrentCell()->setValue(substr(trim($state), 0, -5));
            $ws->getRowDimension($ws->getCursorRow())->setRowHeight(20);

            foreach ($markets as $market => $lines) {
                // Print the market name
                $ws->moveCursor(0, 2);
                $ws->mergeCellsRelative(2, 1);

                // Stylize the cell
                $ws->getStyle($ws->getCursorPosition())->applyFromArray(XLSXStyleFactory::locationHeader());

                $ws->getCurrentCell()->setValue($market);
                $ws->getRowDimension($ws->getCursorRow())->setRowHeight(20);
                $ws->moveCursor(0, 2);

                // Stylize the table
                $ws->getStyle($ws->getRelativeRange(16, count($lines)))->applyFromArray(XLSXStyleFactory::tableBody());

                $ws->pushPosition();
                $ws->moveCursor(12, 0);
                $ws->getStyle($ws->getRelativeRange(1, count($lines)))->applyFromArray([
                    "borders" => [
                        "right" => [
                            "borderStyle" => Border::BORDER_DOUBLE
                        ]
                    ]
                ]);

                $ws->popPosition();

                // Print the lines
                /** @var OrderLine $line */
                foreach ($lines as $line) {
                    // Dates
                    $ws->setRelativeCellFormat(NumberFormat::FORMAT_DATE_YYYYMMDD2, 5, 0);
                    $ws->setRelativeCellFormat(NumberFormat::FORMAT_DATE_YYYYMMDD2, 6, 0);

                    // value formating
                    $ws->setRelativeCellFormat(NumberFormat::FORMAT_CURRENCY_USD, 7, 0);
                    $ws->setRelativeCellFormat(NumberFormat::FORMAT_NUMBER_00, 9, 0);
                    $ws->setRelativeCellFormat(NumberFormat::FORMAT_CURRENCY_USD, 11, 0);
                    $ws->setRelativeCellFormat(NumberFormat::FORMAT_CURRENCY_USD, 12, 0);
                    $ws->setRelativeCellFormat('$#,##0.00', 15, 0);


                    $ws->printRow([
                        $line->property_city,
                        $line->property_name,
                        $line->property_annual_traffic,
                        $line->traffic,
                        $line->product,
                        $line->date_start,
                        $line->date_end,
                        $line->unit_price,
                        $line->nb_screens,
                        $line->nb_weeks,
                        $line->quantity,
                        $line->media_value,
                        $line->net_investment,
                        $line->impressions,
                        $this->network === 'shopping' ? $line->covid_impressions : null,
                        $this->network === 'shopping' ? $line->covid_cpm : null,
                    ]);
                }

                // Print our market footer
                $ws->pushPosition();

                // Stylize the table footer
                $ws->getStyle($ws->getRelativeRange(16, 1))->applyFromArray(XLSXStyleFactory::tableFooter($this->network));

                $ws->pushPosition();
                $ws->moveCursor(12, 0);
                $ws->getStyle($ws->getRelativeRange(1, 1))->applyFromArray([
                    "borders" => [
                        "right" => [
                            "borderStyle" => Border::BORDER_DOUBLE
                        ]
                    ]
                ]);

                $ws->popPosition();

                $ws->mergeCellsRelative(2);
                $ws->getCurrentCell()->setValue("Total " . $market);
                $ws->setRelativeCellFormat(NumberFormat::FORMAT_NUMBER_00, 9, 0);

                // Monetary values
                $ws->setRelativeCellFormat(NumberFormat::FORMAT_CURRENCY_USD, 11, 0);
                $ws->setRelativeCellFormat(NumberFormat::FORMAT_CURRENCY_USD, 12, 0);
                $ws->setRelativeCellFormat('$#,##0.00', 15, 0);

                $ws->moveCursor(2, 0);

                $lines = collect($lines);

                $ws->printRow([
                    $lines->sum("property_annual_traffic"),
                    $lines->sum("traffic"),
                    null,
                    null,
                    null,
                    null,
                    $lines->sum("nb_screens"),
                    null,
                    null,
                    $lines->sum("media_value"),
                    $lines->sum("net_investment"),
                    $lines->sum("impressions"),
                    $this->network === 'shopping' ? $lines->sum("covid_impressions") : null,
                    $this->network === 'shopping' ? $lines->sum("covid_cpm") : null,
                ]);

                $ws->popPosition();
            }

            // Print our state footer
            $ws->moveCursor(0, 2);
            $ws->pushPosition();

            // Stylize the table footer
            $ws->getStyle($ws->getRelativeRange(16, 1))->applyFromArray(XLSXStyleFactory::tableFooter($this->network));

            $ws->pushPosition();
            $ws->moveCursor(12, 0);
            $ws->getStyle($ws->getRelativeRange(1, 1))->applyFromArray([
                "borders" => [
                    "right" => [
                        "borderStyle" => Border::BORDER_DOUBLE
                    ]
                ]
            ]);

            $ws->popPosition();

            $ws->mergeCellsRelative(2);
            $ws->getCurrentCell()->setValue("Total " . substr(trim($state), 0, -5));

            $ws->setRelativeCellFormat(NumberFormat::FORMAT_NUMBER_00, 9, 0);
            // Monetary values
            $ws->setRelativeCellFormat(NumberFormat::FORMAT_CURRENCY_USD, 11, 0);
            $ws->setRelativeCellFormat(NumberFormat::FORMAT_CURRENCY_USD, 12, 0);
            $ws->setRelativeCellFormat('$#,##0.00', 15, 0);


            $ws->moveCursor(2, 0);

            $lines = collect($markets)->flatten();

            $ws->printRow([
                $lines->sum("property_annual_traffic"),
                $lines->sum("traffic"),
                null,
                null,
                null,
                null,
                $lines->sum("nb_screens"),
                null,
                null,
                $lines->sum("media_value"),
                $lines->sum("net_investment"),
                $lines->sum("impressions"),
                $this->network === 'shopping' ? $lines->sum("covid_impressions") : null,
                $this->network === 'shopping' ? $lines->sum("covid_cpm") : null,
            ]);

            $ws->popPosition();
            $ws->moveCursor(0, 1);
        }

        // Print our network guaranteed orders footer
        $ws->moveCursor(0, 2);
        $ws->pushPosition();

        // Stylize the network footer
        $ws->getStyle($ws->getRelativeRange(13, 1))->applyFromArray(XLSXStyleFactory::networkFooter());
        $ws->getRowDimension($ws->getCursorRow())->setRowHeight(20);

        $ws->mergeCellsRelative(2);
        $ws->getCurrentCell()->setValue("Total " . __("common.network-". $this->network)) ;

        // Monetary values
        $ws->setRelativeCellFormat(NumberFormat::FORMAT_CURRENCY_USD, 11, 0);
        $ws->setRelativeCellFormat(NumberFormat::FORMAT_CURRENCY_USD, 12, 0);

        $ws->moveCursor(2, 0);

        $lines = collect($states)->flatten();

        $ws->printRow([
            $lines->sum("property_annual_traffic"),
            $lines->sum("traffic"),
            null,
            null,
            null,
            null,
            $lines->sum("nb_screens"),
            null,
            null,
            $lines->sum("media_value"),
            $lines->sum("net_investment")
        ]);

        $ws->popPosition();
        $ws->moveCursor(0, 1);
    }

    public function printBuaOrderLinee(Collection $orderLines, Worksheet $ws) {
        if (count($orderLines) === 0) {
            return null;
        }

        // We only print one row per period
        $periods = $orderLines->groupBy(["rangeLengthString"]);
        $ws->moveCursor(0, 2);
        $ws->mergeCellsRelative(2, 1);

        // Stylize the cell
        $ws->getStyle($ws->getCursorPosition())->applyFromArray(XLSXStyleFactory::locationHeader());
        $ws->getRowDimension($ws->getCursorRow())->setRowHeight(20);

        $ws->getCurrentCell()->setValue(__("contract.order-type-bua"));
        $ws->moveCursor(0, 2);

        // Stylize the table
        $ws->getStyle($ws->getRelativeRange(13, count($periods)))->applyFromArray(XLSXStyleFactory::tableBody());

        // Week decimal display
        $ws->setRelativeCellFormat('#,##0.00', 10, 0);

        // Monetary values
        $ws->setRelativeCellFormat(NumberFormat::FORMAT_NUMBER_00, 9, 0);
        $ws->setRelativeCellFormat(NumberFormat::FORMAT_CURRENCY_USD, 11, 0);
        $ws->setRelativeCellFormat(NumberFormat::FORMAT_CURRENCY_USD, 12, 0);

        foreach ($periods as $period => $periodLinesArray) {
            $periodLines = collect($periodLinesArray);

            $ws->printRow([
                null,
                null,
                null,
                null,
                null,
                $periodLines->first()->date_start,
                null,
                null,
                null,
                $periodLines->first()->nb_weeks,
                $periodLines->first()->quantity,
                $periodLines->sum("media_value"),
                0,
            ]);

        }
    }
}
