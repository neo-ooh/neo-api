<?php

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
            "Customer/Name"                 => $this->name,
            "Customer/Parent name"          => $this->parent_name,
            "Company"                       => $this->company,
            "Analytic Account/Display Name" => $this->account,
            //            "Customer Reference"            => $this->reference,

            "Customer/Street"               => $this->address_street,
            //            "Customer/Street2"          => $this->address_street_2,
            "Customer/City"                 => $this->address_city,
            //            "Customer/State"            => $this->address_state,
            "Customer/State/State Name"     => $this->address_state_name,
            "Customer/Country/Country Name" => $this->address_country,
        ] = $record;
    }
}
