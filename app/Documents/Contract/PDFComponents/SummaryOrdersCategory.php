<?php
/*
 * Copyright 2020 (c) Neo-OOH - All Rights Reserved
 * Unauthorized copying of this file, via any medium is strictly prohibited
 * Proprietary and confidential
 * Written by Valentin Dufois <vdufois@neo-ooh.com>
 *
 * @neo/api - SummaryOrdersCategory.php
 */

namespace Neo\Documents\Contract\PDFComponents;

use Illuminate\Contracts\Foundation\Application;
use Illuminate\Contracts\View\Factory;
use Illuminate\Contracts\View\View;
use Illuminate\Support\Collection;
use Illuminate\View\Component;
use Neo\Documents\Contract\Order;

class SummaryOrdersCategory extends Component {
    protected string $category;
    protected Collection $orders;
    protected Order $order;

    /**
     * Create the component instance.
     *
     * @param string     $category
     * @param Collection $orders
     * @param Order      $order
     */
    public function __construct(string $category, Collection $orders, Order $order) {
        $this->category = $category;
        $this->orders   = $orders;
        $this->order    = $order;
    }

    /**
     * Get the view / contents that represent the component.
     *
     * @return Application|Factory|View
     */
    public function render() {
        return view('documents.contract.campaign-summary.orders-category', [
            "category" => $this->category,
            "orders"   => $this->orders,
            "order"    => $this->order,
        ]);
    }

}
