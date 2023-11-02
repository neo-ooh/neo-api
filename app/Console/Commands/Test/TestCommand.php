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
use Neo\Models\Actor;
use PhpOffice\PhpSpreadsheet\Reader\Exception;

class TestCommand extends Command {
	protected $signature = 'test:test';

	protected $description = 'Internal tests';

	/**
	 * @return void
	 * @throws Exception
	 */
	public function handle() {
//		dump(Actor::find(448)->getAccessibleActors(shallow: true)->pluck("id"));
//		dump(ActorsGetter::from(40)
//		                 ->selectChildren(recursive: false)
//		                 ->getSelection());

//		dump(ActorsGetter::from(558)
//		                 ->selectParents()
//		                 ->selectFocus()
//		                 ->getSelection());

		dump(Actor::find(1010)->getRootAccessesShallow());
	}
}
