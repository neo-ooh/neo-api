<?php
/*
 * Copyright 2020 (c) Neo-OOH - All Rights Reserved
 * Unauthorized copying of this file, via any medium is strictly prohibited
 * Proprietary and confidential
 * Written by Valentin Dufois <vdufois@neo-ooh.com>
 *
 * @neo/api - DetailedSummaryProductionCosts.php
 */

namespace Neo\Documents\Contract\Components;

use Illuminate\Contracts\Foundation\Application;
use Illuminate\Contracts\View\Factory;
use Illuminate\Support\Collection;
use Illuminate\View\Component;

class DetailedSummaryProductionCosts extends Component {
    protected Collection $production;

    /**
     * Create the component instance.
     *
     * @param Collection $production
     */
    public function __construct(Collection $production) {
        $this->production = $production;
    }

    /**
     * Get the view / contents that represent the component.
     *
     * @return Application|Factory|\Illuminate\Contracts\View\View
     */
    public function render() {
        return view('documents.contract.campaign-details.summary-production-costs', [
            "production" => $this->production
        ]);
    }

}
