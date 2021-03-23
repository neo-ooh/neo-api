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

class Customer {
    public string $name;
    public string $parent_name;
    public string $account;
    public string $company;
    public string $reference;

    public string $address_street;
    public string $address_street_2;
    public string $address_city;
    public string $address_state;
    public string $address_state_name;
    public string $address_country;

    public function __construct(array $record) {
        [
            "partner_id"                       => $this->name,

            "Customer/Parent name"             => $this->parent_name,

            "company_id/name"                  => $this->company,
            "analytic_account_id/display_name" => $this->account,

            "partner_id/street"          => $this->address_street,
            "partner_id/street2"         => $this->address_street_2,
            "partner_id/city"            => $this->address_city,
            "partner_id/state_id/name"   => $this->address_state_name,
            "partner_id/country_id/name" => $this->address_country,
        ] = $record;
    }
}
