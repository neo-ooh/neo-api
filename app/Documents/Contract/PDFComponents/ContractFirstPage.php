<?php
/*
 * Copyright 2020 (c) Neo-OOH - All Rights Reserved
 * Unauthorized copying of this file, via any medium is strictly prohibited
 * Proprietary and confidential
 * Written by Valentin Dufois <vdufois@neo-ooh.com>
 *
 * @neo/api - ContractFirstPage.php
 */

namespace Neo\Documents\Contract\PDFComponents;

use Illuminate\Contracts\Foundation\Application;
use Illuminate\Contracts\View\Factory;
use Illuminate\Contracts\View\View;
use Illuminate\View\Component;
use Neo\Documents\Contract\Customer;
use Neo\Documents\Contract\Order;

class ContractFirstPage extends Component {

    protected Order $order;
    protected Customer $customer;


    /**
     * Create the component instance.
     *
     * @param Order    $order
     * @param Customer $customer
     */
    public function __construct(Order $order, Customer $customer) {
        $this->order    = $order;
        $this->customer = $customer;
    }

    /**
     * Get the view / contents that represent the component.
     *
     * @return Application|Factory|View
     */
    public function render() {
        return view('documents.contract.first-page', [
            "order"    => $this->order,
            "customer" => $this->customer,
        ]);
    }

}
