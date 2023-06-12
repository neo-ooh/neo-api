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
use Illuminate\Support\Facades\Auth;
use Neo\Jobs\Job;
use Neo\Modules\Properties\Models\InventoryResourceEvent;

abstract class InventoryJobBase extends Job {
    private InventoryResourceEvent $event;

    public function __construct(protected InventoryJobType $type, protected int $resourceId, protected int $inventoryId) {
        clock($this->type);
    }

    protected function beforeRun(): bool {
        clock("before before run");
        $this->event               = new InventoryResourceEvent();
        $this->event->inventory_id = $this->inventoryId;
        $this->event->event_type   = $this->type->value;
        $this->event->triggered_at = Carbon::now();
        $this->event->triggered_by = Auth::id();
        clock("before run");
        return true;
    }

    protected function onSuccess(mixed $result): void {
        $this->event->resource_id = $this->resourceId;
        $this->event->is_success  = true;
        $this->event->result      = $result;
        $this->event->save();
    }

    protected function onFailure(mixed $exception): void {
        $this->event->resource_id = $this->resourceId;
        $this->event->is_success  = false;
        $this->event->result      = (array)$exception;
        $this->event->save();
    }

    public function successful(): bool {
        return $this->event->is_success === true;
    }

    public function getResult(): array {
        return $this->event->result;
    }
}
