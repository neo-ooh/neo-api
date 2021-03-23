<?php
/*
 * Copyright 2020 (c) Neo-OOH - All Rights Reserved
 * Unauthorized copying of this file, via any medium is strictly prohibited
 * Proprietary and confidential
 * Written by Valentin Dufois <vdufois@neo-ooh.com>
 *
 * @neo/api - OrderLine.php
 */

namespace Neo\Documents\Contract;

use Neo\Documents\Network;

class OrderLine {
    public string $orderLine;
    public string $description;
    public float $discount;
    public string $date_start;
    public string $date_end;
    public int $impressions;
    public string $traffic;

    public string $market;
    public string $market_name;

    public int $nb_weeks;
    public int $nb_screens;
    public int $quantity;

    public bool $is_production;
    public string $product;
    public string $product_category;
    public string $product_description;
    public string $product_rental;
    public string $product_type;
    public float $unit_price;

    public float $media_value;
    public float $net_investment;

    public float $subtotal;
    public float $price_tax;

    public string $property_type;
    public string $property_name;
    public string $property_lat;
    public string $property_lng;
    public string $property_city;


    public function __construct(array $record) {

//        $this->orderLine           = $record["Order Lines"];
        $this->description         = $record["order_line/name"];

        $this->discount            = (float)($record["order_line/discount"] ?? 0);
        $this->date_start          = $record["order_line/rental_start"];
        $this->date_end            = $record["order_line/rental_end"];
        $this->impressions         = (int)($record["order_line/impression"] ?? 0);
        $this->traffic             = $record["order_line/traffic"];
        $this->market              = $record["order_line/market_id"];
        $this->market_name         = $record["order_line/market_id/name"];
        $this->nb_weeks            = (float)($record["order_line/nb_weeks"] ?? 0);
        $this->nb_screens          = (int)($record["order_line/nb_screen"] ?? 0);
        $this->quantity            = $record["order_line/product_uom_qty"];

        $this->is_production       = $record["Order Lines/Product/Production"] === "VRAI";

        $this->product             = $record["order_line/product_id"];
        $this->product_category    = $record["order_line/product_id/description"];
//        $this->product_description = $record["order_line/product_id/description"];
        $this->product_rental      = $record["order_line/is_product_rentable"];
        $this->product_type        = $record["order_line/product_type"];

        $this->unit_price          = (float)$record["order_line/price_unit"];
        $this->subtotal            = (float)$record["order_line/price_subtotal"];
        $this->price_tax           = (float)$record["order_line/price_tax"];

        $this->property_type       = $record["Order Lines/Property/Property Type"];

        $this->property_name       = $record["order_line/shopping_center_id/name"];
        $this->property_city       = $record["order_line/shopping_center_id/cityw"];
        $this->property_lat        = $record["order_line/shopping_center_id/partner_latitude"];
        $this->property_lng        = $record["order_line/shopping_center_id/partner_longitude"];

        $this->media_value    = $this->unit_price * $this->quantity * $this->nb_screens * $this->nb_weeks;
        $this->net_investment = $this->media_value * (1 - $this->discount / 100);

        if ($this->isGuaranteedBonus() || $this->isBonusUponAvailability()) {
            $this->net_investment = 0;
        }
    }

    public function isNetwork(string $network) {
        switch ($network) {
            case Network::NEO_SHOPPING:
                return strtolower($this->property_type) === 'shopping';
            case Network::NEO_OTG:
                return strtolower($this->property_type) === 'service station' || strtolower($this->property_type) === 'c-store';
            case Network::NEO_FITNESS:
                return strtolower($this->property_type) === 'fitness';
        }

        return false;
    }

    public function isGuaranteedPurchase(): int {
        return (float)$this->discount < 100 && !str_ends_with($this->product, "(bonus)");
    }

    public function isGuaranteedBonus(): int {
        return (int)$this->discount === 100;
    }

    public function isBonusUponAvailability(): int {
        return str_ends_with($this->product, "(bonus)");
    }
}
