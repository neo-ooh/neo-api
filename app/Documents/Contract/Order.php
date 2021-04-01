<?php
/*
 * Copyright 2020 (c) Neo-OOH - All Rights Reserved
 * Unauthorized copying of this file, via any medium is strictly prohibited
 * Proprietary and confidential
 * Written by Valentin Dufois <vdufois@neo-ooh.com>
 *
 * @neo/api - Order.php
 */

namespace Neo\Documents\Contract;

use Illuminate\Support\Collection;

class Order {
    public string $reference;
    public string $date;
    public string $salesperson;
    public string $status;

    public string $campaigns;
    public string $campaign_name;
    public string $campaign_start;
    public string $campaign_end;

    public string $bonus_impression;

    public string $amount_before_discount;
    public string $discount_amount;
    public string $taxes;
    public string $total;
    public string $traffic;

    public string $show_investment;

    public Collection $orderLines;
    public Collection $productionLines;

    // Computed values

    public int $guaranteed_impressions_count = 0;
    public float $guaranteed_value = 0;
    public float $guaranteed_discount = 0;
    public float $guaranteed_investment = 0;

    public bool $has_bua = false;
    public int $bua_impressions_count = 0;
    public float $bua_value = 0;
    public float $bua_discount = 0;
    public float $bua_investment = 0;

    public float $potential_value = 0;
    public float $potential_discount = 0;
    public float $grand_total_investment = 0;

    public float $production_costs = 0;
    public float $net_investment = 0;
    public float $cpm = 0;

    public function __construct(array $record) {
        $this->reference     = $record["name"];
        $this->date          = $record["date_order"];
        $this->salesperson   = $record["user_id"];
        $this->status        = $record["state"];
        $this->campaign_name = $record["campaign_name"];
//        $this->campaign_start         = $record["campaign_ids/date_start"];
//        $this->campaign_end           = $record["campaign_ids/date_end"];
//        $this->bonus_impression       = $record["bonus_impression"];
        $this->amount_before_discount = $record["amount_undiscounted"];
        $this->discount_amount        = $record["amount_discount"];
        $this->taxes                  = $record["amount_tax"];
        $this->total                  = $record["amount_total"];
        $this->traffic                = $record["traffic"];
        $this->show_investment        = $record["investment"] === "True";

        $this->orderLines      = new Collection();
        $this->productionLines = new Collection();
    }

    // Getters

    public function getPurchasedOrders(): Collection {
        return $this->orderLines->filter(fn($order) => $order->isGuaranteedPurchase());
    }

    public function getBonusOrders(): Collection {
        return $this->orderLines->filter(fn($order) => $order->isGuaranteedBonus());
    }

    public function getGuaranteedOrders(): Collection {
        return $this->orderLines->filter(fn($order) => $order->isGuaranteedPurchase() || $order->isGuaranteedBonus());
    }

    public function getBuaOrders(): Collection {
        return $this->orderLines->filter(fn($order) => $order->isBonusUponAvailability());
    }

    // Compute values
    public function computeValues(): void {
        // Guaranteed orders
        $guaranteedOrders                   = $this->getGuaranteedOrders();
        $this->guaranteed_impressions_count = $guaranteedOrders->sum("impressions");
        $this->guaranteed_value             = $guaranteedOrders->sum("media_value");
        $this->guaranteed_investment          = $guaranteedOrders->sum("net_investment");
        $this->guaranteed_discount          = ($this->guaranteed_value - $this->guaranteed_investment) / $this->guaranteed_value * 100;

        // Bua orders
        $buaOrders     = $this->getBuaOrders();
        $this->has_bua = $buaOrders->isNotEmpty();

        if ($this->has_bua) {
            $this->bua_impressions_count = $buaOrders->sum("impressions");
            $this->bua_value             = $buaOrders->sum("media_value");
            $this->bua_discount          = $buaOrders->sum("discount") / $buaOrders->count();
            $this->bua_investment        = $buaOrders->sum("net_investment");
        }

        // Orders totals
        $this->potential_value = $this->guaranteed_value + $this->bua_value;
        $this->potential_discount = ($this->potential_value - $this->total) / $this->potential_value * 100;
        $this->grand_total_investment = $this->guaranteed_investment + $this->bua_investment;

        // Production costs
        $this->production_costs = $this->productionLines->sum("subtotal");
        $this->net_investment = $this->grand_total_investment + $this->production_costs;
        $this->cpm = $this->grand_total_investment / ($this->guaranteed_impressions_count + $this->bua_impressions_count);
    }
}
