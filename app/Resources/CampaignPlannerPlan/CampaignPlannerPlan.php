<?php
/*
 * Copyright 2023 (c) Neo-OOH - All Rights Reserved
 * Unauthorized copying of this file, via any medium is strictly prohibited
 * Proprietary and confidential
 * Written by Valentin Dufois <vdufois@neo-ooh.com>
 *
 * @neo/api - CampaignPlannerPlan.php
 */

namespace Neo\Resources\CampaignPlannerPlan;

use Spatie\LaravelData\Data;

class CampaignPlannerPlan extends Data {
    public function __construct(
        public CampaignPlannerPlanMeta $_meta,
        public CampaignPlannerPlanRoot $plan,
    ) {

    }
}
