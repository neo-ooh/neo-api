<?php
/*
 * Copyright 2023 (c) Neo-OOH - All Rights Reserved
 * Unauthorized copying of this file, via any medium is strictly prohibited
 * Proprietary and confidential
 * Written by Valentin Dufois <vdufois@neo-ooh.com>
 *
 * @neo/api - DetailedOrdersTable.php
 */

namespace Neo\Documents\Contract\PDFComponents;

use Illuminate\Contracts\Foundation\Application;
use Illuminate\Contracts\View\Factory;
use Illuminate\Contracts\View\View;
use Illuminate\Support\Collection;
use Illuminate\View\Component;
use Neo\Documents\Contract\Order;
use Neo\Documents\Contract\OrderLine;
use Neo\Documents\Network;

class DetailedOrdersTable extends Component {
    protected string $type;
    protected Order $order;
    protected string $network;
    protected Collection $purchases;

    public const NETWORK_SUBSECTIONS = [
        Network::NEO_SHOPPING => [null],
        Network::NEO_OTG      => ["outdoor", "indoor", "adapt"],
        Network::NEO_FITNESS  => [null],
    ];

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
     * @return Application|Factory|View|string
     */
    public function render() {
        $purchases = $this
            ->purchases
            ->filter(fn(/**@var OrderLine $order */ $order) => $order->isNetwork($this->network) &&
                [
                    "purchase" => $order->isGuaranteedPurchase(),
                    "bonus"    => $order->isGuaranteedBonus(),
                    "bua"      => $order->isBonusUponAvailability(),
                ][$this->type]);

        $views = "";


        foreach (static::NETWORK_SUBSECTIONS[$this->network] as $subsection) {
            $allLines = collect([...$purchases]);

            $sectionPurchases = match ($subsection) {
                "indoor"  => $allLines->filter(fn(OrderLine $line) => $line->isIndoor()),
                "outdoor" => $allLines->filter(fn(OrderLine $line) => $line->isOutdoor()),
                "adapt"   => $allLines->filter(fn(OrderLine $line) => $line->isAdapt()),
                default   => $allLines
            };

//            dump($this->networkName(), $subsection, $sectionPurchases->count());

            if ($sectionPurchases->count() === 0) {
                continue;
            }

            $views .= view('documents.contract.campaign-details.orders-network', [
                "type"        => $this->type,
                "network"     => $this->network,
                "networkName" => $this->networkName(),
                "subsection"  => $subsection,
                "orders"      => $sectionPurchases
                    ->sortBy(['market_order', 'property_name'])
                    ->groupBy(['market_order', 'property_name']),
                "order"       => $this->order,
            ]);
        }

        return $views;
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
