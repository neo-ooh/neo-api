<?php

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


    /**
     * Create the component instance.
     *
     * @param string     $type
     * @param Order      $order
     * @param string     $network
     * @param Collection $purchases
     */
    public function __construct(string $type, Order $order, string $network, Collection $purchases) {
        $this->type      = $type;
        $this->order     = $order;
        $this->network   = $network;
        $this->purchases = $purchases;
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
            ->sortBy(['market', 'property_name'])
            ->groupBy(['market', 'property_name']);

        if ($purchases->count() === 0) {
            return "";
        }

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

}
