<?php

namespace Neo\Documents\Contract;

class OrderLine {
    public string $orderLine;
    public string $description;
    public string $discount;
    public string $date_start;
    public string $date_end;
    public string $impression;
    public string $traffic;

    public string $market;
    public string $market_name;

    public string $nb_weeks;
    public string $nb_screens;
    public string $quantity;

    public string $product;
    public string $product_description;
    public string $product_rental;
    public string $product_type;
    public string $unit_price;

    public string $subtotal;
    public string $total_tax;

    public string $property_name;
    public string $property_lat;
    public string $property_lng;
    public string $property_city;


    public function __construct(array $record) {
        [
            "Order Lines" => $this->orderLine,
            "Order Lines/Description" => $this->description,
            "Order Lines/Discount (%)" => $this->discount,
            "Order Lines/" => $this->date_start,
            "Order Lines/" => $this->date_end,
            "Order Lines/" => $this->impression,
            "Order Lines/" => $this->traffic,
            "Order Lines/" => $this->market,
            "Order Lines/" => $this->market_name,
            "Order Lines/" => $this->nb_weeks,
            "Order Lines/" => $this->nb_screens,
            "Order Lines/" => $this->quantity,
            "Order Lines/" => $this->product,
            "Order Lines/" => $this->product_description,
            "Order Lines/" => $this->product_rental,
            "Order Lines/" => $this->subtotal,
            "Order Lines/" => $this->total_tax,
            "Order Lines/" => $this->property_name,
            "Order Lines/" => $this->property_lat,
            "Order Lines/" => $this->property_lng,
            "Order Lines/" => $this->property_city,
        ] = $record;
    }

}
