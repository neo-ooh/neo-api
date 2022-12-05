<?php
/*
 * Copyright 2022 (c) Neo-OOH - All Rights Reserved
 * Unauthorized copying of this file, via any medium is strictly prohibited
 * Proprietary and confidential
 * Written by Valentin Dufois <vdufois@neo-ooh.com>
 *
 * @neo/api - DeleteBurstJob.php
 */

namespace Neo\Jobs\Contracts;

use Neo\Jobs\Job;
use Neo\Models\ContractBurst;

class DeleteBurstJob extends Job {
    public function __construct(protected int $burstId, protected bool $deleteLocked = false) {
    }

    /**
     * @inheritDoc
     */
    protected function run(): bool {
        /** @var ContractBurst $burst */
        $burst = ContractBurst::withTrashed()->findOrFail($this->burstId);

        foreach ($burst->screenshots as $screenshot) {
            if (!$this->deleteLocked && $screenshot->is_locked) {
                continue;
            }

            $screenshot->delete();
        }

        if ($burst->screenshots()->count() === 0) {
            $burst->forceDelete();
        }

        return true;
    }
}
