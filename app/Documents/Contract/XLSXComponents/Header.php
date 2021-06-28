<?php

namespace Neo\Documents\Contract\XLSXComponents;

use Neo\Documents\Contract\Customer;
use Neo\Documents\Contract\Order;
use Neo\Documents\XLSX\Component;
use Neo\Documents\XLSX\Worksheet;

class Header extends Component {
    protected Order $order;
    protected Customer $customer;

    public function __construct(Order $order, Customer $customer) {
        $this->order = $order;
        $this->customer = $customer;
    }

    public function render(Worksheet $ws) {
        $ws->pushPosition();

        // Add the Neo logo
        $drawing = new \PhpOffice\PhpSpreadsheet\Worksheet\Drawing();
        $drawing->setName('Neo-OOH');
        $drawing->setDescription('Neo Out of Home');
        $drawing->setPath(resource_path("logos/main.dark.en@2x.png"));
        $drawing->setHeight(60);
        $drawing->setWorksheet($ws);
        $drawing->setCoordinates('F2');

        // Date
        $ws->printRow(["Date", $this->order->date]);

        // Advertiser
        $ws->printRow([__("contract.header-advertiser"), $this->customer->account]);
        $ws->moveCursor(1, 0);
        $ws->mergeCellsRelative(2);
        $ws->moveCursor(-1, 0);

        // Customer
        $ws->printRow([__("contract.header-customer"), $this->customer->parent_name]);
        $ws->moveCursor(1, 0);
        $ws->mergeCellsRelative(2);
        $ws->moveCursor(-1, 0);

        // Presented to
        $ws->printRow([__("contract.header-presented-to"), $this->customer->name]);
        $ws->moveCursor(1, 0);
        $ws->mergeCellsRelative(2);
        $ws->moveCursor(-1, 0);

        // Account executive
        $ws->printRow([__("contract.header-account-executive"), $this->order->salesperson]);
        $ws->moveCursor(1, 0);
        $ws->mergeCellsRelative(2);
        $ws->moveCursor(-1, 0);

        $ws->moveCursor(10, -4);

        // Camapign Name
        $ws->printRow([__("contract.header-campaign"), $this->order->campaign_name]);
        $ws->moveCursor(1, 0);
        $ws->mergeCellsRelative(2);
        $ws->moveCursor(-1, 0);

        $periods = $this->order->orderLines->pluck("rangeLengthString")->unique();

        // Period
        $ws->printRow([trans_choice("common.broadcast-periods", $periods->count()), $periods->join("\n")]);
        $ws->moveCursor(1, 0);
        $ws->mergeCellsRelative(2);
        $ws->moveCursor(-1, 0);

        $ws->popPosition();
        $ws->moveCursor(0, 5);
    }
}
