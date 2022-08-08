<?php
/*
 * Copyright 2020 (c) Neo-OOH - All Rights Reserved
 * Unauthorized copying of this file, via any medium is strictly prohibited
 * Proprietary and confidential
 * Written by Valentin Dufois <vdufois@neo-ooh.com>
 *
 * @neo/api - BroadcastTag.php
 */

namespace Neo\Modules\Broadcast\Models;

use Illuminate\Database\Eloquent\SoftDeletes;
use Neo\Casts\EnumSetCast;
use Neo\Models\Traits\WithPublicRelations;
use Neo\Modules\Broadcast\Enums\BroadcastTagScope;
use Neo\Modules\Broadcast\Enums\BroadcastTagType;
use Neo\Modules\Broadcast\Services\Resources\Tag as TagResource;
use Spatie\DataTransferObject\Exceptions\UnknownProperties;

/**
 * @property int                      $id
 * @property BroadcastTagType         $type
 * @property string                   $name_en
 * @property string                   $name_fr
 * @property array<BroadcastTagScope> $scope
 */
class BroadcastTag extends BroadcastResourceModel {
    use SoftDeletes;
    use WithPublicRelations;

    protected $table = "broadcast_tags";

    protected $fillable = [
        "id",
        "type",
        "name_en",
        "name_fr",
        "scope"
    ];

    protected $casts = [
        "type"  => BroadcastTagType::class,
        "scope" => EnumSetCast::class . ":" . BroadcastTagScope::class
    ];

    protected array $publicRelations = [
        "representations" => "external_representations"
    ];

    /**
     * @throws UnknownProperties
     */
    public function toResource(int $broadcasterId): TagResource {
        /** @var ExternalResource|null $externalRepresentation */
        $externalRepresentation = $this->external_representations->firstWhere("broadcaster_id", "=", $broadcasterId);

        return new TagResource([
            "name"        => $this->name_en,
            "external_id" => $externalRepresentation?->data->external_id ?? "-1",
            "tag_type"    => $this->type,
        ]);
    }
}
