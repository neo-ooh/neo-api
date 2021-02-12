<?php

namespace Neo\Documents\Contract\Components;

use Closure;
use Illuminate\Support\Collection;
use Illuminate\View\Component;
use Illuminate\View\View;

class DetailedSummaryNotices extends Component {

    /**
     * Create the component instance.
     *
     */
    public function __construct() {}

    /**
     * Get the view / contents that represent the component.
     *
     * @return View|Closure|string
     */
    public function render() {
        return view('documents.contract.campaign-details.summary-notices');
    }

}
