<?php

namespace Neo\Documents\Contract\Components;

use Closure;
use Illuminate\Support\Collection;
use Illuminate\View\Component;
use Illuminate\View\View;
use Neo\Documents\Contract\OrderLine;

class DetailedSummaryProductionCosts extends Component {
    protected Collection $production;

    /**
     * Create the component instance.
     *
     * @param OrderLine $production
     */
    public function __construct(Collection $production) {
        $this->production = $production;
    }

    /**
     * Get the view / contents that represent the component.
     *
     * @return View|Closure|string
     */
    public function render() {
        return view('documents.contract.campaign-details.summary-production-costs', [
            "production" => $this->production
        ]);
    }

}
