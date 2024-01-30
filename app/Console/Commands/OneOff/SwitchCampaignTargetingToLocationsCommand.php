<?php
/*
 * Copyright 2024 (c) Neo-OOH - All Rights Reserved
 * Unauthorized copying of this file, via any medium is strictly prohibited
 * Proprietary and confidential
 * Written by Valentin Dufois <vdufois@neo-ooh.com>
 *
 * @neo/api - SwitchCampaignTargetingToLocationsCommand.php
 */

namespace Neo\Console\Commands\OneOff;

use Illuminate\Console\Command;
use Illuminate\Database\Eloquent\Collection;
use Illuminate\Support\Facades\DB;
use Neo\Modules\Broadcast\Models\Campaign;
use Neo\Modules\Broadcast\Models\Location;
use Neo\Modules\Broadcast\Models\Structs\CampaignLocation;
use Neo\Modules\Properties\Models\ResolvedProduct;

class SwitchCampaignTargetingToLocationsCommand extends Command {
    protected $signature = 'one-off:switch-campaign-targeting-to-locations {campaignId} {format?}';

    protected $description = 'Command description';

    public function handle(): void {
        $campaignId = $this->argument("campaignId");
        $forceFormat = $this->argument("format");

        /** @var Campaign $campaign */
        $campaign = Campaign::query()->findOrFail($campaignId);
        /** @var Collection<ResolvedProduct> $products */
        $products = $campaign->products;
        $products->load("locations");

        $inserts = [];
        /** @var ResolvedProduct $product */
        foreach ($products as $product) {
            /** @var Location $location */
            foreach ($product->locations as $location) {
                $inserts[$location->getKey()] = [
                    "campaign_id" => $campaignId,
                    "location_id" => $location->getKey(),
                    "format_id" => $forceFormat ?? $product->format_id,
                ];
            }
        }

        try {
            DB::beginTransaction();
            $campaign->locations()->attach($inserts);
            $campaign->products()->detach();
            DB::commit();
        } catch (\Exception $e) {
            DB::rollBack();
        }
    }
}
