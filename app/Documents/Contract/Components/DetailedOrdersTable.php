<?php
/*
 * Copyright 2020 (c) Neo-OOH - All Rights Reserved
 * Unauthorized copying of this file, via any medium is strictly prohibited
 * Proprietary and confidential
 * Written by Valentin Dufois <vdufois@neo-ooh.com>
 *
 * @neo/api - DetailedOrdersTable.php
 */

namespace Neo\Documents\Contract\Components;

use Closure;
use Illuminate\Support\Collection;
use Illuminate\View\Component;
use Illuminate\View\View;
use Neo\Documents\Contract\Order;
use Neo\Documents\Contract\OrderLine;
use Neo\Documents\Network;

class DetailedOrdersTable extends Component {
    protected string $type;
    protected Order $order;
    protected string $network;
    protected Collection $purchases;

    protected array $regions = [
        "Greater Montreal",
        "Eastern Townships",
        "Center of Quebec",
        "Hull-Gatineau",
        "Quebec City & Region",
        "Northwest of Quebec",
        "Northeast of Quebec",
        "Greater Toronto",
        "North-Western Ontario",
        "South-Western Ontario",
        "Kingston-Belleville",
        "Ottawa",
        "Greater Vancouver",
        "Winnipeg & Region",
        "Regina & Region",
        "Edmonton & Region",
        "Calgary & Region",
        "Halifax & Region",
        "New Brunswick",
    ];

    /**
     * Create the component instance.
     *
     * @param string $type
     * @param Order  $order
     * @param string $network
     */
    public function __construct(string $type, Order $order, string $network) {
        $this->type      = $type;
        $this->order     = $order;
        $this->network   = $network;
        $this->purchases = $order->orderLines;
    }

    /**
     * Get the view / contents that represent the component.
     *
     * @return View|Closure|string
     */
    public function render() {
        $purchases = $this
            ->purchases
            ->filter(fn(/**@var OrderLine $order */ $order) => $order->isNetwork($this->network) &&
                [
                    "purchase" => $order->isGuaranteedPurchase(),
                    "bonus"    => $order->isGuaranteedBonus(),
                    "bua"      => $order->isBonusUponAvailability(),
                ][$this->type])
            ->sortBy(['property_name'])
            ->groupBy(['market', 'property_name']);


        if ($purchases->count() === 0) {
            return "";
        }

        $purchases = (new Collection($this->regions))
            ->flip()
            ->replace($purchases)
            ->filter(fn($region) => !is_int($region));

        return view('documents.contract.campaign-details.orders-network', [
            "type"        => $this->type,
            "network"     => $this->network,
            "networkName" => $this->networkName(),
            "orders"      => $purchases,
            "order"       => $this->order,
        ]);
    }

    /**
     * Gives the display name for the given network identifier
     *
     * @return string
     */
    public function networkName(): string {
        return [
                   Network::NEO_SHOPPING => "Neo Shopping",
                   Network::NEO_OTG      => "Neo On the Go",
                   Network::NEO_FITNESS  => "Neo Fitness",
               ][$this->network];
    }

    public function __toString(): string {
        $temp = $this->render();

        if (is_string($temp)) {
            return $temp;
        }

        return $temp->render();
    }

}
