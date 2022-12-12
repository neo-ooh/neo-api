<?php
/*
 * Copyright 2022 (c) Neo-OOH - All Rights Reserved
 * Unauthorized copying of this file, via any medium is strictly prohibited
 * Proprietary and confidential
 * Written by Valentin Dufois <vdufois@neo-ooh.com>
 *
 * @neo/api - 2022_12_08_151717_remove_libraries_campaigns_shares.php
 */

use Illuminate\Database\Migrations\Migration;
use Illuminate\Support\Facades\Schema;

class RemoveLibrariesCampaignsShares extends Migration {
    public function up() {
        Schema::drop("campaign_shares");
        Schema::drop("libraries_shares");
    }
}
