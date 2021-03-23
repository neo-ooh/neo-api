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
        $this->reference              = $record["name"];
        $this->date                   = $record["date_order"];
        $this->salesperson            = $record["user_id"];
        $this->status                 = $record["state"];
        $this->campaign_name          = $record["campaign_name"];
        $this->campaign_start         = $record["campaign_ids/date_start"];
        $this->campaign_end           = $record["campaign_ids/date_end"];
        $this->bonus_impression       = $record["bonus_impression"];
        $this->amount_before_discount = $record["amount_undiscounted"];
        $this->discount_amount        = $record["amount_discount"];
        $this->taxes                  = $record["amount_tax"];
        $this->total                  = $record["amount_total"];
        $this->traffic                = $record["traffic"];
        $this->show_investment        = !!$record["investment"];
    }
}
