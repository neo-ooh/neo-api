<?php
/*
 * Copyright 2023 (c) Neo-OOH - All Rights Reserved
 * Unauthorized copying of this file, via any medium is strictly prohibited
 * Proprietary and confidential
 * Written by Valentin Dufois <vdufois@neo-ooh.com>
 *
 * @neo/api - BroadcastTagType.php
 */

namespace Neo\Modules\Broadcast\Enums;

enum BroadcastTagType: string {
	/**
	 * Tag with this type are used to properly target players at the format/layout/frame level
	 */
	case Targeting = "criterion";

	/**
	 * Tag used to trigger the display of content based on specific criteria
	 */
	case Trigger = "trigger";

	/**
	 * Tag used to specify the type of content of a broadcast resource
	 */
	case Category = "category";

	/**
	 * Tag used to specify the condition required for a resource to be broadcasted
	 */
	case Condition = "condition";
}
