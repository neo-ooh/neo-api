<?php

namespace Neo\Documents\Contract\Components;

use Closure;
use Illuminate\Support\Collection;
use Illuminate\View\Component;
use Illuminate\View\View;

class DetailedSummary extends Component {
    protected Collection $orders;
    protected Collection $production;

    /**
     * Create the component instance.
     *
     * @param Collection $orders
     * @param Collection  $production
     */
    public function __construct(Collection $orders, Collection $production) {
        $this->orders     = $orders;
        $this->production = $production;
    }

    /**
     * Get the view / contents that represent the component.
     *
     * @return View|Closure|string
     */
    public function render() {
        return view('documents.contract.campaign-details.summary', [
            "orders"     => $this->orders,
            "production" => $this->production,
        ]);
    }

}
