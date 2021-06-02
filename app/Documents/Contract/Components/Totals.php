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

use Illuminate\Contracts\Foundation\Application;
use Illuminate\Contracts\View\Factory;
use Illuminate\Support\Collection;
use Illuminate\View\Component;
use Neo\Documents\Contract\Order;

class Totals extends Component {
    protected Order $order;
    protected Collection $orders;
    protected Collection $production;
    protected string $size;

    /**
     * Create the component instance.
     *
     * @param Order      $order
     * @param Collection $orders
     * @param string     $size
     * @param Collection $production
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
     * @return Application|Factory|\Illuminate\Contracts\View\View
     */
    public function render() {
        return view('documents.contract.order-totals', [
            "showInvestment"        => $this->order->show_investment,
            "size"                  => $this->size,
            "orders"                => $this->orders,
            "guaranteedImpressions" => $this->order->guaranteed_impressions_count,
            "guaranteedValue"       => $this->order->guaranteed_value,
            "guaranteedDiscount"    => $this->order->guaranteed_discount,
            "guaranteedInvestment"  => $this->order->guaranteed_investment,
            "hasBua"                => $this->order->has_bua,
            "buaImpressions"        => $this->order->bua_impressions_count,
            "buaValue"              => $this->order->bua_value,
            "buaDiscount"           => $this->order->bua_discount,
            "buaInvestment"         => $this->order->bua_investment,
            "potentialDiscount"     => $this->order->potential_discount,
            "grandTotalInvestment"  => $this->order->grand_total_investment,

            "production"      => $this->production,
            "productionCosts" => $this->order->production_costs,
            "cpm" => $this->order->cpm,
        ]);
    }

}
