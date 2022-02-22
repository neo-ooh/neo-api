<?php
/*
 * Copyright 2020 (c) Neo-OOH - All Rights Reserved
 * Unauthorized copying of this file, via any medium is strictly prohibited
 * Proprietary and confidential
 * Written by Valentin Dufois <vdufois@neo-ooh.com>
 *
 * @neo/api - ImportContractJob.php
 */

namespace Neo\Jobs\Contracts;

use Illuminate\Bus\Queueable;
use Illuminate\Contracts\Queue\ShouldQueue;
use Illuminate\Foundation\Bus\Dispatchable;
use Illuminate\Queue\InteractsWithQueue;
use Illuminate\Queue\SerializesModels;
use Neo\Models\Advertiser;
use Neo\Models\Client;
use Neo\Models\Contract;
use Neo\Models\ContractFlight;
use Neo\Models\ContractLine;
use Neo\Models\Product;
use Neo\Services\Odoo\Models\Contract as OdooContract;
use Neo\Services\Odoo\Models\OrderLine;
use Neo\Services\Odoo\OdooConfig;
use Symfony\Component\Console\Output\ConsoleOutput;

class ImportContractJob implements ShouldQueue {
    use Dispatchable, InteractsWithQueue, Queueable, SerializesModels;

    public function __construct(protected int               $contractId,
                                protected OdooContract|null $odooContract = null) {
    }

    public function handle() {
        $output = new ConsoleOutput();

        /** @var Contract $contract */
        $contract = Contract::query()->find($this->contractId);

        if (!$contract) {
            // contract not found!
            return;
        }

        $odooClient = OdooConfig::fromConfig()->getClient();

        // Check if the contract is present in ODOO
        if ($this->odooContract === null) {
            $this->odooContract = OdooContract::findByName($odooClient, $contract->contract_id);
        }

        /** @var Client $client */
        $client = Client::query()->firstOrCreate([
            "external_id" => $this->odooContract->partner_id[0]
        ], [
            "name" => $this->odooContract->partner_id[1]
        ]);

        $output->writeln($contract->contract_id . ": Set client to $client->name (#$client->id))");

        $advertiser = null;

        if ($this->odooContract->analytic_account_id) {
            /** @var Advertiser $advertiser */
            $advertiser = Advertiser::query()->firstOrCreate([
                "external_id" => $this->odooContract->analytic_account_id[0]
            ], [
                "name" => $this->odooContract->analytic_account_id[1]
            ]);

            $output->writeln($contract->contract_id . ": Set advertiser to $advertiser->name (#$advertiser->id))");
        }

        $contract->external_id   = $this->odooContract->id;
        $contract->advertiser_id = $advertiser?->getKey();
        $contract->client_id     = $client->getKey();
        $contract->save();

        // Now, we pull all the lines from the contract and put them in their own flights
        $flights = collect();

        $orderLines    = OrderLine::findBy($odooClient, "order_id", $this->odooContract->id)->where("is_linked_line", "!=", 1);
        $contractLines = [];

        $output->writeln($contract->contract_id . ": Importing {$orderLines->count()} lines in the contract...");
        $products = Product::query()->whereIn("external_variant_id", $orderLines->pluck("product_id.0")->unique())->get();
        /** @var OrderLine $orderLine */
        foreach ($orderLines as $orderLine) {
            if ($orderLine->is_linked_line) {
                // Ignore linked lines
                continue;
            }

            /** @var Product|null $product */
            $product = $products->firstWhere("external_variant_id", "=", $orderLine->product_id[0]);

            if ($product === null) {
                // Unknown product, ignore
                continue;
            }

            // Infer order line type
            $type = 'guaranteed';

            if ($product && $product->is_bonus) {
                $type = 'bua';
            } else if ($orderLine->discount === 100) {
                $type = 'bonus';
            }

            $flight = $flights->filter(function (ContractFlight $flight) use ($type, $orderLine) {
                return $flight->start_date->toDateString() === $orderLine->rental_start
                    && $flight->end_date->toDateString() === $orderLine->rental_end
                    && $flight->type === $type;
            })->first();

            if ($flight === null) {
                $flight = new ContractFlight([
                    "contract_id" => $contract->getKey(),
                    "type"        => $type,
                    "start_date"  => $orderLine->rental_start,
                    "end_date"    => $orderLine->rental_end,
                ]);
                $flight->save();
                $flights->push($flight);
            }

            $contractLines[] = [
                "product_id"    => $product->getKey(),
                "flight_id"     => $flight->getKey(),
                "external_id"   => $orderLine->getKey(),
                "spots"         => $orderLine->product_uom_qty,
                "media_value"   => 0,
                "discount"      => $orderLine->discount,
                "discount_type" => "relative",
                "price"         => $orderLine->price_subtotal,
                "traffic"       => 0,
                "impressions"   => $orderLine->connect_impression ?: $orderLine->impression
            ];
        }

        if (count($contractLines) === 0) {
            // If there is no lines, in the contract, we delete it
            $output->writeln($contract->contract_id . ": No orderlines found, deleting contract");
            $contract->delete();
            return;
        }

        ContractLine::query()->insertOrIgnore($contractLines);
        $output->writeln($contract->contract_id . ": {$flights->count()} Flights attached.");

        // Remove any flights attached with the contract that are not part of the current run
        $contract->flights()->whereNotIn("id", $flights->pluck("id"))->delete();
    }
}
