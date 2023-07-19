<?php
/*
 * Copyright 2023 (c) Neo-OOH - All Rights Reserved
 * Unauthorized copying of this file, via any medium is strictly prohibited
 * Proprietary and confidential
 * Written by Valentin Dufois <vdufois@neo-ooh.com>
 *
 * @neo/api - 2023_07_12_163131_migrate_planner_plans.php
 */

use Illuminate\Database\Migrations\Migration;
use Neo\Models\CampaignPlannerSave;
use Symfony\Component\Console\Helper\ProgressBar;
use Symfony\Component\Console\Output\ConsoleOutput;

return new class extends Migration {
    public function up(): void {
        // For each plan, we want to pull it, extract missing information and store them, and store a copy of the plan data into a file
        $plans = CampaignPlannerSave::query()->orderBy("id")->lazy(100);


        $progress = (new ProgressBar(new ConsoleOutput(), $plans->count()));
        $progress->setFormat("%current%/%max% [%bar%] %percent:3s%% %message%");
        $progress->start();

        /** @var CampaignPlannerSave $plan */
        foreach ($plans as $plan) {
            $progress->setMessage($plan->name);
            $progress->advance();

            // Fill in metadata values
            \Illuminate\Support\Facades\DB::table("campaign_planner_saves")
                                          ->where("id", "=", $plan->getKey())
                                          ->update([
                                                       "uid"             => $plan->uid ?? Hashids::encode($plan->id),
                                                       "vik km ersion"   => $plan->version ?? 0,
                                                       "contract"        => $plan->contract_id,
                                                       "client_name"     => $plan->client_name,
                                                       "advertiser_name" => $plan->advertiser_name,
                                                   ]);

            // Store the plan data in a file
            // Load plan
            $planData = CampaignPlannerSave::query()
                                           ->from((new CampaignPlannerSave())->getWriteTable())
                                           ->where("id", "=", $plan->getKey())
                                           ->first()->data;

            $plan->storePlan(json_encode($planData, JSON_UNESCAPED_UNICODE));
        }

        $progress->finish();
    }
};
