<?php

namespace Neo\Documents\Contract\Components;

use Closure;
use Illuminate\Support\Collection;
use Illuminate\View\Component;
use Illuminate\View\View;
use Mpdf\Mpdf;
use Neo\Documents\Contract\Order;

class DetailedOrders extends Component {
    protected order $order;
    protected Collection $orders;

    /**
     * Create the component instance.
     *
     * @param Order $order
     * @param Collection $orders
     */
    public function __construct(Order $order, Collection $orders) {
        $this->order = $order;
        $this->orders = $orders;
    }

    /**
     * Get the view / contents that represent the component.
     *
     * @return View|Closure|string
     */
    public function render() {
        return view('documents.contract.campaign-details.orders', [
            "order" => $this->order,
            "orders" => $this->orders
        ]);
    }

}
