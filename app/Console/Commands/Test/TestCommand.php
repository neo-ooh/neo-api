<?php
/*
 * Copyright 2023 (c) Neo-OOH - All Rights Reserved
 * Unauthorized copying of this file, via any medium is strictly prohibited
 * Proprietary and confidential
 * Written by Valentin Dufois <vdufois@neo-ooh.com>
 *
 * @neo/api - TestCommand.php
 */

namespace Neo\Console\Commands\Test;

use Illuminate\Console\Command;
use Illuminate\Support\Facades\DB;
use Neo\Modules\Broadcast\Services\BroadcasterAdapterFactory;
use Neo\Modules\Broadcast\Services\BroadSign\API\BroadSignClient;
use Neo\Modules\Broadcast\Services\BroadSign\BroadSignAdapter;
use Neo\Modules\Broadcast\Services\BroadSign\Models\Creative;
use PhpOffice\PhpSpreadsheet\Reader\Exception;

class TestCommand extends Command {
	protected $signature = 'test:test';

	protected $description = 'Internal tests';

	/**
	 * @return void
	 * @throws Exception
	 */
	public function handle() {
		/** @var BroadSignAdapter $broadsign */
		$broadsign = BroadcasterAdapterFactory::makeForBroadcaster(1);
		$client    = new BroadSignClient($broadsign->getConfig());

		$creatives = collect(Creative::inContainer($client, 455721438));
		$creatives = $creatives->where("active", "=", true);
		$creatives = $creatives->sortBy("id");
		
		/** @var Creative $creative */
		foreach ($creatives as $creative) {
			if (!$creative->active) {
				continue;
			}

			$this->output->writeLn("[" . $creative->id . "] " . $creative->name);

			$r = DB::select("
				SELECT * FROM `external_resources`
				WHERE JSON_VALUE(`data`, '$.external_id') = ?
				AND `deleted_at` IS NULL
			", [$creative->id]);

			if (count($r) > 0) {
				$this->output->success("Still alive");
				continue;
			}

			$creative->active = false;
			$creative->save();
			$this->output->error("Not used anymore, deactivated.");
		}
	}
}
