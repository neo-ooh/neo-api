<?php
/*
 * Copyright 2023 (c) Neo-OOH - All Rights Reserved
 * Unauthorized copying of this file, via any medium is strictly prohibited
 * Proprietary and confidential
 * Written by Valentin Dufois <vdufois@neo-ooh.com>
 *
 * @neo/api - CampaignPlannerPlanMeta.php
 */

namespace Neo\Resources\CampaignPlannerPlan;

use Spatie\LaravelData\Data;

class CampaignPlannerPlanMeta extends Data {
    public function __construct(
        public string $version,

        public int    $id,
        public string $uid,
        public int    $actor_id,

        public string $name,
        public bool   $includes_compiled,

        public string $created_at,
        public string $updated_at,
    ) {
    }
}
