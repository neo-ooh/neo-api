<?php
/*
 * Copyright 2023 (c) Neo-OOH - All Rights Reserved
 * Unauthorized copying of this file, via any medium is strictly prohibited
 * Proprietary and confidential
 * Written by Valentin Dufois <vdufois@neo-ooh.com>
 *
 * @neo/api - CPProductPricing.php
 */

namespace Neo\Resources\CampaignPlannerPlan\CompiledPlan\OOH;

enum CPProductPricing: string {
	case Unit = "unit";
	case UnitProduct = "unit-product";
	case CPM = "cpm";
}
