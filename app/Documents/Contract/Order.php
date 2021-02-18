<?php

namespace Neo\Documents\Contract;

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

    public function __construct(array $record) {
        [
            "Order Reference"        => $this->reference,
            "Order Date"             => $this->date,
            "Salesperson"            => $this->salesperson,
            "Status"                 => $this->status,
//            "Campaigns"              => $this->campaigns,
            "Campaign Name"          => $this->campaign_name,
//            "Campaigns/Start date"   => $this->campaign_start,
//            "Campaigns/End date"     => $this->campaign_end,
            "Bonus Impression"       => $this->bonus_impression,
            "Amount Before Discount" => $this->amount_before_discount,
            "Discount Amount"        => $this->discount_amount,
            "Taxes"                  => $this->taxes,
            "Total"                  => $this->total,
            "Traffic"                => $this->traffic,
            "Investment"             => $this->show_investment,
        ] = $record;

        $this->show_investment = $this->show_investment === 'VRAI';
    }
}
