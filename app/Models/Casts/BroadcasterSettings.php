<?php

namespace Neo\Models\Casts;

use Illuminate\Contracts\Database\Eloquent\CastsAttributes;
use Neo\Models\BroadcasterConnection;
use Neo\Models\ConnectionSettingsBroadSign;
use Neo\Models\ConnectionSettingsPiSignage;
use Neo\Services\API\Traits\HasAttributes;
use Neo\Services\Broadcast\Broadcaster;
use RuntimeException;

/**
 * This class casts the broadcasters_connections.settings to a convenient PHP representation
 */
class BroadcasterSettings implements CastsAttributes {
    /**
     * @inheritDoc
     * @param BroadcasterConnection $model
     * @throws \JsonException
     */
    public function get($model, string $key, $value, array $attributes) {
        $settings = $value !== null ? json_decode($value, true, 512, JSON_THROW_ON_ERROR) : [];

        $settings["broadcaster_uuid"] = $attributes["uuid"];

        return match ($model->broadcaster) {
            Broadcaster::BROADSIGN => new ConnectionSettingsBroadSign($settings),
            Broadcaster::PISIGNAGE => new ConnectionSettingsPiSignage($settings),
            default => null,
        };
    }

    /**
     * @inheritDoc
     */
    public function set($model, string $key, $value, array $attributes) {
        if(!in_array(HasAttributes::class, class_uses($value), true)) {
            throw new RuntimeException("Bad format");
        }

        return $value->toJson();
    }
}
