<?php
/*
 * Copyright 2020 (c) Neo-OOH - All Rights Reserved
 * Unauthorized copying of this file, via any medium is strictly prohibited
 * Proprietary and confidential
 * Written by Valentin Dufois <vdufois@neo-ooh.com>
 *
 * @neo/api - ImportContractDataJob.php
 */

namespace Neo\Jobs\Contracts;

use Illuminate\Bus\Queueable;
use Illuminate\Contracts\Queue\ShouldQueue;
use Illuminate\Foundation\Bus\Dispatchable;
use Illuminate\Queue\InteractsWithQueue;
use Illuminate\Queue\SerializesModels;
use Illuminate\Support\Collection;
use JsonException;
use Neo\Enums\ProductsFillStrategy;
use Neo\Models\Advertiser;
use Neo\Models\Client;
use Neo\Models\Contract;
use Neo\Models\ContractFlight;
use Neo\Models\ContractLine;
use Neo\Models\Product;
use Neo\Resources\Contracts\CPCompiledFlight;
use Neo\Resources\Contracts\FlightType;
use Neo\Services\Odoo\Models\Contract as OdooContract;
use Neo\Services\Odoo\Models\OrderLine;
use Neo\Services\Odoo\OdooConfig;
use Symfony\Component\Console\Output\ConsoleOutput;
use function Ramsey\Uuid\v4;

class ImportContractDataJob implements ShouldQueue {
    use Dispatchable, InteractsWithQueue, Queueable, SerializesModels;

    public function __construct(protected int               $contractId,
                                protected OdooContract|null $odooContract = null) {
    }

