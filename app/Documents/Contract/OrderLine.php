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

use Carbon\Carbon;
use Illuminate\Support\Facades\App;
use Illuminate\Support\Str;
use InvalidArgumentException;
use Neo\Documents\Exceptions\MissingColumnException;
use Neo\Documents\Network;

class OrderLine {

    public const TYPE_GUARANTEED_PURCHASE = 1;
    public const TYPE_GUARANTEED_BONUS = 2;
    public const TYPE_BONUS_UPON_AVAIL = 3;
    public const TYPE_EXTENSION_STRATEGY = 4;

    public const COVID_TRAFFIC_FACTOR = [
        "Digital - Vertical"    => 0.5,
        "Digital - Horizontal"  => 0.7,
        "Digital - Spectacular" => 0.7,
        "Column poster"         => 0.5,
        "Mall poster"           => 0.5,
        "Mall+ poster"          => 0.5,
        "Rotating column"       => 0.5,
        "Sky poster"            => 0.5,
        "Unik poster"           => 0.5,
        "Banner"               => 0.5,
    ];

    public string $orderLine;
    public string $description;
    public float $discount;

    public string $date_start;
    public string $date_end;
    public float $nb_weeks;
    public string $rangeLengthString;

    public int $impressions;
    public string $traffic;
    public int $covid_impressions = 0;
    public float $covid_cpm = 0;

    public string $market;
    public string $market_name;
    public string $market_order;

    public string $audience_segment;
    public string $impression_format;

    public int $nb_screens;
    public float $quantity;

    public bool $is_production;
    public string $product;
    public string $product_category;
    public string $product_description;
    public string $product_rental;
    public bool $isMobileProduct = false;
    public string $product_type;
    public float $unit_price;
    public float $cpm;

    public float $media_value;
    public float $net_investment;

    public float $subtotal;
    public float $price_tax;

    public string $property_type;
    public string $property_name;
    public string $property_state;
    public float $property_annual_traffic;
    public string $property_lat;
    public string $property_lng;
    public string $property_city;

    protected int $type;

    const PERIOD_FORMAT_FR = 'j F';
    const PERIOD_FORMAT_EN = 'F jS';


