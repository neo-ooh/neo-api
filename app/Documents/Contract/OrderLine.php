<?php

namespace Neo\Documents\Contract;

use Neo\Documents\Network;

class OrderLine {
    public string $orderLine;
    public string $description;
    public string $discount;
    public string $date_start;
    public string $date_end;
    public string $impressions;
    public string $traffic;

    public string $market;
    public string $market_name;

    public string $nb_weeks;
    public string $nb_screens;
    public string $quantity;

    public string $product;
    public string $product_category;
    public string $product_description;
    public string $product_rental;
    public string $product_type;
    public string $unit_price;

    public string $subtotal;
    public string $total_tax;

    public string $property_type;
    public string $property_name;
    public string $property_lat;
    public string $property_lng;
    public string $property_city;


    public function __construct(array $record) {
        [
            "Order Lines" => $this->orderLine,
            "Order Lines/Description" => $this->description,
            "Order Lines/Discount (%)" => $this->discount,
            "Order Lines/Start date" => $this->date_start,
            "Order Lines/End date" => $this->date_end,
            "Order Lines/Impression" => $this->impressions,
            "Order Lines/Traffic" => $this->traffic,
            "Order Lines/Market" => $this->market,
            "Order Lines/Market/Name" => $this->market_name,
            "Order Lines/Nb Weeks" => $this->nb_weeks,
            "Order Lines/Nb Screen/Poster" => $this->nb_screens,
            "Order Lines/Quantity" => $this->quantity,
            "Order Lines/Product" => $this->product,
            "Order Lines/Product/Product Category" => $this->product_category,
            "Order Lines/Product/Description" => $this->product_description,
            "Order Lines/Rental Product" => $this->product_rental,
            "Order Lines/Type of Product" => $this->product_type,
            "Order Lines/Unit Price" => $this->unit_price,
            "Order Lines/Subtotal" => $this->subtotal,
            "Order Lines/Total Tax" => $this->total_tax,
            "Order Lines/Property/Property Type" => $this->property_type,
            "Order Lines/Property/Name" => $this->property_name,
            "Order Lines/Property/Geo Latitude" => $this->property_lat,
            "Order Lines/Property/Geo Longitude" => $this->property_lng,
            "Order Lines/Property/City" => $this->property_city,
        ] = $record;

        $this->discount = (int)$this->discount;
    }

    public function isNetwork(string $network) {
        switch ($network) {
            case Network::NEO_SHOPPING:
                return $this->property_type === 'Shopping';
            case Network::NEO_OTG:
                return $this->property_type === 'Service Station' || $this->property_type === 'C-store';
            case Network::NEO_FITNESS:
                return $this->property_type === 'Fitness';
        }

        return false;
    }

    public function netInvestment(): int {
        return $this->unit_price * $this->quantity * (1 - (int)$this->discount / 100);
    }

    public function isGuaranteedPurchase(): int {
        return (int)$this->discount < 100 && !str_ends_with($this->product, "(bonus)");
    }

    public function isGuaranteedBonus(): int {
        return (int)$this->discount === 100 && str_ends_with($this->product, "(bonus)");
    }

    public function isBonusUponAvailability(): int {
        return (int)$this->discount === 0 && str_ends_with($this->product, "(bonus)");
    }
}
