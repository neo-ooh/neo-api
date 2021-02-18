<?php

namespace Neo\Documents\Contract\Components;

use Closure;
use Illuminate\Support\Collection;
use Illuminate\View\Component;
use Illuminate\View\View;
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
     */
    public function __construct(string $category, Collection $orders, Order $order) {
        $this->category = $category;
        $this->orders   = $orders;
        $this->order   = $order;
    }

    /**
     * Get the view / contents that represent the component.
     *
     * @return View|Closure|string
     */
    public function render() {
        return view('documents.contract.campaign-summary.orders-category', [
            "category" => $this->category,
            "orders"   => $this->orders,
            "order"   => $this->order,
        ]);
    }

}