    public function handle(): void {
        $output = new ConsoleOutput();

        /** @var Contract|null $contract */
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
            "odoo_id" => $this->odooContract->partner_id[0],
        ], [
            "name" => $this->odooContract->partner_id[1],
        ]);

        $output->writeln($contract->contract_id . ": Set client to $client->name (#$client->id))");

        $advertiser = null;

        if ($this->odooContract->analytic_account_id) {
            /** @var Advertiser $advertiser */
            $advertiser = Advertiser::query()->firstOrCreate([
                "odoo_id" => $this->odooContract->analytic_account_id[0],
            ], [
                "name" => $this->odooContract->analytic_account_id[1],
            ]);

            $output->writeln($contract->contract_id . ": Set advertiser to $advertiser->name (#$advertiser->id))");
        }

        $contract->external_id   = $this->odooContract->id;
        $contract->advertiser_id = $advertiser?->getKey();
        $contract->client_id     = $client->getKey();
        $contract->save();

        // Check if the contract has a compiled plan attached to it. If yes, import and store it
        $contractAttachment = $this->odooContract->getAttachment($contract->getAttachedPlanName());

        if ($contractAttachment) {
            $contract->storePlan($contractAttachment->datas);
            $contract->has_plan = true;
            $contract->save();
        }

        // Now, we pull all the lines from the contract and put them in their own flights
        $flights    = $this->getFlightsFromPlan($contract);
        $orderLines = collect();
        $chunkSize  = 50;

        do {
            $hasMore = false;

            $lines = OrderLine::all($odooClient, [
                ["order_id", '=', $this->odooContract->id],
                ["is_linked_line", '!=', 1],
            ], $chunkSize, $orderLines->count());

            if ($lines->count() === $chunkSize) {
                $hasMore = true;
            }

            $orderLines = $orderLines->merge($lines);

            $output->writeln($contract->contract_id . ": Received {$orderLines->count()} lines...");

        } while ($hasMore);

        $contractLines              = [];
        $expectedDigitalImpressions = 0;

        $output->writeln($contract->contract_id . ": Importing {$orderLines->count()} lines in the contract...");
        $products = Product::query()
                           ->whereIn("external_variant_id", $orderLines->pluck("product_id.0")->unique())
                           ->with(["category"])
                           ->get();

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

            if ($product->is_bonus) {
                $type = 'bua';
            } else if ($orderLine->discount > 99.9) {
                $type = 'bonus';
            }

            /** @var ContractFlight|null $flight */
            $flight = $flights->filter(function (ContractFlight $flight) use ($type, $orderLine) {
                return $flight->start_date->toDateString() === $orderLine->rental_start
                    && $flight->end_date->toDateString() === $orderLine->rental_end
                    && $flight->type === $type;
            })->first();

            if ($flight === null) {
                $flight = ContractFlight::query()->firstOrCreate([
                    "contract_id" => $contract->getKey(),
                    "uid"         => v4(),
                    "type"        => $type,
                    "start_date"  => $orderLine->rental_start,
                    "end_date"    => $orderLine->rental_end,
                ]);
                $flights->push($flight);
            }

            $line = [
                "product_id"    => $product->getKey(),
                "flight_id"     => $flight->getKey(),
                "external_id"   => $orderLine->getKey(),
                "spots"         => $orderLine->product_uom_qty,
                "media_value"   => $orderLine->price_unit * $orderLine->nb_weeks * $orderLine->nb_screen * $orderLine->product_uom_qty,
                "discount"      => $orderLine->discount,
                "discount_type" => "relative",
                "price"         => $orderLine->price_subtotal,
                "traffic"       => 0,
                "impressions"   => $orderLine->connect_impression ?: $orderLine->impression,
            ];

            $contractLines[] = $line;

            // If the line is guaranteed and for a digital product, sum its impressions
            if ($product->category->fill_strategy === ProductsFillStrategy::digital &&
                $flight->type !== FlightType::BUA) {
                $expectedDigitalImpressions += $line["impressions"];
            }
        }

        if (count($contractLines) === 0) {
            // If there is no lines, in the contract, we delete it
            $output->writeln($contract->contract_id . ": No orderlines found, deleting contract");
            $contract->delete();
            return;
        }

        ContractLine::query()->insertOrIgnore($contractLines);
        $output->writeln($contract->contract_id . ": {$flights->count()} Flights attached.");

        // Update contract start date, end date and expected impressions
        $startDate = $flights
            ->where("type", "!=", FlightType::BUA)
            ->whenEmpty(function () use ($flights) {
                return $flights->where("type", "=", FlightType::BUA);
            })
            ->sortBy("start_date")
            ->first()?->start_date;

        $endDate = $flights
            ->where("type", "!=", FlightType::BUA)
            ->whenEmpty(function () use ($flights) {
                return $flights->where("type", "=", FlightType::BUA);
            })
            ->sortBy("end_date", SORT_REGULAR, "desc")
            ->first()?->end_date;

        $contract->start_date           = $startDate;
        $contract->end_date             = $endDate;
        $contract->expected_impressions = $expectedDigitalImpressions;
        $contract->save();
    }

    /**
     * @throws JsonException
     */
    public function getFlightsFromPlan(Contract $contract): Collection {
        // Get the compiled plan from the contract
        $plan = $contract->getStoredPlanAttribute();

        if (!$plan) {
            return collect();
        }

        $flights = collect();

        /** @var CPCompiledFlight $flight */
        foreach ($plan->flights as $i => $flight) {
            $flights->push(ContractFlight::query()->updateOrCreate([
                "contract_id" => $contract->getKey(),
                "uid"         => $flight->id,
            ], [
                "name"       => $flight->name ?? "Flight #" . $i,
                "type"       => $flight->type->value,
                "start_date" => $flight->start_date,
                "end_date"   => $flight->end_date,
            ]));
        }

        // Empty all flights lines as they will all be re-imported
        $flights->each(fn(ContractFlight $flight) => $flight->lines()->delete());

        // Remove any flights attached with the contract that are not part of the ones just created
        $contract->flights()->whereNotIn("id", $flights->pluck("id"))->delete();

        return $flights;
    }
}
