<?php
/*
 * Copyright 2023 (c) Neo-OOH - All Rights Reserved
 * Unauthorized copying of this file, via any medium is strictly prohibited
 * Proprietary and confidential
 * Written by Valentin Dufois <vdufois@neo-ooh.com>
 *
 * @neo/api - ActorType.php
 */

namespace Neo\Enums;

enum ActorType: string {
	case User = "user";
	case Group = "group";
	case Property = "property";
	case Contract = "contract";
}