    public function __construct(array $record) {
        $expectedColumns = ["order_line/name",
                            "order_line/discount",
                            "order_line/rental_start",
                            "order_line/rental_end",
                            "order_line/impression",
                            "order_line/traffic",
                            //                            "order_line/impression_format",
                            //                            "order_line/cpm",
                            "order_line/market_id",
                            "order_line/market_id/name",
                            //                            "order_line/market_name",
                            "order_line/market_id/sequence",
                            "order_line/nb_weeks",
                            "order_line/nb_screen",
                            "order_line/product_uom_qty",
                            "order_line/product_id/production",
                            "order_line/product_id/categ_id",
                            "order_line/product_id",
                            "order_line/product_id/description",
                            //                            "order_line/is_mobile_product",
                            "order_line/is_product_rentable",
                            "order_line/product_type",
                            "order_line/price_unit",
                            "order_line/price_subtotal",
                            "order_line/price_tax",
                            //                            "order_line/segment",
                            "order_line/shopping_center_id/annual_traffic",
                            "order_line/shopping_center_id/center_type",
                            "order_line/shopping_center_id/city",
                            "order_line/shopping_center_id/name",
                            "order_line/shopping_center_id/partner_latitude",
                            "order_line/shopping_center_id/partner_longitude",
                            "order_line/shopping_center_id/state_id"];

        foreach ($expectedColumns as $col) {
            if (!array_key_exists($col, $record)) {
                throw new MissingColumnException($col);
            }
        }

        $this->description = $record["order_line/name"];

        $this->discount = (float)($record["order_line/discount"] ?? 0);

        $this->date_start        = $record["order_line/rental_start"];
        $this->date_end          = $record["order_line/rental_end"];
        $this->nb_weeks          = (float)($record["order_line/nb_weeks"] ?? 0);
        $this->rangeLengthString = $this->getPeriodString();

        $this->impressions  = (int)($record["order_line/impression"] ?? 0);
        $this->traffic      = $record["order_line/traffic"];
        $this->market       = $record["order_line/market_id"];
        $this->market_name  = $record["order_line/market_id/name"];
        $this->market_order = $record["order_line/market_id/sequence"];
        $this->nb_screens   = (int)($record["order_line/nb_screen"] ?? 0);
        $this->quantity     = $record["order_line/product_uom_qty"];

        $this->is_production = $record["order_line/product_id/production"] === "True";

        $this->product             = $record["order_line/product_id"];
        $this->product_description = $record["order_line/product_id/description"];
        $this->product_category    = $record["order_line/product_id/categ_id"];
        $this->product_rental      = $record["order_line/is_product_rentable"] === "True";

        if (array_key_exists("order_line/is_mobile_product", $record)) {
            $this->isMobileProduct = $record["order_line/is_mobile_product"] === "True";
        }
        $this->product_type = $record["order_line/product_type"];

        $this->unit_price = (float)$record["order_line/price_unit"];
        $this->subtotal   = (float)$record["order_line/price_subtotal"];
        $this->price_tax  = (float)$record["order_line/price_tax"];

        $this->property_type = $record["order_line/shopping_center_id/center_type"];

        $this->property_name           = $record["order_line/shopping_center_id/name"];
        $this->property_city           = $record["order_line/shopping_center_id/city"];
        $this->property_state          = $record["order_line/shopping_center_id/state_id"];
        $this->property_lat            = $record["order_line/shopping_center_id/partner_latitude"];
        $this->property_lng            = $record["order_line/shopping_center_id/partner_longitude"];
        $this->property_annual_traffic = (float)$record["order_line/shopping_center_id/annual_traffic"];

        $this->media_value = $this->unit_price * $this->quantity * $this->nb_screens * $this->nb_weeks;

        $this->net_investment = $this->subtotal;

        // Set the order type before modifying anything;
        $this->inferOrderType();

        if ($this->isGuaranteedBonus() || $this->isBonusUponAvailability()) {
            $this->net_investment = 0;
        }

        if ($this->isBonusUponAvailability() && Str::endsWith(trim($this->description), "(bonus)")) {
            $this->product = substr($this->description, 0, -7);
        }

        if ($this->isExtensionStrategy()) {
            $this->audience_segment  = $record["order_line/segment"];
            $this->impression_format = $record["order_line/impression_format"];
            $this->market_name       = $record["order_line/market_name"];
            $this->cpm               = (float)$record["order_line/cpm"];
        }

        if ($this->impressions > 0 && isset($this->product_category) && $this->isNetwork(Network::NEO_SHOPPING) && array_key_exists($this->product_category, static::COVID_TRAFFIC_FACTOR)) {
            $this->covid_impressions = $this->impressions * static::COVID_TRAFFIC_FACTOR[$this->product_category];
             $this->covid_cpm = ($this->net_investment / $this->covid_impressions) * 1000;
        } else {
            // for lins without covid specific impressions and cpm, we simply carry on the regular ones
            $this->covid_impressions = $this->impressions;
            $this->covid_cpm  = $this->impressions > 0 ? ($this->net_investment / $this->impressions) * 1000 : 0;
        }
    }

    protected function inferOrderType() {
        if (str_ends_with($this->product, "(bonus)")) {
            $this->type = static::TYPE_BONUS_UPON_AVAIL;
            return;
        }

        if ((int)round($this->discount) === 100) {
            $this->type = static::TYPE_GUARANTEED_BONUS;
            return;
        }

        if ($this->isMobileProduct) {
            $this->type = static::TYPE_EXTENSION_STRATEGY;
            return;
        }

        $this->type = static::TYPE_GUARANTEED_PURCHASE;
    }

    public function isNetwork(string $network) {
        switch ($network) {
            case Network::NEO_SHOPPING:
                return strtolower($this->property_type) === 'shopping';
            case Network::NEO_OTG:
                return strtolower($this->property_type) === 'service station' || strtolower($this->property_type) === 'c-store' || strtolower($this->property_type) === 'station service' || strtolower($this->property_type) === 'dépanneur';
            case Network::NEO_FITNESS:
                return strtolower($this->property_type) === 'fitness';
        }

        return false;
    }

    public function isGuaranteedPurchase(): int {
        return $this->type === static::TYPE_GUARANTEED_PURCHASE;
    }

    public function isGuaranteedBonus(): int {
        return $this->type === static::TYPE_GUARANTEED_BONUS;
    }

    public function isBonusUponAvailability(): int {
        return $this->type === static::TYPE_BONUS_UPON_AVAIL;
    }

    public function isExtensionStrategy(): int {
        return $this->type === static::TYPE_EXTENSION_STRATEGY;
    }

    protected function getPeriodString() {
        $format = App::currentLocale() === "fr" ? static::PERIOD_FORMAT_FR : static::PERIOD_FORMAT_EN;

        $dateObj = Carbon::make($this->date_start);

        if (!$dateObj) {
            throw new InvalidArgumentException("date_start field is not a valid datetime representation.");
        }

        $dateObj->locale(App::getLocale());

        return $dateObj->translatedFormat($format) . " x " . $this->nb_weeks . " " . trans_choice("common.weeks", $this->nb_weeks);
    }
}
