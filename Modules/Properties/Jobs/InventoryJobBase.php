<?php
/*
 * Copyright 2023 (c) Neo-OOH - All Rights Reserved
 * Unauthorized copying of this file, via any medium is strictly prohibited
 * Proprietary and confidential
 * Written by Valentin Dufois <vdufois@neo-ooh.com>
 *
 * @neo/api - InventoryJobBase.php
 */

namespace Neo\Modules\Properties\Jobs;

use Carbon\Carbon;
use Neo\Jobs\Job;
use Neo\Modules\Properties\Models\InventoryResourceEvent;

abstract class InventoryJobBase extends Job {
    private InventoryResourceEvent $event;

    public function __construct(private readonly InventoryJobType $type, protected int $resourceId, protected int $inventoryId) {
    }

    protected function beforeRun(): bool {
        $this->event               = new InventoryResourceEvent();
        $this->event->resource_id  = $this->resourceId;
        $this->event->inventory_id = $this->inventoryId;
        $this->event->event_type   = $this->type;
        $this->event->triggered_at = Carbon::now();
        return true;
    }

    protected function onSuccess(mixed $result): void {
        $this->event->is_success = true;
        $this->event->result     = $result;
        $this->event->save();
    }

    protected function onFailure(mixed $exception): void {
        $this->event->is_success = false;
        $this->event->result     = (array)$exception;
        $this->event->save();
    }
}