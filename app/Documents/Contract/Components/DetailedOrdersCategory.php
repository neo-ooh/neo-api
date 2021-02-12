<?php
/*
 * Copyright 2020 (c) Neo-OOH - All Rights Reserved
 * Unauthorized copying of this file, via any medium is strictly prohibited
 * Proprietary and confidential
 * Written by Valentin Dufois <Valentin Dufois>
 *
 * @neo/api - DetailedOrdersCategory.php
 */

namespace Neo\Documents\Contract\Components;

use Closure;
use Illuminate\Support\Collection;
use Illuminate\View\Component;
use Illuminate\View\View;
use Neo\Documents\Contract\OrderLine;

class DetailedOrdersCategory extends Component {
    protected string $type;
    protected Collection $purchases;


    /**
     * Create the component instance.
     *
     * @param string     $type
     * @param Collection $purchases
     */
    public function __construct(string $type, Collection $purchases) {
        $this->purchases = $purchases;
        $this->type      = $type;
    }

    /**
     * Get the view / contents that represent the component.
     *
     * @return View|Closure|string
     */
    public function render() {
        $purchases = $this
            ->purchases
            ->filter(fn(/**@var OrderLine $order */ $order) => [
                    "purchase" => $order->isGuaranteedPurchase(),
                    "bonus"    => $order->isGuaranteedBonus(),
                    "bua"      => $order->isBonusUponAvailability(),
                ][$this->type]);

        if($purchases->count() === 0) {
            return "";
        }

        return view('documents.contract.campaign-details.orders-category', [
            "type"        => $this->type,
            "orders"      => $purchases,
            "totalSpots" => $purchases->sum("quantity"),
            "totalScreens" => $purchases->sum("nb_screens"),
            "totalImpressions" => $purchases->sum("impressions"),
            "totalValue" => $purchases->sum("unit_price"),
            "totalDiscount" => $purchases->sum("discount"),
            "totalInvestment" => $purchases->sum(fn($order) => $order->netInvestment()),
        ]);
    }

}
