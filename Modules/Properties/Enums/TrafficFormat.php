<?php
/*
 * Copyright 2023 (c) Neo-OOH - All Rights Reserved
 * Unauthorized copying of this file, via any medium is strictly prohibited
 * Proprietary and confidential
 * Written by Valentin Dufois <vdufois@neo-ooh.com>
 *
 * @neo/api - TrafficFormat.php
 */

namespace Neo\Modules\Properties\Enums;

enum TrafficFormat: string {
    case MonthlyMedian = "monthly_median";
    case MonthlyAdjusted = "monthly_adjusted";
    case DailyConstant = "daily_constant";
}
