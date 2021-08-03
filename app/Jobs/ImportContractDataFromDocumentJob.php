<?php
/*
 * Copyright 2020 (c) Neo-OOH - All Rights Reserved
 * Unauthorized copying of this file, via any medium is strictly prohibited
 * Proprietary and confidential
 * Written by Valentin Dufois <vdufois@neo-ooh.com>
 *
 * @neo/api - ImportContractDataFromDocumentJob.php
 */

namespace Neo\Jobs;

use Carbon\Carbon;
use Illuminate\Bus\Queueable;
use Illuminate\Contracts\Queue\ShouldQueue;
use Illuminate\Foundation\Bus\Dispatchable;
use Illuminate\Queue\InteractsWithQueue;
use Illuminate\Queue\SerializesModels;
use Neo\Documents\Contract\Customer;
use Neo\Documents\Contract\Order;
use Neo\Enums\Network;
use Neo\Models\Actor;
use Neo\Models\Client;
use Neo\Models\Contract;
use Neo\Models\ContractNetworkData;

class ImportContractDataFromDocumentJob implements ShouldQueue {
    use Dispatchable, InteractsWithQueue, Queueable, SerializesModels;

    public function __construct(protected Customer $customer,
                                protected Order    $order) {
    }

    public function handle() {
        // Get contract in DB matching the received one, if none exist, create one
        /** @var Contract $contract */
        $contract = Contract::query()->firstOrNew([
            "contract_id" => $this->order->reference
        ]);

        if (!$contract->exists) {
            // Attach a client to the contract
            $contract->client()->associate(Client::query()->firstOrCreate([
                "name" => $this->customer->parent_name
            ]));

            // Attach a rep to the contract
            $rep = Actor::query()->where("name", "=", $this->order->salesperson)->first();
            if ($rep !== null) {
                $contract->owner()->associate($rep);
            }

        }

        // Now, fill in some information about the contract
        $contract->advertiser_name = $this->customer->parent_name;
        $contract->executive_name = $this->order->salesperson;
        $contract->presented_to = $this->customer->name;
        $contract->start_date = $this->order->getGuaranteedOrders()->min(fn($date) => Carbon::parse($date));
        $contract->end_date = $this->order->getGuaranteedOrders()->max(fn($date) => Carbon::parse($date));
        $contract->save();

        // And now fill in informations about purchases
        $guaranteedOrders = $this->order->getGuaranteedOrders();
        $bonusOrders = $this->order->getBonusOrders();
        $buaOrders = $this->order->getBuaOrders();

        foreach (Network::getValues() as $network) {
            $data = new ContractNetworkData();
            $data->contract_id = $contract->id;
            $data->network = $network;

            $networkGuaranteedOrders = $guaranteedOrders->filter(fn($order) => $order->isNetwork($network));

            $data->has_guaranteed_reservations = $networkGuaranteedOrders->isNotEmpty();
            if($data->has_guaranteed_reservations) {
                $data->guaranteed_impressions    = $networkGuaranteedOrders->sum("impressions");
                $data->guaranteed_media_value    = $networkGuaranteedOrders->sum("media_value");
                $data->guaranteed_net_investment = $networkGuaranteedOrders->sum("net_investment");
            }

            $networkBonusOrders = $bonusOrders->filter(fn($order) => $order->isNetwork($network));

            $data->has_bonus_reservations = $networkBonusOrders->isNotEmpty();
            if($data->has_bonus_reservations) {
                $data->bonus_impressions    = $networkBonusOrders->sum("impressions");
                $data->bonus_media_value    = $networkBonusOrders->sum("media_value");
            }

            $networkBuaOrders = $buaOrders->filter(fn($order) => $order->isNetwork($network));

            $data->has_bua_reservations = $networkBuaOrders->isNotEmpty();
            if($data->has_bua_reservations) {
                $data->bua_impressions    = $networkBuaOrders->sum("impressions");
                $data->bua_media_value    = $networkBuaOrders->sum("media_value");
            }

            $data->save();
        }

        // All good
    }
}
