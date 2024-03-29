<?php
/*
 * Copyright 2023 (c) Neo-OOH - All Rights Reserved
 * Unauthorized copying of this file, via any medium is strictly prohibited
 * Proprietary and confidential
 * Written by Valentin Dufois <vdufois@neo-ooh.com>
 *
 * @neo/api - InventoryProvider.php
 */

namespace Neo\Modules\Properties\Models;

use Cache;
use Carbon\Carbon;
use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\HasMany;
use Illuminate\Database\Eloquent\SoftDeletes;
use Neo\Enums\Capability;
use Neo\Helpers\Relation;
use Neo\Models\Traits\HasCreatedByUpdatedBy;
use Neo\Models\Traits\HasPublicRelations;
use Neo\Modules\Properties\Models\StructuredColumns\InventoryProviderSettings;
use Neo\Modules\Properties\Services\Exceptions\InvalidInventoryAdapterException;
use Neo\Modules\Properties\Services\InventoryAdapter;
use Neo\Modules\Properties\Services\InventoryAdapterFactory;
use Neo\Modules\Properties\Services\InventoryCapability;
use Neo\Modules\Properties\Services\InventoryType;
use function Ramsey\Uuid\v4;

/**
 * @property int                       $id
 * @property string                    $uuid
 * @property InventoryType             $provider
 * @property string                    $name
 * @property bool                      $is_active
 * @property bool                      $allow_pull
 * @property bool                      $auto_pull
 * @property bool                      $allow_push
 * @property bool                      $auto_push
 * @property InventoryProviderSettings $settings
 * @property Carbon|null               $last_pull_at
 * @property Carbon|null               $last_push_at
 *
 * @property Carbon                    $created_at
 * @property int                       $created_by
 * @property Carbon                    $updated_at
 * @property int                       $updated_by
 * @property Carbon|null               $deleted_at
 * @property int|null                  $deleted_by
 */
class InventoryProvider extends Model {
	use SoftDeletes;
	use HasCreatedByUpdatedBy;
	use HasPublicRelations;

	protected $table = "inventory_providers";

	protected $primaryKey = "id";

	protected $fillable = [
		"provider",
		"name",
		"is_active",
		"allow_pull",
		"auto_pull",
		"allow_push",
		"auto_push",
		"settings",
	];

	protected $casts = [
		"provider"     => InventoryType::class,
		"is_active"    => "bool",
		"allow_pull"   => "bool",
		"auto_pull"    => "bool",
		"allow_push"   => "bool",
		"auto_push"    => "bool",
		"settings"     => InventoryProviderSettings::class,
		"last_pull_at" => "datetime",
		"last_push_at" => "datetime",
	];

	protected $hidden = [
		"settings",
	];

	public function getPublicRelations(): array {
		return [
			"capabilities" => new Relation(append: "capabilities", gate: Capability::properties_inventories_edit),
			"events"       => new Relation(load: "events", gate: Capability::inventories_edit),
			"settings"     => new Relation(
				custom: fn(InventoryProvider $provider) => $provider->makeVisible("settings")->settings->except("api_key"),
				gate  : Capability::inventories_edit
			),
		];
	}

	protected static function boot() {
		parent::boot();

		static::creating(function (InventoryProvider $inventoryProvider) {
			$inventoryProvider->uuid = v4();
		});
	}

	public function external_representations(): HasMany {
		return $this->hasMany(ExternalInventoryResource::class, "inventory_id", "id");
	}

	public function events(): HasMany {
		return $this->hasMany(InventoryResourceEvent::class, "inventory_id", "id");
	}

	/**
	 * @return InventoryCapability[]
	 * @throws InvalidInventoryAdapterException
	 */
	public function getCapabilitiesAttribute() {
		return InventoryAdapterFactory::make($this)->getCapabilities();
	}

	/**
	 * Make and return the adapter for this inventory
	 *
	 * @throws InvalidInventoryAdapterException
	 */
	public function getAdapter(): InventoryAdapter {
		return InventoryAdapterFactory::make($this);
	}

	public function clearCache() {
		switch ($this->provider) {
			case InventoryType::Odoo:
				Cache::tags(["odoo-data"])->flush();
				break;
			default:
				// nothing
		}
	}
}
