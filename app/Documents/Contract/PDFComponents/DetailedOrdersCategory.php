<?php
/*
 * Copyright 2020 (c) Neo-OOH - All Rights Reserved
 * Unauthorized copying of this file, via any medium is strictly prohibited
 * Proprietary and confidential
 * Written by Valentin Dufois <vdufois@neo-ooh.com>
 *
 * @neo/api - DetailedOrdersCategory.php
 */

namespace Neo\Documents\Contract\PDFComponents;

use Illuminate\Contracts\Foundation\Application;
use Illuminate\Contracts\View\Factory;
use Illuminate\Contracts\View\View;
use Illuminate\Support\Collection;
use Illuminate\View\Component;
use Neo\Documents\Contract\Order;
use Neo\Documents\Contract\OrderLine;

class DetailedOrdersCategory extends Component {
    protected string $type;
    protected Order $order;
    protected Collection $purchases;


    /**
     * Create the component instance.
     *
     * @param string     $type
     * @param Order      $order
     * @param Collection $purchases
     */
    public function __construct(string $type, Order $order, Collection $purchases) {
        $this->type      = $type;
        $this->order     = $order;
        $this->purchases = $purchases;
    }

    /**
     * Get the view / contents that represent the component.
     *
     * @return Application|Factory|View|string
     */
    public function render() {
        $purchases = $this
            ->purchases
            ->filter(fn(/**@var OrderLine $order */ $order) => [
                                                                   "purchase" => $order->isGuaranteedPurchase(),
                                                                   "bonus"    => $order->isGuaranteedBonus(),
                                                                   "bua"      => $order->isBonusUponAvailability(),][$this->type]);

        if ($purchases->count() === 0) {
            return "";
        }

        return view('documents.contract.campaign-details.orders-category', [
            "type"             => $this->type,
            "order"            => $this->order,
            "orders"           => $purchases,
            "totalSpots"       => $purchases->sum("quantity"),
            "totalScreens"     => $purchases->sum("nb_screens"),
            "totalImpressions" => $purchases->sum("impressions"),
            "totalValue"       => $purchases->sum("media_value"),
            "totalDiscount"    => $purchases->sum("discount"),
            "totalInvestment"  => $purchases->sum("net_investment"),
        ]);
    }

    public function __toString(): string {
        $temp = $this->render();

        if (is_string($temp)) {
            return $temp;
        }

        return $temp->render();
    }

}
