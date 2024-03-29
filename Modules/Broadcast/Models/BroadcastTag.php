<?php
/*
 * Copyright 2023 (c) Neo-OOH - All Rights Reserved
 * Unauthorized copying of this file, via any medium is strictly prohibited
 * Proprietary and confidential
 * Written by Valentin Dufois <vdufois@neo-ooh.com>
 *
 * @neo/api - BroadcastTag.php
 */

namespace Neo\Modules\Broadcast\Models;

use Carbon\Carbon;
use Illuminate\Database\Eloquent\Relations\BelongsToMany;
use Illuminate\Database\Eloquent\SoftDeletes;
use Neo\Casts\EnumSetCast;
use Neo\Models\Traits\HasPublicRelations;
use Neo\Modules\Broadcast\Enums\BroadcastResourceType;
use Neo\Modules\Broadcast\Enums\BroadcastTagScope;
use Neo\Modules\Broadcast\Enums\BroadcastTagType;
use Neo\Modules\Broadcast\Rules\AccessibleBroadcastTag;
use Neo\Modules\Broadcast\Services\Resources\Tag as TagResource;

/**
 * @property int                      $id
 * @property BroadcastTagType         $type
 * @property string                   $name_en
 * @property string                   $name_fr
 * @property array<BroadcastTagScope> $scope
 * @property boolean                  $is_primary
 *
 * @property Carbon                   $created_at
 * @property Carbon                   $updated_at
 * @property Carbon|null              $deleted_at
 */
class BroadcastTag extends BroadcastResourceModel {
	use SoftDeletes;
	use HasPublicRelations;

	public BroadcastResourceType $resourceType = BroadcastResourceType::Tag;

	protected $table = "broadcast_tags";

	protected $fillable = [
		"id",
		"type",
		"name_en",
		"name_fr",
		"scope",
		"is_primary",
	];

	protected $casts = [
		"type"       => BroadcastTagType::class,
		"scope"      => EnumSetCast::class . ":" . BroadcastTagScope::class,
		"is_primary" => "boolean",
	];

	protected $hidden = [
		"pivot",
	];

	protected string $accessRule = AccessibleBroadcastTag::class;

	protected array $publicRelations = [
		"representations" => "external_representations",
	];

	public function toResource(int $broadcasterId): TagResource {
		/** @var ExternalResource|null $externalRepresentation */
		$externalRepresentation = $this->external_representations->firstWhere("broadcaster_id", "=", $broadcasterId);

		return new TagResource(
			broadcaster_id: $broadcasterId,
			external_id   : $externalRepresentation?->data->external_id ?? "-1",
			name          : $this->name_en,
			tag_type      : $this->type,
		);
	}

	/*
	|--------------------------------------------------------------------------
	| Relations
	|--------------------------------------------------------------------------
	*/

	/**
	 * @return BelongsToMany<Format>
	 */
	public function formats(): BelongsToMany {
		return $this->belongsToMany(Format::class, "format_broadcast_tags", "broadcast_tag_id", "format_id");
	}
}
