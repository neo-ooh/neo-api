<?php
/*
 * Copyright 2023 (c) Neo-OOH - All Rights Reserved
 * Unauthorized copying of this file, via any medium is strictly prohibited
 * Proprietary and confidential
 * Written by Valentin Dufois <vdufois@neo-ooh.com>
 *
 * @neo/api - AudienceDataSources.php
 */

namespace Neo\Modules\Properties\Services\Reach\Models;

/**
 * @property int    $id
 * @property string $name
 */
class AudienceDataSources extends ReachModel {
    public string $endpoint = "audience_data_sources";
    public string $key = "id";
}
