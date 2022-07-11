<?php
/*
 * Copyright 2020 (c) Neo-OOH - All Rights Reserved
 * Unauthorized copying of this file, via any medium is strictly prohibited
 * Proprietary and confidential
 * Written by Valentin Dufois <vdufois@neo-ooh.com>
 *
 * @neo/api - Customer.php
 */

namespace Neo\Documents\Contract;

use Neo\Documents\Exceptions\MissingColumnException;

class Customer {
    public string $name;
    public string $phone;
    public string $email;
    public string $parent_name;
    public string $account;
    public string $reference;

    public string $payable_account = "";

    public string $address_street;
    public string $address_street_2;
    public string $address_city;
    public string $address_state;
    public string $address_state_name;
    public string $address_country;

    public function __construct(array $record) {
        $expectedColumns = ["partner_id/name",
                            "partner_id/parent_name",
                            "partner_id/phone",
                            "partner_id/email",
                            "analytic_account_id/display_name",
                            "partner_id/street",
                            "partner_id/city",
                            "partner_id/state_id/name",
                            "partner_id/country_id/name"];

        foreach ($expectedColumns as $col) {
            if (!array_key_exists($col, $record)) {
                throw new MissingColumnException($col);
            }
        }

        [
            "partner_id/name"        => $this->name,
            "partner_id/parent_name" => $this->parent_name,
            "partner_id/phone"       => $this->phone,
            "partner_id/email"       => $this->email,

            "analytic_account_id/display_name" => $this->account,

            "partner_id/street"          => $this->address_street,
            "partner_id/city"            => $this->address_city,
            "partner_id/state_id/name"   => $this->address_state_name,
            "partner_id/country_id/name" => $this->address_country,
        ] = $record;

        if (array_key_exists("partner_invoice_id/email", $record)) {
            $this->payable_account = $record["partner_invoice_id/email"];
        }

    }

    public function getAddress() {
        return "$this->address_street, $this->address_city, $this->address_state_name";
    }
}
