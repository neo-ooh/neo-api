<?php

namespace Neo\Documents\Contract\Components;

use Closure;
use Illuminate\Support\Collection;
use Illuminate\View\Component;
use Illuminate\View\View;

class DetailedSummary extends Component {
    protected Collection $orders;
    protected Collection $production;
    protected bool $renderDisclaimers;

    /**
     * Create the component instance.
     *
     * @param Collection $orders
     * @param Collection  $production
     */
    public function __construct(Collection $orders, Collection $production, bool $renderDisclaimers) {
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
            "orders"     => $this->orders,
            "production" => $this->production,
            "renderDisclaimers" => $this->renderDisclaimers,
        ]);
    }

}
