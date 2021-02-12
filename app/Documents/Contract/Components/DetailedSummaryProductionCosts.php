<?php

namespace Neo\Documents\Contract\Components;

use Closure;
use Illuminate\Support\Collection;
use Illuminate\View\Component;
use Illuminate\View\View;

class DetailedSummaryProductionCosts extends Component {
    protected Collection $orders;

    /**
     * Create the component instance.
     *
     * @param Collection $orders
     */
    public function __construct(Collection $orders) {
        $this->orders = $orders;
    }

    /**
     * Get the view / contents that represent the component.
     *
     * @return View|Closure|string
     */
    public function render() {
        return view('documents.contract.campaign-details.summary-production-costs', [
            "orders" => $this->orders
        ]);
    }

}
