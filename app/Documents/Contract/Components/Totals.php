<?php

namespace Neo\Documents\Contract\Components;

use Closure;
use Illuminate\Support\Collection;
use Illuminate\View\Component;
use Illuminate\View\View;

class Totals extends Component {
    protected Collection $orders;
    protected string $size;

    /**
     * Create the component instance.
     *
     * @param Collection $orders
     * @param string     $size
     */
    public function __construct(Collection $orders, string $size) {
        $this->orders = $orders;
        $this->size = $size;
    }

    /**
     * Get the view / contents that represent the component.
     *
     * @return View|Closure|string
     */
    public function render() {
        $guaranteedOrders = $this->orders->filter(fn($order) => $order->isGuaranteedPurchase() || $order->isGuaranteedBonus());
        $buaOrders = $this->orders->filter(fn($order) => $order->isBonusUponAvailability());

        return view('documents.contract.order-totals', [
            "size" => $this->size,
            "orders" => $this->orders,
            "guaranteedImpressions" => $guaranteedOrders->sum("impressions"),
            "guaranteedValue" => $guaranteedOrders->sum("unit_price"),
            "guaranteedDiscount" => $guaranteedOrders->sum("discount") / $guaranteedOrders->count(),
            "guaranteedInvestment" => $guaranteedOrders->sum(fn($order) => $order->netInvestment()),
            "hasBua" => $buaOrders->isNotEmpty(),
            "buaImpressions" => $buaOrders->sum("impressions"),
            "buaValue" => $buaOrders->sum("unit_price"),
            "buaDiscount" => $buaOrders->sum("discount") / $guaranteedOrders->count(),
            "buaInvestment" => $buaOrders->sum(fn($order) => $order->netInvestment()),
        ]);
    }

}
