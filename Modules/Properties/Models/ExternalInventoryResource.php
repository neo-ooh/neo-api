<?php
/*
 * Copyright 2023 (c) Neo-OOH - All Rights Reserved
 * Unauthorized copying of this file, via any medium is strictly prohibited
 * Proprietary and confidential
 * Written by Valentin Dufois <vdufois@neo-ooh.com>
 *
 * @neo/api - ExternalInventoryResource.php
 */

namespace Neo\Modules\Properties\Models;

use Carbon\Traits\Date;
use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\SoftDeletes;
use Neo\Models\Traits\HasCreatedByUpdatedBy;
use Neo\Modules\Properties\Models\StructuredColumns\InventoryRepresentationContext;
use Neo\Modules\Properties\Services\Resources\Enums\InventoryResourceType;
use Neo\Modules\Properties\Services\Resources\InventoryResourceId;

/**
 * @property int                            $id
 * @property int                            $resource_id
 * @property int                            $inventory_id
 * @property InventoryResourceType          $type
 * @property string                         $external_id
 * @property InventoryRepresentationContext $context
 *
 * @property Date                           $created_at
 * @property int|null                       $created_by
 * @property Date                           $updated_at
 * @property int|null                       $updated_by
 * @property Date|null                      $deleted_at
 * @property int|null                       $deleted_by
 */
class ExternalInventoryResource extends Model {
	use SoftDeletes;
	use HasCreatedByUpdatedBy;

	protected $table = "external_inventories_resources";

	protected $primaryKey = "id";

	protected $fillable = [
		"resource_id",
		"inventory_id",
		"type",
		"external_id",
		"context",
	];

	protected $casts = [
		"context" => InventoryRepresentationContext::class,
		"type"    => InventoryResourceType::class,
	];

	public function toInventoryResourceId() {
		return new InventoryResourceId(
			inventory_id: $this->inventory_id,
			external_id : $this->external_id,
			type        : $this->type,
			context     : $this->context->toArray(),
		);
	}

	public static function fromInventoryResource(InventoryResourceId $resource) {
		return new static([
			                  "inventory_id" => $resource->inventory_id,
			                  "type"         => $resource->type,
			                  "external_id"  => $resource->external_id,
			                  "context"      => $resource->context,
		                  ]);
	}
}
