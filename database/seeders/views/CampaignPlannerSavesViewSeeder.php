<?php
/*
 * Copyright 2023 (c) Neo-OOH - All Rights Reserved
 * Unauthorized copying of this file, via any medium is strictly prohibited
 * Proprietary and confidential
 * Written by Valentin Dufois <vdufois@neo-ooh.com>
 *
 * @neo/api - CampaignPlannerSavesViewSeeder.php
 */

namespace Database\Seeders\views;

use Illuminate\Database\Seeder;
use Illuminate\Support\Facades\DB;

class CampaignPlannerSavesViewSeeder extends Seeder {
    public function run() {
        $viewName = "campaign_planner_saves_view";

        DB::statement("DROP VIEW IF EXISTS $viewName");

        DB::statement(/** @lang SQL */ <<<EOS
        CREATE VIEW `$viewName` AS
        SELECT
          `id`,
          JSON_VALUE(`data`, '$._meta.uid') as `uid`,
          `name`,
          `actor_id`,
          JSON_VALUE(`data`, '$._meta.version') as `version`,
          COALESCE(JSON_VALUE(`data`, '$.plan.odoo.contract'), JSON_VALUE(`data`, '$.odoo.contract')) as `contract_id`,
          COALESCE(JSON_VALUE(`data`, '$.plan.odoo.partnerName[1]'), JSON_VALUE(`data`, '$.odoo.partnerName')) as `client_name`,
          COALESCE(JSON_VALUE(`data`, '$.plan.odoo.analyticAccountName[1]'), JSON_VALUE(`data`, '$.odoo.analyticAccountName')) as `advertiser_name`,
          `created_at`,
          `updated_at`
        FROM `campaign_planner_saves`
        EOS
        );
    }
}
