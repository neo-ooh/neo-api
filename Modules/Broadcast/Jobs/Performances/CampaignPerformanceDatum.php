<?php
/*
 * Copyright 2023 (c) Neo-OOH - All Rights Reserved
 * Unauthorized copying of this file, via any medium is strictly prohibited
 * Proprietary and confidential
 * Written by Valentin Dufois <vdufois@neo-ooh.com>
 *
 * @neo/api - CampaignPerformanceDatum.php
 */

namespace Neo\Modules\Broadcast\Jobs\Performances;

use Neo\Modules\Broadcast\Models\ExternalResource;
use Neo\Modules\Broadcast\Services\Resources\ExternalBroadcasterResourceId;
use Spatie\LaravelData\Data;

class CampaignPerformanceDatum extends Data {
	public function __construct(
		public ExternalResource|null         $representation,

		public ExternalBroadcasterResourceId $campaign,
		/**
		 * @var string Date string YYYY-mm-DD
		 */
		public string                        $date,

		/**
		 * @var int How many repetitions for the campaign on this date
		 */
		public int                           $repetitions,

		/**
		 * @var int How many impressions for the campaign on this date
		 */
		public int                           $impressions,
	) {

	}
}
