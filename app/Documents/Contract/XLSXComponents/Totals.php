<?php
/*
 * Copyright 2020 (c) Neo-OOH - All Rights Reserved
 * Unauthorized copying of this file, via any medium is strictly prohibited
 * Proprietary and confidential
 * Written by Valentin Dufois <vdufois@neo-ooh.com>
 *
 * @neo/api - Totals.php
 */

namespace Neo\Documents\Contract\XLSXComponents;


use Neo\Documents\Contract\Order;
use Neo\Documents\XLSX\Component;
use Neo\Documents\XLSX\Worksheet;
use Neo\Documents\XLSX\XLSXStyleFactory;
use PhpOffice\PhpSpreadsheet\Style\NumberFormat;

class Totals extends Component {

    protected Order $order;

    protected int $annualTraffic;
    protected int $campaignTraffic;

    public function __construct(Order $order, int $annualTraffic, int $campaignTraffic) {
        $this->order           = $order;
        $this->annualTraffic   = $annualTraffic;
        $this->campaignTraffic = $campaignTraffic;
    }

    public function render(Worksheet $ws) {
        // First the bonus totals
        $this->renderBonusTotals($ws);

        // Then, the TOTAL CANADA row, with an additional header row just before
        $ws->moveCursor(0, 2);
        $ws->pushPosition();

        $ws->getStyle($ws->getRelativeRange(13, 1))->applyFromArray(XLSXStyleFactory::tableHeader());

        $ws->printRow([
            __("contract.table-markets"),
            null,
            __("contract.table-annual-traffic"),
            __("contract.table-campaign-traffic"),
            null,
            null,
            null,
            null,
            __("contract.table-count-of-screens-posters"),
            null,
            null,
            __("contract.table-media-value"),
            __("contract.table-net-investment")
        ]);
        $ws->moveCursor(0, 1);

        $ws->getStyle($ws->getRelativeRange(13, 1))->applyFromArray(XLSXStyleFactory::totals());

        $ws->mergeCellsRelative(2);
        $ws->getCurrentCell()->setValue("TOTAL CANADA");

        // Monetary values
        $ws->setRelativeCellFormat(NumberFormat::FORMAT_CURRENCY_USD, 11, 0);
        $ws->setRelativeCellFormat(XLSXStyleFactory::FORMAT_CURRENCY_TWO_PLACES, 12, 0);

        $ws->moveCursor(2, 0);

        $ws->printRow([
            $this->annualTraffic,
            $this->campaignTraffic,
            null,
            null,
            null,
            null,
            $this->order->orderLines->sum("nb_screens"),
            null,
            null,
            $this->order->orderLines->sum("media_value"),
            $this->order->orderLines->sum("net_investment")
        ]);

        $ws->popPosition();
        $ws->moveCursor(0, 4);

        // Prepare our calculations
        $cpmTotal = $this->order->getGuaranteedOrders()
                                ->sum("impressions") > 0 ? ($this->order->net_investment / $this->order->getGuaranteedOrders()
                                                                                                       ->sum("impressions")) * 1000 : 0;
        $cpm      = $this->order->orderLines->sum("impressions") > 0 ? ($this->order->net_investment / $this->order->orderLines->sum("impressions")) * 1000 : 0;

        // Finally, we print the footer excerpts
        $ws->pushPosition();
        $ws->moveCursor(3, 0);

        $this->addRow($ws, __("common.guaranteed-impressions"), $this->order->getGuaranteedOrders()->sum("impressions"));

        $this->addRow($ws, __("CPM"), $cpmTotal, XLSXStyleFactory::FORMAT_CURRENCY_TWO_PLACES);

        $this->addRow($ws, __("common.potential-impressions"), $this->order->orderLines->sum("impressions"));

        $this->addRow($ws, __("common.potential-cpm"), $cpm, '$#,##0.00');

        $this->addRow($ws, __("SAVINGS"), $this->order->potential_value - $this->order->grand_total_investment, NumberFormat::FORMAT_CURRENCY_USD);

        $this->addRow($ws, __("DISCOUNT"), $this->order->potential_discount / 100.0, NumberFormat::FORMAT_PERCENTAGE);

        $this->addRow($ws, __("PRODUCTION FEES"), $this->order->production_costs, NumberFormat::FORMAT_CURRENCY_USD);

        $this->addRow($ws, __("NET INVESTMENT"), $this->order->net_investment, XLSXStyleFactory::FORMAT_CURRENCY_TWO_PLACES);


        $ws->popPosition();
        $ws->moveCursor(0, 16);
    }

    public function renderBonusTotals(Worksheet $ws) {
        $bonusMediaValue = $this->order->getBuaOrders()->sum("media_value");

        if ($bonusMediaValue === 0) {
            return;
        }

        $ws->moveCursor(0, 2);
        $ws->pushPosition();

        // Stylize the row
        $ws->getStyle($ws->getRelativeRange(13, 1))->applyFromArray(XLSXStyleFactory::tableFooter("shopping"));

        // Monetary values
        $ws->setRelativeCellFormat(NumberFormat::FORMAT_CURRENCY_USD, 11, 0);
        $ws->setRelativeCellFormat(NumberFormat::FORMAT_CURRENCY_USD, 12, 0);

        $ws->mergeCellsRelative(2, 1);
        $ws->getCurrentCell()->setValue("TOTAL BONUS");

        $ws->moveCursor(11, 0);
        $ws->getCurrentCell()->setValue($bonusMediaValue);

        $ws->moveCursor(1, 0);
        $ws->getCurrentCell()->setValue(0);

        $ws->popPosition();
    }

    public function addRow(Worksheet $ws, string $label, $value, $format = null) {
        $ws->pushPosition();

        $ws->getStyle($ws->getRelativeRange(3, 1))->applyFromArray(XLSXStyleFactory::totals());
        $ws->mergeCellsRelative(2);
        $ws->getCurrentCell()->setValue($label);
        $ws->moveCursor(2, 0);

        if ($format) {
            $ws->setRelativeCellFormat($format);
        }

        $ws->getCurrentCell()->setValue($value);

        $ws->popPosition();
        $ws->moveCursor(0, 2);
    }
}
