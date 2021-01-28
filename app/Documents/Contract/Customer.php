<?php

namespace Neo\Documents\Contract;

class Customer {
    public string $name;
    public string $account;
    public string $reference;

    public string $address_street;
    public string $address_street_2;
    public string $address_city;
    public string $address_state;
    public string $address_state_name;
    public string $address_country;

    public function __construct(array $record) {
        [
            "Customer"                      => $this->name,
            "Analytic Account/Display Name" => $this->account,
            "Customer Reference"            => $this->reference,

            "Customer/Street"           => $this->address_street,
            "Customer/Street2"          => $this->address_street_2,
            "Customer/City"             => $this->address_city,
            "Customer/State"            => $this->address_state,
            "Customer/State/State Name" => $this->address_state_name,
            "Customer/Country"          => $this->address_country,
        ] = $record;
    }
}
