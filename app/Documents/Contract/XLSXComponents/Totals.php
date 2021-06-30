<?php


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
        $ws->getCurrentCell()->setValue($this->order->getBuaOrders()->sum("media_value"));

        $ws->moveCursor(1, 0);
        $ws->getCurrentCell()->setValue(0);

        $ws->popPosition();
        $ws->moveCursor(0, 1);

        // Then, the TOTAL CANADA row
        $ws->moveCursor(0, 1);
        $ws->pushPosition();

        $ws->getStyle($ws->getRelativeRange(13, 1))->applyFromArray(XLSXStyleFactory::totals());

        $ws->mergeCellsRelative(2);
        $ws->getCurrentCell()->setValue("TOTAL CANADA");

        // Monetary values
        $ws->setRelativeCellFormat(NumberFormat::FORMAT_CURRENCY_USD, 11, 0);
        $ws->setRelativeCellFormat(NumberFormat::FORMAT_CURRENCY_USD, 12, 0);

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
        $ws->moveCursor(0, 2);

        // Finally, we print the footer excerpts
        $ws->pushPosition();
        $ws->moveCursor(3, 0);

        $ws->pushPosition();

        $ws->getStyle($ws->getRelativeRange(5, 1))->applyFromArray(XLSXStyleFactory::totals());
        $ws->mergeCellsRelative(4);
        $ws->getCurrentCell()->setValue(__("contract.table-impressions"));
        $ws->moveCursor(4, 0);
        $ws->getCurrentCell()->setValue($this->order->orderLines->sum("impressions"));

        $ws->popPosition();
        $ws->moveCursor(0, 2);

        $ws->pushPosition();

        $ws->getStyle($ws->getRelativeRange(5, 1))->applyFromArray(XLSXStyleFactory::totals());
        $ws->mergeCellsRelative(4);
        $ws->getCurrentCell()->setValue(__("CPM"));
        $ws->moveCursor(4, 0);
        $ws->getCurrentCell()->setValue($this->order->cpm);
        $ws->setRelativeCellFormat('$#,##0.00');

        $ws->popPosition();
        $ws->moveCursor(0, 2);

        $ws->pushPosition();

        $covidImpressions = $this->order->orderLines->sum("covid_impressions");

        $ws->getStyle($ws->getRelativeRange(5, 1))->applyFromArray(XLSXStyleFactory::totals());
        $ws->mergeCellsRelative(4);
        $ws->getCurrentCell()->setValue("COVID " . __("contract.table-impressions"));
        $ws->moveCursor(4, 0);
        $ws->getCurrentCell()->setValue($covidImpressions);

        $ws->popPosition();
        $ws->moveCursor(0, 2);

        $ws->pushPosition();

        $covidImpressions = $this->order->orderLines->sum("covid_impressions");

        $ws->getStyle($ws->getRelativeRange(5, 1))->applyFromArray(XLSXStyleFactory::totals());
        $ws->mergeCellsRelative(4);
        $ws->getCurrentCell()->setValue("COVID " . __("contract.table-cpm"));
        $ws->moveCursor(4, 0);
        $ws->getCurrentCell()->setValue(($this->order->net_investment / $covidImpressions) * 1000);
        $ws->setRelativeCellFormat('$#,##0.00');

        $ws->popPosition();
        $ws->moveCursor(7, -6);

        $ws->pushPosition();

        $ws->getStyle($ws->getRelativeRange(3, 1))->applyFromArray(XLSXStyleFactory::totals());
        $ws->mergeCellsRelative(2);
        $ws->getCurrentCell()->setValue(__("NET INVESTMENT"));
        $ws->moveCursor(2, 0);
        $ws->getCurrentCell()->setValue($this->order->net_investment);
        $ws->setRelativeCellFormat(NumberFormat::FORMAT_CURRENCY_USD);

        $ws->popPosition();
        $ws->moveCursor(0, 2);

        $ws->pushPosition();

        $ws->getStyle($ws->getRelativeRange(3, 1))->applyFromArray(XLSXStyleFactory::totals());
        $ws->mergeCellsRelative(2);
        $ws->getCurrentCell()->setValue(__("SAVINGS"));
        $ws->moveCursor(2, 0);
        $ws->getCurrentCell()->setValue($this->order->potential_value - $this->order->net_investment);
        $ws->setRelativeCellFormat(NumberFormat::FORMAT_CURRENCY_USD);

        $ws->popPosition();
        $ws->moveCursor(0, 2);

        $ws->pushPosition();

        $ws->getStyle($ws->getRelativeRange(3, 1))->applyFromArray(XLSXStyleFactory::totals());
        $ws->mergeCellsRelative(2);
        $ws->getCurrentCell()->setValue(__("DISCOUNT"));
        $ws->moveCursor(2, 0);
        $ws->setRelativeCellFormat(NumberFormat::FORMAT_PERCENTAGE);
        $ws->getCurrentCell()->setValue($this->order->potential_discount / 100.0);

        $ws->popPosition();
        $ws->moveCursor(0, 2);


        $ws->popPosition();
        $ws->moveCursor(0, 8);
    }
}
