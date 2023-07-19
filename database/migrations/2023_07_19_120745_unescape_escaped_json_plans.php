<?php
/*
 * Copyright 2023 (c) Neo-OOH - All Rights Reserved
 * Unauthorized copying of this file, via any medium is strictly prohibited
 * Proprietary and confidential
 * Written by Valentin Dufois <vdufois@neo-ooh.com>
 *
 * @neo/api - 2023_07_19_120745_unescape_escaped_json_plans.php
 */

use Illuminate\Database\Migrations\Migration;
use Illuminate\Support\Facades\Storage;
use Neo\Models\CampaignPlannerSave;
use Symfony\Component\Console\Helper\ProgressBar;
use Symfony\Component\Console\Output\ConsoleOutput;

return new class extends Migration {
    public function up(): void {
        $plans = CampaignPlannerSave::query()->orderBy("id")->lazy(100);

        $progress = (new ProgressBar(new ConsoleOutput(), 3058));
        $progress->setFormat("%current%/%max% [%bar%] %percent:3s%% %message%");
        $progress->start();

        /** @var CampaignPlannerSave $plan */
        foreach ($plans as $plan) {
            $progress->advance();
            $rawPlan = Storage::disk("public")->get($plan->plan_path);
            $plan->storePlan(trim(stripcslashes($rawPlan), "\""));
        }
        $progress->finish();
    }
};
