<?php
/*
 * Copyright 2020 (c) Neo-OOH - All Rights Reserved
 * Unauthorized copying of this file, via any medium is strictly prohibited
 * Proprietary and confidential
 * Written by Valentin Dufois <vdufois@neo-ooh.com>
 *
 * @neo/api - DetailedSummary.php
 */

namespace Neo\Documents\Contract\Components;

use Closure;
use Illuminate\Support\Collection;
use Illuminate\View\Component;
use Illuminate\View\View;
use Neo\Documents\Contract\Order;

class DetailedSummary extends Component {
    protected Order $order;
    protected Collection $orders;
    protected Collection $production;
    protected bool $renderDisclaimers;

    /**
     * Create the component instance.
     *
     * @param Collection $orders
     * @param Collection  $production
     */
    public function __construct(Order $order, Collection $orders, Collection $production, bool $renderDisclaimers) {
        $this->order     = $order;
        $this->orders     = $orders;
        $this->production = $production;
        $this->renderDisclaimers = $renderDisclaimers;
    }

    /**
     * Get the view / contents that represent the component.
     *
     * @return View|Closure|string
     */
    public function render() {
        return view('documents.contract.campaign-details.summary', [
            "order"     => $this->order,
            "orders"     => $this->orders,
            "production" => $this->production,
            "renderDisclaimers" => $this->renderDisclaimers,
        ]);
    }

}
