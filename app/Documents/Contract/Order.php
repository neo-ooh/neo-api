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

use Arr;
use Carbon\Carbon;
use Illuminate\Support\Collection;
use Neo\Documents\Exceptions\MissingColumnException;
use Neo\Documents\Network;

class Order {
    public string $locale;
    public string $company_name;
    public string $reference;
    public string $date;
    public string $salesperson;
    public string $salesperson_phone;
    public string $salesperson_email;
    public string $status;

    public string $campaigns;
    public string $campaign_name;
    public string $campaign_start;
    public string $campaign_end;

    public string $bonus_impression;

    public float $amount_before_discount;
    public float $discount_amount;
    public float $taxes;
    public float $total;
    public int $traffic;

    public string $show_investment;

    public Collection $orderLines;
    public Collection $productionLines;

    public bool $use_invoice_plan = false;
    public Collection $invoice_plan_steps;

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

    /**
     * @throws MissingColumnException
     */
    public function __construct(array $record) {
        $expectedColumns = ["partner_id/lang",
                            "company_id/name",
                            "name",
                            "date_order",
                            "user_id",
                            "user_id/phone",
                            "user_id/email",
                            "state",
                            "campaign_name",
                            "amount_undiscounted",
                            "amount_tax",
                            "amount_total",
                            "traffic",
                            "investment"];

        foreach ($expectedColumns as $col) {
            if (!array_key_exists($col, $record)) {
                throw new MissingColumnException($col);
            }
        }

        $this->locale            = substr($record["partner_id/lang"], 0, 2);
        $this->company_name      = $record["company_id/name"];
        $this->reference         = $record["name"];
        $this->date              = $record["date_order"];
        $this->salesperson       = $record["user_id"];
        $this->salesperson_phone = $record["user_id/phone"];
        $this->salesperson_email = $record["user_id/email"];
        $this->status            = $record["state"];
        $this->campaign_name     = $record["campaign_name"];
//        $this->campaign_start         = $record["campaign_ids/date_start"];
//        $this->campaign_end           = $record["campaign_ids/date_end"];
//        $this->bonus_impression       = $record["bonus_impression"];
        $this->amount_before_discount = (float)$record["amount_undiscounted"];
//        $this->discount_amount        = $record["amount_discount"];
        $this->taxes           = (float)$record["amount_tax"];
        $this->total           = (float)$record["amount_total"];
        $this->traffic         = (int)$record["traffic"];
        $this->show_investment = $record["investment"] === "True";

        $this->orderLines      = new Collection();
        $this->productionLines = new Collection();

        $this->use_invoice_plan   = data_get($record, "use_invoice_plan", false) === "True";
        $this->invoice_plan_steps = new Collection();

        if (Arr::exists($record, "invoice_plan_ids/invoice_move_ids/amount_untaxed")) {
            $this->addInvoicePlanStep($record);
        }

    }

    public function addInvoicePlanStep(array $record) {
        $this->invoice_plan_steps->add([
            "step"   => $record["invoice_plan_ids/invoice_move_ids/nb_in_plan"],
            "date"   => Carbon::parse($record["invoice_plan_ids/plan_date_start"])->locale($this->locale),
            "amount" => (float)$record["invoice_plan_ids/invoice_move_ids/amount_untaxed"],
        ]);
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

    public function getAudienceExtensionLines(): Collection {
        return $this->orderLines->filter(fn($order) => $order->isExtensionStrategy());
    }

    public function getAdServerLines(): Collection {
        return $this->orderLines->filter(fn($order) => $order->isAdServerProduct());
    }

    public function getShoppingOrders() {
        return $this->orderLines->filter(fn($order) => $order->isNetwork(Network::NEO_SHOPPING));
    }

    public function getOTGOrders() {
        return $this->orderLines->filter(fn($order) => $order->isNetwork(Network::NEO_OTG));
    }

    public function getFitnessOrders() {
        return $this->orderLines->filter(fn($order) => $order->isNetwork(Network::NEO_FITNESS));
    }

    // Compute values
    public function computeValues(): void {
        // Guaranteed orders
        $guaranteedOrders = $this->getGuaranteedOrders();
        $extensionOrders  = $this->getAudienceExtensionLines()->merge($this->getAdServerLines());

        if ($guaranteedOrders->isNotEmpty() || $extensionOrders->isNotEmpty()) {
            $this->guaranteed_impressions_count = $guaranteedOrders->sum("impressions") + $extensionOrders->sum("impressions");
            $this->guaranteed_value             = $guaranteedOrders->sum("media_value") + $extensionOrders->sum("media_value");
            $this->guaranteed_investment        = $guaranteedOrders->sum("net_investment") + $extensionOrders->sum("net_investment");


            $this->guaranteed_discount = ($this->guaranteed_value - $this->guaranteed_investment) / $this->guaranteed_value * 100;
        }

        // Bua orders
        $buaOrders     = $this->getBuaOrders();
        $this->has_bua = $buaOrders->isNotEmpty();

        if ($this->has_bua) {
            $this->bua_impressions_count = $buaOrders->sum("impressions");
            $this->bua_value             = $buaOrders->sum("media_value");
            $this->bua_discount          = $buaOrders->sum("discount") / $buaOrders->count();
            $this->bua_investment        = $buaOrders->sum("net_investment");
        }

        if ($this->has_bua || $guaranteedOrders->isNotEmpty() || $extensionOrders->isNotEmpty()) {
            // Orders totals
            $this->potential_value        = $this->guaranteed_value + $this->bua_value;
            $this->grand_total_investment = $this->guaranteed_investment + $this->bua_investment;
            $this->potential_discount     = (1 - $this->grand_total_investment / $this->potential_value) * 100;

            // Only calculate the cpm if
            if (($this->guaranteed_impressions_count + $this->bua_impressions_count) > 0) {
                $this->cpm = 1000 * $this->grand_total_investment / ($this->guaranteed_impressions_count + $this->bua_impressions_count);
            }
        }

        // Production costs
        $this->production_costs = $this->productionLines->sum("subtotal");

        $this->net_investment = $this->grand_total_investment + $this->production_costs;

        // Sort payment plan in correct order
        $this->invoice_plan_steps->sortBy("step");
    }
}
