<?php
/*
 * Copyright 2023 (c) Neo-OOH - All Rights Reserved
 * Unauthorized copying of this file, via any medium is strictly prohibited
 * Proprietary and confidential
 * Written by Valentin Dufois <vdufois@neo-ooh.com>
 *
 * @neo/api - SynchronizeAllNetworksJob.php
 */

namespace Neo\Modules\Broadcast\Jobs\Networks;

use Illuminate\Database\Eloquent\Collection;
use Neo\Jobs\Job;
use Neo\Modules\Broadcast\Models\Network;

/**
 * This job triggers a synchronization on every network registered.
 *
 * @extends Job<null>
 */
class SynchronizeAllNetworksJob extends Job {
	public function __construct() {
	}

	protected function run(): mixed {
		/** @var Collection<Network> $networks */
		$networks = Network::query()->get(["id"]);

		foreach ($networks as $network) {
			$syncJob = new SynchronizeNetworkJob($network->getKey());
			$syncJob->handle();
		}

		return null;
	}
}
