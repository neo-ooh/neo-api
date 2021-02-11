<?php

namespace Neo\Documents\Contract\Components;

use Closure;
use Illuminate\Support\Collection;
use Illuminate\View\Component;
use Illuminate\View\View;
use Mpdf\Mpdf;

class DetailedOrders extends Component {
    protected Mpdf $mpdf;
    protected Collection $orders;

    /**
     * Create the component instance.
     *
     * @param Mpdf       $mpdf
     * @param Collection $orders
     */
    public function __construct(Mpdf $mpdf, Collection $orders) {
        $this->mpdf   = $mpdf;
        $this->orders = $orders;
    }

    /**
     * Get the view / contents that represent the component.
     *
     * @return View|Closure|string
     */
    public function render() {
        return view('documents.contract.campaign-details.orders', [
            "orders" => $this->orders
        ]);
    }

}
