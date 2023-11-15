<?php
/*
 * Copyright 2023 (c) Neo-OOH - All Rights Reserved
 * Unauthorized copying of this file, via any medium is strictly prohibited
 * Proprietary and confidential
 * Written by Valentin Dufois <vdufois@neo-ooh.com>
 *
 * @neo/api - ContractState.php
 */

namespace Neo\Modules\Properties\Services\Resources\Enums;

enum ContractState: string {
	case Draft = "draft";
	case Locked = "locked";
}
