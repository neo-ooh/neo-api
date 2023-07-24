<?php
/*
 * Copyright 2023 (c) Neo-OOH - All Rights Reserved
 * Unauthorized copying of this file, via any medium is strictly prohibited
 * Proprietary and confidential
 * Written by Valentin Dufois <vdufois@neo-ooh.com>
 *
 * @neo/api - POPData.php
 */

namespace Neo\Documents\POP;

use Illuminate\Support\Collection;
use JetBrains\PhpStorm\ArrayShape;
use Neo\Models\ContractScreenshot;

class POPData {
    public string $contract_name;
    public int $contract_id;
    public string $locale;

    #[ArrayShape([
        'name' => 'string',
    ])]
    public array $advertiser;

    #[ArrayShape([
        'name' => 'string',
    ])]
    public array $client;

    #[ArrayShape([
        'name' => 'string',
    ])]
    public array $presented_to;

    #[ArrayShape([
        'name' => 'string',
    ])]
    public array $salesperson;

    /**
     * @var Collection<Screenshot>
     */
    public Collection $screenshots;

    /**
     * @var bool Should the screenshots be mocked up when integrated in the document ?
     */
    public bool $screenshots_mockup;

    /**
     * @var Collection<POPBuyTypeValues>
     */
    public Collection $values;

    public bool $has_guaranteed_buys = false;
    public bool $has_bonus_buys = false;
    public bool $has_bua_buys = false;

    public float $guaranteed_media_value = 0;
    public float $guaranteed_contracted_impressions = 0;
    public float $guaranteed_net_investment = 0;
    public float $guaranteed_cpm = 0;

    public float $counted_guaranteed_impressions = 0;
    public float $counted_guaranteed_media_value = 0;

    public float $counted_bonus_impressions = 0;
    public float $counted_bonus_media_value = 0;

    public float $counted_bua_impressions = 0;
    public float $counted_bua_media_value = 0;

    public float $total_counted_media_value = 0;
    public float $total_counted_impressions = 0;
    public float $total_counted_cpm = 0;

    public function __construct(array $data) {
        $this->contract_name      = $data["contract_name"];
        $this->contract_id        = $data["contract_id"];
        $this->locale             = $data["locale"];
        $this->advertiser         = [
            "name" => $data["advertiser"]["name"],
        ];
        $this->client             = [
            "name" => $data["client"]["name"],
        ];
        $this->presented_to       = [
            "name" => $data["presented_to"]["name"],
        ];
        $this->salesperson        = [
            "name" => $data["salesperson"]["name"],
        ];
        $this->screenshots_mockup = $data["screenshots_mockup"];
        $this->screenshots        = ContractScreenshot::query()
                                                      ->whereIn("id", $data["screenshots"])
                                                      ->with(["burst", "burst.location", "burst.location.display_type"])
                                                      ->get()
                                                      ->toBase()
                                                      ->map(fn(ContractScreenshot $screenshot) => new Screenshot($screenshot, $this->screenshots_mockup));

        $this->values = collect($data["values"])->map(static fn(array $values) => new POPBuyTypeValues($values))
                                                ->filter(fn(POPBuyTypeValues $values) => $values->networks->count() > 0);

        $guaranteedEntries = $this->values->where("type", "===", "guaranteed")->where("show", "=", true);

        if ($guaranteedEntries->count() > 0) {
            $this->has_guaranteed_buys = true;

            $this->guaranteed_media_value            = $guaranteedEntries->sum("media_value");
            $this->guaranteed_contracted_impressions = $guaranteedEntries->sum("contracted_impressions");
            $this->guaranteed_net_investment         = $guaranteedEntries->sum("net_investment");
            $this->guaranteed_cpm                    = $this->guaranteed_net_investment / $this->guaranteed_contracted_impressions * 1000;


            $this->counted_guaranteed_impressions = $guaranteedEntries->sum("counted_impressions");
            $this->counted_guaranteed_media_value = $this->guaranteed_media_value / $this->guaranteed_contracted_impressions * $this->counted_guaranteed_impressions;
        }

        $bonusEntries = $this->values->where("type", "===", "bonus")->where("show", "=", true);

        if ($bonusEntries->count() > 0) {
            $this->has_bonus_buys = true;


            $this->counted_bonus_impressions = $bonusEntries->sum("counted_impressions");
            $this->counted_bonus_media_value = $bonusEntries->sum("media_value") / $bonusEntries->sum("contracted_impressions") * $this->counted_bonus_impressions;
        }

        $buaEntries = $this->values->where("type", "===", "bua")->where("show", "=", true);

        if ($buaEntries->count() > 0) {
            $this->has_bua_buys = true;

            $this->counted_bua_impressions = $buaEntries->sum("counted_impressions");
            $this->counted_bua_media_value = $buaEntries->sum("media_value") / $buaEntries->sum("contracted_impressions") * $this->counted_bua_impressions;
        }

        $this->total_counted_media_value = $this->counted_guaranteed_media_value + $this->counted_bonus_media_value + $this->counted_bua_media_value;
        $this->total_counted_impressions = $this->values->sum("counted_impressions");

        if ($this->total_counted_impressions > 0) {
            $this->total_counted_cpm = $this->guaranteed_net_investment / $this->total_counted_impressions * 1000;
        }
    }
}
