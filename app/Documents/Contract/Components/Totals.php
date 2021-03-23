<?php
/*
 * Copyright 2020 (c) Neo-OOH - All Rights Reserved
 * Unauthorized copying of this file, via any medium is strictly prohibited
 * Proprietary and confidential
 * Written by Valentin Dufois <vdufois@neo-ooh.com>
 *
 * @neo/api - Totals.php
 */

namespace Neo\Documents\Contract\Components;

use Closure;
use Illuminate\Support\Collection;
use Illuminate\View\Component;
use Illuminate\View\View;
use Neo\Documents\Contract\Order;

class Totals extends Component {
    protected Order $order;
    protected Collection $orders;
    protected Collection $production;
    protected string $size;

    /**
     * Create the component instance.
     *
     * @param Collection $orders
     * @param Collection $production
     * @param string     $size
     */
    public function __construct(Order $order, Collection $orders, string $size, Collection $production) {
        $this->order      = $order;
        $this->orders     = $orders;
        $this->production = $production;
        $this->size       = $size;
    }

    /**
     * Get the view / contents that represent the component.
     *
     * @return View|Closure|string
     */
    public function render() {
        $guaranteedOrders = $this->orders->filter(fn($order) => $order->isGuaranteedPurchase() || $order->isGuaranteedBonus());
        $buaOrders        = $this->orders->filter(fn($order) => $order->isBonusUponAvailability());

        return view('documents.contract.order-totals', [
            "showInvestment"        => $this->order->show_investment,
            "size"                  => $this->size,
            "orders"                => $this->orders,
            "guaranteedImpressions" => $guaranteedOrders->sum("impressions"),
            "guaranteedValue"       => $guaranteedOrders->sum("media_value"),
            "guaranteedDiscount"    => $guaranteedOrders->count() > 0 ? $guaranteedOrders->sum("discount") / $guaranteedOrders->count() : 0,
            "guaranteedInvestment"  => $guaranteedOrders->sum("net_investment"),
            "hasBua"                => $buaOrders->isNotEmpty(),
            "buaImpressions"        => $buaOrders->sum("impressions"),
            "buaValue"              => $buaOrders->sum("media_value"),
            "buaDiscount"           => $guaranteedOrders->count() > 0 ? $buaOrders->sum("discount") / $guaranteedOrders->count() : 0,
            "buaInvestment"         => $buaOrders->sum("net_investment"),

            "grandTotalInvestment" => $guaranteedOrders->sum("net_investment") + $buaOrders->sum("net_investment"),

            "production" => $this->production
        ]);
    }

}
