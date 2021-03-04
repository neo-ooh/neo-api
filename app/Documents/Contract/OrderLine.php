<?php

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
    public float $total_tax;

    public string $property_type;
    public string $property_name;
    public string $property_lat;
    public string $property_lng;
    public string $property_city;


    public function __construct(array $record) {

//        $this->orderLine           = $record["Order Lines"];
//        $this->traffic             = $record["Order Lines/Traffic"];
//        $this->property_lat        = $record["Order Lines/Property/Geo Latitude"];
//        $this->property_lng        = $record["Order Lines/Property/Geo Longitude"];

        $this->description         = $record["Order Lines/Description"];
        $this->discount            = (float)($record["Order Lines/Discount (%)"] ?? 0);
        $this->date_start          = $record["Order Lines/Start date"];
        $this->date_end            = $record["Order Lines/End date"];
        $this->impressions         = (int)($record["Order Lines/Impression"] ?? 0);
        $this->market              = $record["Order Lines/Market"];
        $this->market_name         = $record["Order Lines/Market/Name"];
        $this->nb_weeks            = (int)($record["Order Lines/Nb Weeks"] ?? 0);
        $this->nb_screens          = (int)($record["Order Lines/Nb Screen/Poster"] ?? 0);
        $this->quantity            = $record["Order Lines/Quantity"];
        $this->product             = $record["Order Lines/Product"];
        $this->is_production       = $record["Order Lines/Product/Production"] === "VRAI";
        $this->product_category    = $record["Order Lines/Product/Product Category"];
        $this->product_description = $record["Order Lines/Product/Description"];
        $this->product_rental      = $record["Order Lines/Rental Product"];
        $this->product_type        = $record["Order Lines/Type of Product"];
        $this->unit_price          = (float)$record["Order Lines/Unit Price"];
        $this->subtotal            = (float)$record["Order Lines/Subtotal"];
        $this->total_tax           = (float)$record["Order Lines/Total Tax"];
        $this->property_type       = $record["Order Lines/Property/Property Type"];
        $this->property_name       = $record["Order Lines/Property/Name"];
        $this->property_city       = $record["Order Lines/Property/City"];

        $this->media_value    = $this->unit_price * $this->quantity * $this->nb_screens * $this->nb_weeks;
        $this->net_investment = $this->media_value * (1 - $this->discount / 100);

        if($this->isGuaranteedBonus() || $this->isBonusUponAvailability()) {
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
